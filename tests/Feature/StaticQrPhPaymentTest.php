<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Unit;
use Carbon\Carbon;
use Database\Seeders\DirectStaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class StaticQrPhPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DirectStaySeeder::class);
    }

    private function createUnpaidBooking(string $code, float $depositAmount = 1000.00): Booking
    {
        $unit = Unit::firstOrFail();

        $booking = Booking::create([
            'booking_code' => $code,
            'unit_id' => $unit->id,
            'guest_name' => 'Maria Clara',
            'guest_email' => 'maria@example.com',
            'guest_phone' => '09171234567',
            'guest_count' => 2,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'nights_count' => 2,
            'base_amount' => 3000.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => $depositAmount,
            'platform_fee' => 150.00,
            'total_amount' => 3150.00 + $depositAmount,
            'status' => 'pending_verification',
            'payment_status' => 'unpaid',
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'advance_deposit',
            'amount' => $depositAmount,
            'status' => 'pending',
        ]);

        return $booking;
    }

    public function test_static_qr_initiation_assigns_unique_centavo_offsets_within_30_minutes(): void
    {
        $bookingA = $this->createUnpaidBooking('DS-QR-01');
        $bookingB = $this->createUnpaidBooking('DS-QR-02');
        $bookingC = $this->createUnpaidBooking('DS-QR-03');

        // An expired pending payment (> 30 mins ago) should not block reuse of +1 centavo.
        $oldPayment = Payment::create([
            'booking_id' => $bookingC->id,
            'amount_centavos' => 100001,
            'status' => 'pending',
            'payment_scope' => 'deposit',
        ]);
        $oldPayment->forceFill(['created_at' => now()->subMinutes(31)])->saveQuietly();

        $responseA = $this->post(route('compliance.qrph.checkout', $bookingA->booking_code), [
            'payment_scope' => 'deposit',
        ]);
        $paymentA = Payment::where('booking_id', $bookingA->id)->firstOrFail();
        $responseA->assertRedirect(route('payments.qrph', $paymentA));

        $responseB = $this->post(route('compliance.qrph.checkout', $bookingB->booking_code), [
            'payment_scope' => 'deposit',
        ]);
        $paymentB = Payment::where('booking_id', $bookingB->id)->firstOrFail();
        $responseB->assertRedirect(route('payments.qrph', $paymentB));

        $this->assertSame('pending', $paymentA->status);
        $this->assertSame(100001, $paymentA->amount_centavos);
        $this->assertSame('pending', $paymentB->status);
        $this->assertSame(100002, $paymentB->amount_centavos);
    }

    public function test_static_qr_initiation_enforces_minimum_100_centavos_base(): void
    {
        $booking = $this->createUnpaidBooking('DS-QR-MIN', 0.50);

        $this->post(route('compliance.qrph.checkout', $booking->booking_code), [
            'payment_scope' => 'deposit',
        ])->assertRedirect();

        $payment = Payment::where('booking_id', $booking->id)->firstOrFail();
        $this->assertSame(101, $payment->amount_centavos);
    }

    public function test_qrph_view_displays_qr_image_exact_amount_polling_and_conditional_simulation_button(): void
    {
        $booking = $this->createUnpaidBooking('DS-QR-VIEW');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount_centavos' => 100007,
            'status' => 'pending',
            'payment_scope' => 'deposit',
        ]);

        Config::set('services.paymongo.sandbox_simulation', false);
        $this->get(route('payments.qrph', $payment))
            ->assertOk()
            ->assertSee('images/paymongo-static-qr.png')
            ->assertSee('1,000.07')
            ->assertSee(route('payments.status', $payment), false)
            ->assertDontSee('Simulate Payment');

        Config::set('services.paymongo.sandbox_simulation', true);
        $this->get(route('payments.qrph', $payment))
            ->assertOk()
            ->assertSee('Simulate Payment');
    }

    public function test_status_polling_endpoint_and_sandbox_simulation_work_end_to_end(): void
    {
        $booking = $this->createUnpaidBooking('DS-QR-SIM');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount_centavos' => 100015,
            'status' => 'pending',
            'payment_scope' => 'deposit',
        ]);

        $this->getJson(route('payments.status', $payment))
            ->assertOk()
            ->assertExactJson([
                'status' => 'pending',
                'redirect_url' => route('bookings.show', $booking->id),
            ]);

        // Disabled by default -> 404
        Config::set('services.paymongo.sandbox_simulation', false);
        $this->post(route('payments.qrph.simulate', $payment))->assertNotFound();

        // Enabled -> marks payment and booking confirmed
        Config::set('services.paymongo.sandbox_simulation', true);
        $this->post(route('payments.qrph.simulate', $payment))
            ->assertRedirect(route('bookings.show', $booking->id));

        $payment->refresh();
        $booking->refresh();

        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->paymongo_payment_id);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('verified', $booking->payment_status);

        $this->getJson(route('payments.status', $payment))
            ->assertOk()
            ->assertExactJson([
                'status' => 'paid',
                'redirect_url' => route('bookings.show', $booking->id),
            ]);

        $this->get(route('bookings.show', $booking->id))
            ->assertOk()
            ->assertSee($booking->booking_code);
    }

    public function test_webhook_rejects_invalid_or_missing_signature_and_matches_qr_paid_and_payment_paid(): void
    {
        $webhookSecret = 'whsk_test_static_qr_secret';
        Config::set('services.paymongo.webhook_secret', $webhookSecret);

        $bookingQr = $this->createUnpaidBooking('DS-QR-WH-1');
        $paymentQr = Payment::create([
            'booking_id' => $bookingQr->id,
            'amount_centavos' => 100023,
            'status' => 'pending',
            'payment_scope' => 'deposit',
        ]);

        $qrPaidPayload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'qr.paid',
                    'data' => [
                        'id' => 'qr_evt_123',
                        'type' => 'qr',
                        'attributes' => [
                            'amount' => 100023,
                            'payment_id' => 'pay_live_qr_001',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        // Invalid signature returns 401
        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => 't=1700000000,li=bad_signature',
            ],
            $qrPaidPayload
        )->assertStatus(401);

        // Valid live signature (`li=`) marks payment paid and booking confirmed
        $timestamp = (string) time();
        $liveSig = hash_hmac('sha256', $timestamp.'.'.$qrPaidPayload, $webhookSecret);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},li={$liveSig}",
            ],
            $qrPaidPayload
        )->assertOk()->assertJson(['received' => true]);

        $paymentQr->refresh();
        $bookingQr->refresh();
        $this->assertSame('paid', $paymentQr->status);
        $this->assertSame('pay_live_qr_001', $paymentQr->paymongo_payment_id);
        $this->assertNotNull($paymentQr->paid_at);
        $this->assertSame('confirmed', $bookingQr->status);
        $this->assertSame('verified', $bookingQr->payment_status);
        $this->assertSame('paymongo_qrph', $bookingQr->payment_method);

        $this->assertDatabaseHas('transactions', [
            'booking_id' => $bookingQr->id,
            'type' => 'advance_deposit',
            'status' => 'completed',
            'reference_number' => 'pay_live_qr_001',
        ]);

        // Also test `payment.paid` matching a pending static QR payment with `te=` signature
        $bookingPay = $this->createUnpaidBooking('DS-QR-WH-2');
        $paymentPay = Payment::create([
            'booking_id' => $bookingPay->id,
            'amount_centavos' => 100045,
            'status' => 'pending',
            'payment_scope' => 'deposit',
        ]);

        $paymentPaidPayload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'payment.paid',
                    'data' => [
                        'id' => 'pay_live_002',
                        'type' => 'payment',
                        'attributes' => [
                            'amount' => 100045,
                            'status' => 'paid',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $testSig = hash_hmac('sha256', $timestamp.'.'.$paymentPaidPayload, $webhookSecret);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$testSig}",
            ],
            $paymentPaidPayload
        )->assertOk()->assertJson(['received' => true]);

        $paymentPay->refresh();
        $bookingPay->refresh();
        $this->assertSame('paid', $paymentPay->status);
        $this->assertSame('pay_live_002', $paymentPay->paymongo_payment_id);
        $this->assertSame('confirmed', $bookingPay->status);
        $this->assertSame('verified', $bookingPay->payment_status);
    }

    public function test_webhook_handles_qr_expired_gracefully_and_fails_closed_without_secret(): void
    {
        $booking = $this->createUnpaidBooking('DS-QR-EXP');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount_centavos' => 100088,
            'status' => 'pending',
            'payment_scope' => 'deposit',
        ]);

        $expiredPayload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'qr.expired',
                    'data' => [
                        'id' => 'qr_exp_1',
                        'attributes' => ['amount' => 100088],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        Config::set('services.paymongo.webhook_secret', '');
        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => 't=1700000000,te=anything',
            ],
            $expiredPayload
        )->assertStatus(401);

        $secret = 'whsk_test_exp';
        Config::set('services.paymongo.webhook_secret', $secret);
        $timestamp = (string) time();
        $sig = hash_hmac('sha256', $timestamp.'.'.$expiredPayload, $secret);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$sig}",
            ],
            $expiredPayload
        )->assertOk()->assertJson(['received' => true]);

        $this->assertSame('pending', $payment->fresh()->status);
    }
}
