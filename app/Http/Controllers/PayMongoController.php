<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayMongoController extends Controller
{
    /**
     * Initiate a PayMongo Checkout Session and redirect the guest to pay.
     */
    public function checkout(Request $request, string $bookingCode, PayMongoService $payMongo): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'This reservation has been cancelled. Payments are no longer accepted.');
        }

        if ($booking->isPaid()) {
            return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
                ->with('success', 'Payment for this reservation has already been verified.');
        }

        $validated = $request->validate([
            'payment_scope' => ['nullable', 'in:deposit,full'],
        ]);

        $paymentScope = $validated['payment_scope'] ?? PayMongoService::SCOPE_DEPOSIT;

        try {
            $session = $payMongo->createCheckoutSession(
                booking: $booking,
                paymentScope: $paymentScope,
                successUrl: route('compliance.paymongo.success', ['bookingCode' => $booking->booking_code]),
                cancelUrl: route('compliance.paymongo.cancel', ['bookingCode' => $booking->booking_code]),
            );

            return redirect()->away($session['checkout_url']);
        } catch (\Throwable $e) {
            Log::error('PayMongo checkout creation failed', [
                'booking_code' => $booking->booking_code,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
                ->with('error', 'Unable to launch PayMongo checkout: '.$e->getMessage());
        }
    }

    /**
     * Verify PayMongo payment upon redirect back to success URL; succeed only if verified paid.
     */
    public function success(Request $request, string $bookingCode, PayMongoService $payMongo): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->isPaid()) {
            return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
                ->with('success', 'Payment verified via PayMongo! You may proceed to Step 4.');
        }

        $checkoutId = (string) ($request->query('session_id') ?: $booking->paymongo_checkout_id);

        if ($checkoutId === '' || $booking->paymongo_checkout_id !== $checkoutId) {
            return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
                ->with('error', 'No matching PayMongo checkout session was found for this booking.');
        }

        try {
            $verification = $payMongo->verifyCheckoutSession($checkoutId);
        } catch (\Throwable $e) {
            Log::error('PayMongo verification failed', [
                'booking_code' => $booking->booking_code,
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
                ->with('error', 'Could not confirm your PayMongo payment status. Please try verifying again.');
        }

        if (! $verification['paid']) {
            return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
                ->with('error', 'Payment was not completed (Status: '.ucfirst($verification['status']).'). Your booking remains unpaid until payment succeeds.');
        }

        $paymentScope = $verification['payment_scope'] ?? $booking->payment_scope ?? PayMongoService::SCOPE_DEPOSIT;
        $paidAmount = $verification['amount'] ?? $payMongo->resolveAmount($booking, $paymentScope);
        $paymentId = $verification['payment_id'] ?? $checkoutId;
        $paymentMethod = $verification['payment_method'] ?? 'gcash';

        $payMongo->markBookingPaid(
            booking: $booking,
            checkoutId: $checkoutId,
            paymentId: $paymentId,
            paymentMethod: $paymentMethod,
            paidAmount: $paidAmount,
            paymentScope: $paymentScope,
        );

        return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
            ->with('success', 'Payment succeeded via PayMongo! ₱'.number_format($paidAmount, 2).' has been verified (Ref: '.$paymentId.'). Proceed to Step 4.');
    }

    /**
     * Handle cancelled or abandoned PayMongo checkout redirects.
     */
    public function cancel(string $bookingCode): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
            ->with('error', 'PayMongo payment was cancelled. No amount was charged — you can retry whenever you are ready.');
    }

    /**
     * Render local PayMongo Sandbox Checkout Simulator when API keys are not configured.
     */
    public function sandboxPage(string $checkoutId, PayMongoService $payMongo): View
    {
        if (! $payMongo->isSimulationEnabled()) {
            abort(404);
        }

        $session = $payMongo->getSimulatedSession($checkoutId);
        if ($session === null) {
            abort(404, 'Checkout session expired or not found.');
        }

        $booking = Booking::with('unit.building')
            ->where('booking_code', $session['booking_code'])
            ->firstOrFail();

        return view('payments.paymongo-sandbox', [
            'session' => $session,
            'booking' => $booking,
        ]);
    }

    /**
     * Complete or fail the simulated PayMongo sandbox payment and redirect back to callback.
     */
    public function sandboxComplete(Request $request, string $checkoutId, PayMongoService $payMongo): RedirectResponse
    {
        if (! $payMongo->isSimulationEnabled()) {
            abort(404);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:succeed,fail'],
            'payment_method' => ['nullable', Rule::in(PayMongoService::ALLOWED_PAYMENT_METHODS)],
        ]);

        $succeed = $validated['action'] === 'succeed';
        $selectedMethod = $validated['payment_method'] ?? PayMongoService::ALLOWED_PAYMENT_METHOD;
        $session = $payMongo->completeSimulatedSession($checkoutId, $succeed, $selectedMethod);

        if ($session === null) {
            abort(404, 'Checkout session expired or not found.');
        }

        if ($succeed) {
            return redirect()->away($session['success_url']);
        }

        return redirect()->away($session['cancel_url']);
    }

    /**
     * Initiate a Static QR Ph payment with unique centavo fingerprinting and show the QR page.
     */
    public function initiateQrPh(Request $request, string $bookingCode, PayMongoService $payMongo): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'This reservation has been cancelled. Payments are no longer accepted.');
        }

        if ($booking->isPaid()) {
            return redirect()->route('bookings.show', $booking->id)
                ->with('success', 'Payment for this reservation has already been verified.');
        }

        $validated = $request->validate([
            'payment_scope' => ['nullable', 'in:deposit,full'],
        ]);

        $paymentScope = $validated['payment_scope'] ?? PayMongoService::SCOPE_DEPOSIT;
        $payment = $payMongo->createStaticQrPayment($booking, $paymentScope);

        return redirect()->route('payments.qrph', $payment);
    }

    /**
     * Display the Static QR Ph checkout screen with exact centavo amount and 3-second polling.
     */
    public function showQrPh(Payment $payment): View
    {
        $payment->load(['booking.unit.building', 'booking.unit.host']);

        return view('payments.qrph', [
            'payment' => $payment,
            'booking' => $payment->booking,
            'sandboxSimulation' => (bool) config('services.paymongo.sandbox_simulation'),
        ]);
    }

    /**
     * Return JSON status for the 3-second Static QR Ph polling script.
     */
    public function paymentStatus(Payment $payment): JsonResponse
    {
        return response()->json([
            'status' => $payment->status,
            'redirect_url' => route('bookings.show', $payment->booking_id),
        ]);
    }

    /**
     * Locally simulate a paid Static QR Ph payment when sandbox simulation is enabled.
     */
    public function simulateQrPh(Payment $payment, PayMongoService $payMongo): RedirectResponse
    {
        if (! (bool) config('services.paymongo.sandbox_simulation')) {
            abort(404);
        }

        $payMongo->markStaticQrPaymentPaid($payment, 'pay_sim_qr_'.Str::random(16));

        return redirect()->route('bookings.show', $payment->booking_id)
            ->with('success', 'QR Ph payment simulated and confirmed!');
    }
}
