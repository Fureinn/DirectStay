<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayMongoWebhookController extends Controller
{
    /**
     * Handle incoming PayMongo webhook events (qr.paid, payment.paid, checkout_session.payment.paid, qr.expired).
     */
    public function __invoke(Request $request, PayMongoService $payMongo): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('Paymongo-Signature');

        if (! $payMongo->verifyWebhookSignature($rawPayload, $signatureHeader)) {
            Log::warning('PayMongo webhook signature verification failed', [
                'has_signature' => ! empty($signatureHeader),
                'has_secret' => ! empty(config('services.paymongo.webhook_secret')),
            ]);

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $eventType = $payload['data']['attributes']['type'] ?? null;

        Log::info('PayMongo webhook received', [
            'event_type' => $eventType,
            'resource_type' => $payload['data']['attributes']['data']['type'] ?? null,
        ]);

        if (in_array($eventType, ['qr.paid', 'payment.paid'], true)) {
            $resourceData = $payload['data']['attributes']['data'] ?? [];
            $attributes = is_array($resourceData['attributes'] ?? null) ? $resourceData['attributes'] : [];

            $receivedAmount = $this->extractAmountCentavos($attributes);
            $paymentId = $this->extractPaymentId($resourceData, $attributes);

            Log::info('PayMongo QR/Payment webhook parsed', [
                'event_type' => $eventType,
                'received_amount' => $receivedAmount,
                'payment_id' => $paymentId,
            ]);

            if ($receivedAmount !== null && $receivedAmount > 0) {
                $payment = Payment::where('status', 'pending')
                    ->where('amount_centavos', $receivedAmount)
                    ->latest('id')
                    ->first();

                if ($payment) {
                    $resolvedPaymentId = $paymentId !== '' ? $paymentId : 'pay_qr_'.Str::random(16);
                    $payMongo->markStaticQrPaymentPaid($payment, $resolvedPaymentId);

                    return response()->json([
                        'received' => true,
                        'matched_payment_id' => $payment->id,
                    ]);
                }
            }

            if ($eventType === 'payment.paid') {
                return $this->handleCheckoutSessionPayment($payload, $payMongo, $eventType);
            }

            return response()->json(['received' => true]);
        }

        if ($eventType === 'checkout_session.payment.paid') {
            return $this->handleCheckoutSessionPayment($payload, $payMongo, $eventType);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Extract received payment amount in centavos from PayMongo qr.paid / payment.paid attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function extractAmountCentavos(array $attributes): ?int
    {
        $payments = $attributes['payments'] ?? [];
        $firstPayment = is_array($payments) && isset($payments[0]) && is_array($payments[0])
            ? $payments[0]
            : null;

        $rawAmount = $firstPayment['attributes']['amount']
            ?? $attributes['latest_payment']['attributes']['amount']
            ?? $attributes['payment']['attributes']['amount']
            ?? $attributes['amount']
            ?? null;

        if ($rawAmount === null || ! is_numeric($rawAmount)) {
            return null;
        }

        return (int) $rawAmount;
    }

    /**
     * Extract the PayMongo payment ID from webhook event resource data.
     *
     * @param  array<string, mixed>  $resourceData
     * @param  array<string, mixed>  $attributes
     */
    private function extractPaymentId(array $resourceData, array $attributes): string
    {
        $payments = $attributes['payments'] ?? [];
        $firstPayment = is_array($payments) && isset($payments[0]) && is_array($payments[0])
            ? $payments[0]
            : null;

        return (string) (
            $firstPayment['id']
            ?? $attributes['latest_payment']['id']
            ?? $attributes['payment_id']
            ?? $attributes['payment']['id']
            ?? $resourceData['id']
            ?? ''
        );
    }

    /**
     * Handle standard PayMongo Checkout Session webhook events.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleCheckoutSessionPayment(array $payload, PayMongoService $payMongo, string $eventType): JsonResponse
    {
        $resourceData = $payload['data']['attributes']['data'] ?? [];
        $attributes = is_array($resourceData['attributes'] ?? null) ? $resourceData['attributes'] : [];

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
        $paymentMethod = strtolower((string) (
            $paidPayment['attributes']['source']['type']
            ?? $paidPayment['attributes']['payment_method']['type']
            ?? $attributes['source']['type']
            ?? $attributes['payment_method_used']
            ?? PayMongoService::ALLOWED_PAYMENT_METHOD
        ));

        if (! PayMongoService::isAllowedPaymentMethod($paymentMethod)) {
            return response()->json(['ignored' => 'Only GCash and QR Ph payments are accepted.']);
        }

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
