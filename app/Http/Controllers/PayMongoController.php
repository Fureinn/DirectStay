<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            'payment_method' => ['required', 'in:gcash,paymaya,card,grab_pay'],
        ]);

        $succeed = $validated['action'] === 'succeed';
        $session = $payMongo->completeSimulatedSession($checkoutId, $succeed, $validated['payment_method']);

        if ($session === null) {
            abort(404, 'Checkout session expired or not found.');
        }

        if ($succeed) {
            return redirect()->away($session['success_url']);
        }

        return redirect()->away($session['cancel_url']);
    }

    /**
     * Handle PayMongo webhook callbacks for asynchronous payment confirmation.
     */
    public function webhook(Request $request, PayMongoService $payMongo): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('Paymongo-Signature');

        if (! $payMongo->verifyWebhookSignature($rawPayload, $signatureHeader)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $eventType = $payload['data']['attributes']['type'] ?? null;
        if (! in_array($eventType, ['checkout_session.payment.paid', 'payment.paid'], true)) {
            return response()->json(['received' => true]);
        }

        $resourceData = $payload['data']['attributes']['data'] ?? [];
        $attributes = $resourceData['attributes'] ?? [];

        $checkoutId = $eventType === 'checkout_session.payment.paid'
            ? (string) ($resourceData['id'] ?? '')
            : (string) ($attributes['checkout_session_id'] ?? '');

        $bookingCode = $attributes['metadata']['booking_code']
            ?? ($attributes['reference_number'] ?? null);

        $booking = null;
        if ($checkoutId !== '') {
            $booking = Booking::where('paymongo_checkout_id', $checkoutId)->first();
        }
        if (! $booking && $bookingCode) {
            $booking = Booking::where('booking_code', $bookingCode)->first();
        }

        if (! $booking) {
            return response()->json(['received' => true]);
        }

        $payments = $attributes['payments'] ?? [];
        $paidPayment = is_array($payments) ? ($payments[0] ?? null) : null;

        $paymentId = (string) ($paidPayment['id'] ?? ($resourceData['id'] ?? $checkoutId));
        $paymentMethod = (string) ($paidPayment['attributes']['source']['type']
            ?? ($attributes['source']['type'] ?? 'gcash'));
        $paidCentavos = $paidPayment['attributes']['amount']
            ?? ($attributes['amount'] ?? null);
        $paymentScope = (string) ($attributes['metadata']['payment_scope']
            ?? $booking->payment_scope
            ?? PayMongoService::SCOPE_DEPOSIT);
        $paidAmount = $paidCentavos !== null
            ? round(((int) $paidCentavos) / 100, 2)
            : $payMongo->resolveAmount($booking, $paymentScope);

        $payMongo->markBookingPaid(
            booking: $booking,
            checkoutId: $checkoutId !== '' ? $checkoutId : (string) $booking->paymongo_checkout_id,
            paymentId: $paymentId,
            paymentMethod: $paymentMethod,
            paidAmount: $paidAmount,
            paymentScope: $paymentScope,
        );

        return response()->json(['received' => true]);
    }
}
