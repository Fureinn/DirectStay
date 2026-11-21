<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PayMongoService
{
    public const SCOPE_DEPOSIT = 'deposit';

    public const SCOPE_FULL = 'full';

    /**
     * Determine if live or test PayMongo API credentials are configured.
     */
    public function isLiveApiConfigured(): bool
    {
        return ! empty(config('services.paymongo.secret_key'));
    }

    /**
     * Determine if local sandbox simulation mode should be used.
     */
    public function isSimulationEnabled(): bool
    {
        if ($this->isLiveApiConfigured()) {
            return false;
        }

        return (bool) config('services.paymongo.sandbox_simulation') || app()->environment(['local', 'testing']);
    }

    /**
     * Resolve the payable amount in PHP based on the selected payment scope.
     */
    public function resolveAmount(Booking $booking, string $paymentScope): float
    {
        if ($paymentScope === self::SCOPE_FULL) {
            return (float) $booking->total_amount;
        }

        return (float) $booking->advance_deposit_amount;
    }

    /**
     * Create a PayMongo Checkout Session (or local sandbox simulation session).
     *
     * @return array{checkout_id: string, checkout_url: string, amount: float, mode: string}
     */
    public function createCheckoutSession(Booking $booking, string $paymentScope, string $successUrl, string $cancelUrl): array
    {
        $booking->loadMissing('unit.building');

        $scope = $paymentScope === self::SCOPE_FULL ? self::SCOPE_FULL : self::SCOPE_DEPOSIT;
        $amount = $this->resolveAmount($booking, $scope);
        $amountInCentavos = (int) round($amount * 100);

        if ($amountInCentavos < 2000) {
            throw new RuntimeException('PayMongo requires a minimum transaction amount of ₱20.00.');
        }

        $unitTitle = $booking->unit?->title ?? 'DirectStay Unit';
        $description = $scope === self::SCOPE_FULL
            ? "Full Stay & Security Deposit ({$booking->booking_code}) - {$unitTitle}"
            : "Advance Security Deposit Escrow ({$booking->booking_code}) - {$unitTitle}";

        if ($this->isLiveApiConfigured()) {
            $response = Http::withBasicAuth((string) config('services.paymongo.secret_key'), '')
                ->acceptJson()
                ->asJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->post($this->baseUrl().'/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'billing' => [
                                'name' => $booking->guest_name,
                                'email' => $booking->guest_email,
                                'phone' => $booking->guest_phone,
                            ],
                            'line_items' => [
                                [
                                    'currency' => 'PHP',
                                    'amount' => $amountInCentavos,
                                    'name' => $scope === self::SCOPE_FULL ? 'DirectStay Full Stay Payment' : 'DirectStay Advance Security Deposit',
                                    'quantity' => 1,
                                    'description' => $description,
                                ],
                            ],
                            'payment_method_types' => ['qrph', 'gcash', 'paymaya', 'card', 'grab_pay'],
                            'reference_number' => $booking->booking_code,
                            'send_email_receipt' => true,
                            'show_description' => true,
                            'show_line_items' => true,
                            'description' => $description,
                            'success_url' => $successUrl,
                            'cancel_url' => $cancelUrl,
                            'metadata' => [
                                'booking_id' => (string) $booking->id,
                                'booking_code' => $booking->booking_code,
                                'payment_scope' => $scope,
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                $detail = $response->json('errors.0.detail') ?? $response->body();

                throw new RuntimeException('PayMongo API error: '.$detail);
            }

            $data = $response->json('data');
            $checkoutId = (string) ($data['id'] ?? '');
            $checkoutUrl = (string) ($data['attributes']['checkout_url'] ?? '');

            if ($checkoutId === '' || $checkoutUrl === '') {
                throw new RuntimeException('PayMongo returned an incomplete checkout session response.');
            }

            $booking->update([
                'paymongo_checkout_id' => $checkoutId,
                'payment_scope' => $scope,
            ]);

            return [
                'checkout_id' => $checkoutId,
                'checkout_url' => $checkoutUrl,
                'amount' => $amount,
                'mode' => 'api',
            ];
        }

        if ($this->isSimulationEnabled()) {
            $checkoutId = 'cs_sim_'.Str::random(20);

            $booking->update([
                'paymongo_checkout_id' => $checkoutId,
                'payment_scope' => $scope,
            ]);

            Cache::put($this->simulationCacheKey($checkoutId), [
                'checkout_id' => $checkoutId,
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'amount' => $amount,
                'payment_scope' => $scope,
                'description' => $description,
                'status' => 'pending',
                'payment_id' => null,
                'payment_method' => null,
                'success_url' => $successUrl.(str_contains($successUrl, '?') ? '&' : '?').'session_id='.$checkoutId,
                'cancel_url' => $cancelUrl,
            ], now()->addHours(2));

            return [
                'checkout_id' => $checkoutId,
                'checkout_url' => route('paymongo.sandbox', ['checkoutId' => $checkoutId]),
                'amount' => $amount,
                'mode' => 'simulation',
            ];
        }

        throw new RuntimeException('PayMongo secret key is not configured.');
    }

    /**
     * Retrieve a PayMongo Checkout Session and determine whether payment succeeded.
     *
     * @return array{paid: bool, status: string, payment_id: ?string, payment_method: ?string, amount: ?float, payment_scope: ?string, raw: array<string, mixed>}
     */
    public function verifyCheckoutSession(string $checkoutId): array
    {
        if (str_starts_with($checkoutId, 'cs_sim_') && $this->isSimulationEnabled()) {
            $sim = Cache::get($this->simulationCacheKey($checkoutId));
            if (! is_array($sim)) {
                return [
                    'paid' => false,
                    'status' => 'expired',
                    'payment_id' => null,
                    'payment_method' => null,
                    'amount' => null,
                    'payment_scope' => null,
                    'raw' => [],
                ];
            }

            $isPaid = ($sim['status'] ?? null) === 'paid';

            return [
                'paid' => $isPaid,
                'status' => (string) ($sim['status'] ?? 'pending'),
                'payment_id' => $sim['payment_id'] ?? null,
                'payment_method' => $sim['payment_method'] ?? null,
                'amount' => isset($sim['amount']) ? (float) $sim['amount'] : null,
                'payment_scope' => $sim['payment_scope'] ?? null,
                'raw' => $sim,
            ];
        }

        if (! $this->isLiveApiConfigured()) {
            throw new RuntimeException('PayMongo secret key is not configured.');
        }

        $response = Http::withBasicAuth((string) config('services.paymongo.secret_key'), '')
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->get($this->baseUrl().'/checkout_sessions/'.$checkoutId);

        if ($response->failed()) {
            $detail = $response->json('errors.0.detail') ?? $response->body();

            throw new RuntimeException('Unable to verify PayMongo session: '.$detail);
        }

        $data = $response->json('data') ?? [];
        $attributes = $data['attributes'] ?? [];
        $payments = $attributes['payments'] ?? [];

        $paidPayment = null;
        if (is_array($payments)) {
            foreach ($payments as $payment) {
                if (($payment['attributes']['status'] ?? null) === 'paid') {
                    $paidPayment = $payment;
                    break;
                }
            }
        }

        $paymentIntentStatus = $attributes['payment_intent']['attributes']['status'] ?? null;
        $sessionStatus = $attributes['status'] ?? 'unpaid';
        $isPaid = $paidPayment !== null || $paymentIntentStatus === 'succeeded' || $sessionStatus === 'paid';

        $paymentId = $paidPayment['id'] ?? ($attributes['payment_intent']['id'] ?? null);
        $paymentMethod = $paidPayment['attributes']['source']['type']
            ?? ($attributes['payment_method_used'] ?? 'gcash');
        $paidCentavos = $paidPayment['attributes']['amount']
            ?? ($attributes['payment_intent']['attributes']['amount'] ?? null);

        return [
            'paid' => $isPaid,
            'status' => $isPaid ? 'paid' : (string) ($paymentIntentStatus ?? $sessionStatus),
            'payment_id' => $paymentId ? (string) $paymentId : null,
            'payment_method' => $paymentMethod ? (string) $paymentMethod : null,
            'amount' => $paidCentavos !== null ? round(((int) $paidCentavos) / 100, 2) : null,
            'payment_scope' => $attributes['metadata']['payment_scope'] ?? null,
            'raw' => is_array($data) ? $data : [],
        ];
    }

    /**
     * Retrieve a simulated checkout session payload.
     *
     * @return ?array<string, mixed>
     */
    public function getSimulatedSession(string $checkoutId): ?array
    {
        $data = Cache::get($this->simulationCacheKey($checkoutId));

        return is_array($data) ? $data : null;
    }

    /**
     * Authorize or fail a simulated checkout session.
     *
     * @return ?array<string, mixed>
     */
    public function completeSimulatedSession(string $checkoutId, bool $succeed, string $paymentMethod = 'gcash'): ?array
    {
        $session = $this->getSimulatedSession($checkoutId);
        if ($session === null) {
            return null;
        }

        if ($succeed) {
            $session['status'] = 'paid';
            $session['payment_id'] = 'pay_sim_'.Str::random(16);
            $session['payment_method'] = $paymentMethod;
        } else {
            $session['status'] = 'failed';
            $session['payment_method'] = $paymentMethod;
        }

        Cache::put($this->simulationCacheKey($checkoutId), $session, now()->addHours(2));

        return $session;
    }

    /**
     * Mark a booking as paid & verified after PayMongo payment succeeds (idempotent).
     */
    public function markBookingPaid(
        Booking $booking,
        string $checkoutId,
        string $paymentId,
        string $paymentMethod,
        float $paidAmount,
        string $paymentScope = self::SCOPE_DEPOSIT,
    ): void {
        DB::transaction(function () use ($booking, $checkoutId, $paymentId, $paymentMethod, $paidAmount, $paymentScope): void {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($lockedBooking->payment_status === 'verified' && $lockedBooking->paymongo_payment_id === $paymentId) {
                return;
            }

            $normalizedMethod = str_starts_with($paymentMethod, 'paymongo_')
                ? $paymentMethod
                : 'paymongo_'.$paymentMethod;

            $lockedBooking->update([
                'payment_status' => 'verified',
                'paymongo_checkout_id' => $checkoutId,
                'paymongo_payment_id' => $paymentId,
                'payment_method' => $normalizedMethod,
                'payment_scope' => $paymentScope,
                'paid_amount' => $paidAmount,
                'paid_at' => now(),
            ]);

            $depositTxn = Transaction::where('booking_id', $lockedBooking->id)
                ->where('type', 'advance_deposit')
                ->first();

            if ($depositTxn) {
                $depositTxn->update([
                    'status' => 'completed',
                    'reference_number' => $paymentId,
                    'metadata' => array_merge($depositTxn->metadata ?? [], [
                        'gateway' => 'paymongo',
                        'checkout_id' => $checkoutId,
                        'payment_method' => $normalizedMethod,
                        'payment_scope' => $paymentScope,
                    ]),
                ]);
            } else {
                Transaction::create([
                    'booking_id' => $lockedBooking->id,
                    'type' => 'advance_deposit',
                    'amount' => $lockedBooking->advance_deposit_amount,
                    'reference_number' => $paymentId,
                    'status' => 'completed',
                    'metadata' => [
                        'gateway' => 'paymongo',
                        'checkout_id' => $checkoutId,
                        'payment_method' => $normalizedMethod,
                        'payment_scope' => $paymentScope,
                    ],
                ]);
            }

            if ($paymentScope === self::SCOPE_FULL) {
                Transaction::where('booking_id', $lockedBooking->id)
                    ->where('type', 'platform_fee')
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'completed',
                        'reference_number' => $paymentId,
                    ]);

                $stayBalance = (float) $lockedBooking->base_amount + (float) $lockedBooking->add_ons_amount;
                if ($stayBalance > 0) {
                    $existingStayTxn = Transaction::where('booking_id', $lockedBooking->id)
                        ->where('type', 'stay_payment')
                        ->where('reference_number', $paymentId)
                        ->exists();

                    if (! $existingStayTxn) {
                        Transaction::create([
                            'booking_id' => $lockedBooking->id,
                            'type' => 'stay_payment',
                            'amount' => $stayBalance,
                            'reference_number' => $paymentId,
                            'status' => 'completed',
                            'metadata' => [
                                'gateway' => 'paymongo',
                                'checkout_id' => $checkoutId,
                                'payment_method' => $normalizedMethod,
                                'description' => 'Full stay room rate & add-ons settled via PayMongo',
                            ],
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Verify the Paymongo-Signature header on incoming webhook payloads.
     */
    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool
    {
        $secret = (string) config('services.paymongo.webhook_secret');
        if ($secret === '') {
            return true;
        }

        if (! $signatureHeader) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            $pair = explode('=', trim($segment), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }

        $timestamp = $parts['t'] ?? null;
        if (! $timestamp) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        $testSignature = $parts['te'] ?? null;
        $liveSignature = $parts['li'] ?? null;

        if ($liveSignature !== null && hash_equals($expectedSignature, $liveSignature)) {
            return true;
        }

        if ($testSignature !== null && hash_equals($expectedSignature, $testSignature)) {
            return true;
        }

        return false;
    }

    /**
     * Build the base PayMongo API URL.
     */
    private function baseUrl(): string
    {
        return rtrim((string) config('services.paymongo.base_url', 'https://api.paymongo.com/v1'), '/');
    }

    /**
     * Build cache key for a simulated sandbox checkout session.
     */
    private function simulationCacheKey(string $checkoutId): string
    {
        return 'paymongo_sim_'.$checkoutId;
    }
}
