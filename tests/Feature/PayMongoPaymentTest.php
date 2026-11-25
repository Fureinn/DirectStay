<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DirectStaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayMongoPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DirectStaySeeder::class);
    }

    /**
     * Create a sample unpaid booking with pending ledger transactions.
     */
    private function createUnpaidBooking(string $code = 'DS-PM-01'): Booking
    {
        $unit = Unit::firstOrFail();

        $booking = Booking::create([
            'booking_code' => $code,
            'unit_id' => $unit->id,
            'guest_name' => 'Juan Dela Cruz',
            'guest_email' => 'juan@example.com',
            'guest_phone' => '09178889900',
            'guest_count' => 2,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'nights_count' => 2,
            'base_amount' => 3000.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 150.00,
            'total_amount' => 4150.00,
            'status' => 'pending_verification',
            'payment_status' => 'unpaid',
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'advance_deposit',
            'amount' => 1000.00,
            'status' => 'pending',
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'platform_fee',
            'amount' => 150.00,
            'status' => 'pending',
        ]);

        return $booking;
    }

    public function test_paymongo_api_checkout_redirects_and_succeeds_only_when_verified_paid(): void
    {
        Config::set('services.paymongo.secret_key', 'sk_test_dummy_key');
        Config::set('services.paymongo.base_url', 'https://api.paymongo.com/v1');

        Http::preventStrayRequests();
        Http::fake([
            'api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_123456',
                    'type' => 'checkout_session',
                    'attributes' => [
                        'checkout_url' => 'https://checkout.paymongo.com/cs_test_123456',
                    ],
                ],
            ], 200),
            'api.paymongo.com/v1/checkout_sessions/cs_test_123456' => Http::response([
                'data' => [
                    'id' => 'cs_test_123456',
                    'type' => 'checkout_session',
                    'attributes' => [
                        'status' => 'active',
                        'payment_intent' => [
                            'id' => 'pi_test_999',
                            'attributes' => [
                                'status' => 'succeeded',
                                'amount' => 100000,
                            ],
                        ],
                        'payments' => [
                            [
                                'id' => 'pay_test_abc123',
                                'attributes' => [
                                    'amount' => 100000,
                                    'status' => 'paid',
                                    'source' => ['type' => 'gcash'],
                                ],
                            ],
                        ],
                        'metadata' => [
                            'payment_scope' => 'deposit',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $booking = $this->createUnpaidBooking('DS-PM-API-01');

        $checkoutResponse = $this->post(route('compliance.paymongo.checkout', $booking->booking_code), [
            'payment_scope' => 'deposit',
        ]);

        $checkoutResponse->assertRedirect('https://checkout.paymongo.com/cs_test_123456');

        Http::assertSent(function ($request): bool {
            if ($request->url() !== 'https://api.paymongo.com/v1/checkout_sessions') {
                return true;
            }

            return $request['data']['attributes']['payment_method_types'] === ['gcash', 'qrph'];
        });

        $booking->refresh();
        $this->assertSame('cs_test_123456', $booking->paymongo_checkout_id);
        $this->assertSame('unpaid', $booking->payment_status);

        $successResponse = $this->get(route('compliance.paymongo.success', ['bookingCode' => $booking->booking_code]));
        $successResponse->assertRedirect(route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3]));
        $successResponse->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('verified', $booking->payment_status);
        $this->assertSame('pay_test_abc123', $booking->paymongo_payment_id);
        $this->assertSame('paymongo_gcash', $booking->payment_method);
        $this->assertEquals(1000.00, (float) $booking->paid_amount);
        $this->assertNotNull($booking->paid_at);

        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'advance_deposit',
            'status' => 'completed',
            'reference_number' => 'pay_test_abc123',
        ]);
    }

    public function test_paymongo_success_callback_does_not_succeed_if_session_is_unpaid(): void
    {
        Config::set('services.paymongo.secret_key', 'sk_test_dummy_key');
        Config::set('services.paymongo.base_url', 'https://api.paymongo.com/v1');

        Http::preventStrayRequests();
        Http::fake([
            'api.paymongo.com/v1/checkout_sessions/cs_test_unpaid' => Http::response([
                'data' => [
                    'id' => 'cs_test_unpaid',
                    'type' => 'checkout_session',
                    'attributes' => [
                        'status' => 'active',
                        'payment_intent' => [
                            'id' => 'pi_test_unpaid',
                            'attributes' => [
                                'status' => 'awaiting_payment_method',
                                'amount' => 100000,
                            ],
                        ],
                        'payments' => [],
                    ],
                ],
            ], 200),
        ]);

        $booking = $this->createUnpaidBooking('DS-PM-UNPAID-01');
        $booking->update(['paymongo_checkout_id' => 'cs_test_unpaid', 'payment_scope' => 'deposit']);

        $response = $this->get(route('compliance.paymongo.success', ['bookingCode' => $booking->booking_code]));

        $response->assertRedirect(route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3]));
        $response->assertSessionHas('error');

        $booking->refresh();
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertNull($booking->paymongo_payment_id);
    }

    public function test_paymongo_sandbox_simulation_succeeds_and_fails_properly(): void
    {
        Config::set('services.paymongo.secret_key', '');
        Config::set('services.paymongo.sandbox_simulation', true);

        $booking = $this->createUnpaidBooking('DS-PM-SIM-01');

        // Start simulated checkout for full stay payment
        $checkoutResponse = $this->post(route('compliance.paymongo.checkout', $booking->booking_code), [
            'payment_scope' => 'full',
        ]);

        $booking->refresh();
        $checkoutId = $booking->paymongo_checkout_id;
        $this->assertNotNull($checkoutId);
        $this->assertStringStartsWith('cs_sim_', $checkoutId);

        $checkoutResponse->assertRedirect(route('paymongo.sandbox', ['checkoutId' => $checkoutId]));

        // Visiting success directly before completing payment should NOT mark booking as paid
        $prematureResponse = $this->get(route('compliance.paymongo.success', [
            'bookingCode' => $booking->booking_code,
            'session_id' => $checkoutId,
        ]));
        $prematureResponse->assertSessionHas('error');
        $this->assertSame('unpaid', $booking->fresh()->payment_status);

        // Non-GCash payment method is rejected in sandbox
        $this->post(route('paymongo.sandbox.complete', ['checkoutId' => $checkoutId]), [
            'action' => 'succeed',
            'payment_method' => 'paymaya',
        ])->assertSessionHasErrors('payment_method');

        // Complete simulated payment with "succeed" using GCash
        $completeResponse = $this->post(route('paymongo.sandbox.complete', ['checkoutId' => $checkoutId]), [
            'action' => 'succeed',
            'payment_method' => 'gcash',
        ]);

        $completeResponse->assertRedirect();
        $followResponse = $this->get($completeResponse->headers->get('Location'));
        $followResponse->assertRedirect(route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3]));
        $followResponse->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('verified', $booking->payment_status);
        $this->assertSame('paymongo_gcash', $booking->payment_method);
        $this->assertSame('full', $booking->payment_scope);
        $this->assertEquals(4150.00, (float) $booking->paid_amount);

        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'advance_deposit',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'platform_fee',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'stay_payment',
            'amount' => 3000.00,
            'status' => 'completed',
        ]);
    }

    public function test_paymongo_sandbox_fail_and_cancel_keep_booking_unpaid(): void
    {
        Config::set('services.paymongo.secret_key', '');
        Config::set('services.paymongo.sandbox_simulation', true);

        $booking = $this->createUnpaidBooking('DS-PM-FAIL-01');

        $this->post(route('compliance.paymongo.checkout', $booking->booking_code), [
            'payment_scope' => 'deposit',
        ]);

        $checkoutId = $booking->fresh()->paymongo_checkout_id;

        $failResponse = $this->post(route('paymongo.sandbox.complete', ['checkoutId' => $checkoutId]), [
            'action' => 'fail',
            'payment_method' => 'gcash',
        ]);

        $failResponse->assertRedirect(route('compliance.paymongo.cancel', ['bookingCode' => $booking->booking_code]));

        $cancelPage = $this->get($failResponse->headers->get('Location'));
        $cancelPage->assertRedirect(route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3]));
        $cancelPage->assertSessionHas('error');

        $this->assertSame('unpaid', $booking->fresh()->payment_status);
    }

    public function test_paymongo_webhook_verifies_signature_and_marks_booking_paid(): void
    {
        $webhookSecret = 'whsec_test_secret_123';
        Config::set('services.paymongo.webhook_secret', $webhookSecret);

        $booking = $this->createUnpaidBooking('DS-PM-WH-01');
        $booking->update(['paymongo_checkout_id' => 'cs_wh_987654', 'payment_scope' => 'deposit']);

        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_wh_987654',
                        'attributes' => [
                            'reference_number' => $booking->booking_code,
                            'metadata' => [
                                'booking_code' => $booking->booking_code,
                                'payment_scope' => 'deposit',
                            ],
                            'payments' => [
                                [
                                    'id' => 'pay_wh_555666',
                                    'attributes' => [
                                        'amount' => 100000,
                                        'status' => 'paid',
                                        'source' => ['type' => 'gcash'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        // Invalid signature is rejected
        $this->postJson(route('webhooks.paymongo'), json_decode($payload, true), [
            'Paymongo-Signature' => 't=1700000000,te=invalid_signature',
        ])->assertStatus(401);

        // Valid signature succeeds
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $webhookSecret);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$signature}",
            ],
            $payload
        )->assertOk();

        $booking->refresh();
        $this->assertSame('verified', $booking->payment_status);
        $this->assertSame('pay_wh_555666', $booking->paymongo_payment_id);
        $this->assertSame('paymongo_gcash', $booking->payment_method);
    }

    public function test_cancelled_booking_cannot_initiate_paymongo_checkout(): void
    {
        $booking = $this->createUnpaidBooking('DS-PM-CANC-01');
        $booking->update(['status' => 'cancelled']);

        $this->post(route('compliance.paymongo.checkout', $booking->booking_code))
            ->assertSessionHas('error');

        $this->assertNull($booking->fresh()->paymongo_checkout_id);
    }

    public function test_gcash_transaction_is_traceable_on_both_buyer_and_seller_accounts(): void
    {
        Config::set('services.paymongo.secret_key', '');
        Config::set('services.paymongo.sandbox_simulation', true);

        $buyer = User::where('email', 'customer@directstay.local')->firstOrFail();
        $seller = User::where('email', 'host@directstay.local')->firstOrFail();
        $otherBuyer = User::factory()->create(['role' => 'guest']);

        $booking = $this->createUnpaidBooking('DS-TRACE-01');
        $booking->update([
            'user_id' => $buyer->id,
            'guest_name' => $buyer->name,
            'guest_email' => $buyer->email,
        ]);

        $this->post(route('compliance.paymongo.checkout', $booking->booking_code), [
            'payment_scope' => 'deposit',
        ]);

        $checkoutId = $booking->fresh()->paymongo_checkout_id;
        $complete = $this->post(route('paymongo.sandbox.complete', ['checkoutId' => $checkoutId]), [
            'action' => 'succeed',
            'payment_method' => 'gcash',
        ]);
        $this->get($complete->headers->get('Location'));

        $booking->refresh();
        $paymentRef = $booking->paymongo_payment_id;
        $this->assertNotNull($paymentRef);

        // 1. Buyer sees the transaction in My Transactions & can view Official Receipt
        $this->actingAs($buyer)
            ->get(route('customer.transactions'))
            ->assertOk()
            ->assertSee($booking->booking_code)
            ->assertSee($paymentRef)
            ->assertSee('GCash');

        $this->actingAs($buyer)
            ->get(route('transactions.receipt', $booking->booking_code))
            ->assertOk()
            ->assertSee('Buyer Receipt (Payment Transmitted)')
            ->assertSee($paymentRef)
            ->assertSee($seller->email)
            ->assertSee($buyer->email);

        // 2. Seller (Host) sees the exact same transaction in Seller Ledger & can view Official Receipt
        $this->actingAs($seller)
            ->get(route('host.transactions.index'))
            ->assertOk()
            ->assertSee($booking->booking_code)
            ->assertSee($paymentRef)
            ->assertSee($buyer->name);

        $this->actingAs($seller)
            ->get(route('transactions.receipt', $booking->booking_code))
            ->assertOk()
            ->assertSee('Seller Receipt (Payment Received)')
            ->assertSee($paymentRef)
            ->assertSee($seller->email)
            ->assertSee($buyer->email);

        // 3. Unrelated buyer cannot view another buyer's receipt or host ledger
        $this->actingAs($otherBuyer)
            ->get(route('transactions.receipt', $booking->booking_code))
            ->assertForbidden();

        $this->actingAs($otherBuyer)
            ->get(route('host.transactions.index'))
            ->assertForbidden();

        // 4. Host cannot open guest-only My Transactions page
        $this->actingAs($seller)
            ->get(route('customer.transactions'))
            ->assertForbidden();
    }

    public function test_paymongo_api_checkout_verifies_qrph_payment_successfully(): void
    {
        Config::set('services.paymongo.secret_key', 'sk_test_dummy_key');
        Config::set('services.paymongo.base_url', 'https://api.paymongo.com/v1');

        Http::preventStrayRequests();
        Http::fake([
            'api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_qrph_001',
                    'type' => 'checkout_session',
                    'attributes' => [
                        'checkout_url' => 'https://checkout.paymongo.com/cs_test_qrph_001',
                    ],
                ],
            ], 200),
            'api.paymongo.com/v1/checkout_sessions/cs_test_qrph_001' => Http::response([
                'data' => [
                    'id' => 'cs_test_qrph_001',
                    'type' => 'checkout_session',
                    'attributes' => [
                        'status' => 'paid',
                        'payment_intent' => [
                            'id' => 'pi_test_qrph_001',
                            'attributes' => [
                                'status' => 'succeeded',
                                'amount' => 100000,
                            ],
                        ],
                        'payments' => [
                            [
                                'id' => 'pay_qrph_abc999',
                                'attributes' => [
                                    'amount' => 100000,
                                    'status' => 'paid',
                                    'payment_method' => ['type' => 'qrph'],
                                ],
                            ],
                        ],
                        'metadata' => [
                            'payment_scope' => 'deposit',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $booking = $this->createUnpaidBooking('DS-PM-QRPH-01');

        $this->post(route('compliance.paymongo.checkout', $booking->booking_code), [
            'payment_scope' => 'deposit',
        ])->assertRedirect('https://checkout.paymongo.com/cs_test_qrph_001');

        $this->get(route('compliance.paymongo.success', ['bookingCode' => $booking->booking_code]))
            ->assertRedirect(route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3]))
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('verified', $booking->payment_status);
        $this->assertSame('pay_qrph_abc999', $booking->paymongo_payment_id);
        $this->assertSame('paymongo_qrph', $booking->payment_method);
    }

    public function test_paymongo_sandbox_simulation_supports_qrph_payment_method(): void
    {
        Config::set('services.paymongo.secret_key', '');
        Config::set('services.paymongo.sandbox_simulation', true);

        $buyer = User::where('email', 'customer@directstay.local')->firstOrFail();
        $booking = $this->createUnpaidBooking('DS-SIM-QRPH-01');
        $booking->update(['user_id' => $buyer->id]);

        $this->post(route('compliance.paymongo.checkout', $booking->booking_code), [
            'payment_scope' => 'deposit',
        ]);

        $checkoutId = $booking->fresh()->paymongo_checkout_id;

        $this->get(route('paymongo.sandbox', ['checkoutId' => $checkoutId]))
            ->assertOk()
            ->assertSee('QR Ph (Scan to Pay)');

        $completeResponse = $this->post(route('paymongo.sandbox.complete', ['checkoutId' => $checkoutId]), [
            'action' => 'succeed',
            'payment_method' => 'qrph',
        ]);

        $this->get($completeResponse->headers->get('Location'))
            ->assertRedirect(route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3]))
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame('verified', $booking->payment_status);
        $this->assertSame('paymongo_qrph', $booking->payment_method);

        $this->actingAs($buyer)
            ->get(route('transactions.receipt', $booking->booking_code))
            ->assertOk()
            ->assertSee('QR Ph');
    }

    public function test_paymongo_webhook_accepts_qrph_and_ignores_unsupported_methods(): void
    {
        $webhookSecret = 'whsec_test_qrph_123';
        Config::set('services.paymongo.webhook_secret', $webhookSecret);

        $booking = $this->createUnpaidBooking('DS-WH-QRPH-01');
        $booking->update(['paymongo_checkout_id' => 'cs_wh_qrph_01', 'payment_scope' => 'deposit']);

        $unsupportedPayload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_wh_qrph_01',
                        'attributes' => [
                            'booking_code' => $booking->booking_code,
                            'payments' => [
                                [
                                    'id' => 'pay_wh_card_01',
                                    'attributes' => [
                                        'amount' => 100000,
                                        'status' => 'paid',
                                        'source' => ['type' => 'card'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = (string) time();
        $unsupportedSig = hash_hmac('sha256', $timestamp.'.'.$unsupportedPayload, $webhookSecret);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$unsupportedSig}",
            ],
            $unsupportedPayload
        )->assertOk()->assertJson(['ignored' => 'Only GCash and QR Ph payments are accepted.']);

        $this->assertSame('unpaid', $booking->fresh()->payment_status);

        $qrphPayload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_wh_qrph_01',
                        'attributes' => [
                            'reference_number' => $booking->booking_code,
                            'metadata' => [
                                'booking_code' => $booking->booking_code,
                                'payment_scope' => 'deposit',
                            ],
                            'payments' => [
                                [
                                    'id' => 'pay_wh_qrph_777',
                                    'attributes' => [
                                        'amount' => 100000,
                                        'status' => 'paid',
                                        'payment_method' => ['type' => 'qrph'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $qrphSig = hash_hmac('sha256', $timestamp.'.'.$qrphPayload, $webhookSecret);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$qrphSig}",
            ],
            $qrphPayload
        )->assertOk();

        $booking->refresh();
        $this->assertSame('verified', $booking->payment_status);
        $this->assertSame('pay_wh_qrph_777', $booking->paymongo_payment_id);
        $this->assertSame('paymongo_qrph', $booking->payment_method);
    }

    public function test_checkout_inspection_automatically_refunds_paymongo_gcash_deposit(): void
    {
        Config::set('services.paymongo.secret_key', 'sk_test_dummy_key');
        Config::set('services.paymongo.base_url', 'https://api.paymongo.com/v1');

        Http::preventStrayRequests();
        Http::fake([
            'api.paymongo.com/v1/refunds' => Http::response([
                'data' => [
                    'id' => 'ref_gcash_12345',
                    'type' => 'refund',
                    'attributes' => [
                        'amount' => 50000,
                        'status' => 'pending',
                        'payment_id' => 'pay_gcash_001',
                    ],
                ],
            ], 200),
        ]);

        $host = User::where('email', 'host@directstay.local')->firstOrFail();
        $booking = $this->createUnpaidBooking('DS-REF-GCASH');
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'paymongo_payment_id' => 'pay_gcash_001',
            'payment_method' => 'paymongo_gcash',
            'paid_amount' => 1000.00,
        ]);

        $this->actingAs($host)
            ->post(route('host.checkouts.store', $booking), [
                'inventory_status' => ['aircon' => 'intact'],
                'cleaning_type' => 'none',
                'penalty_garbage' => '1',
            ])
            ->assertRedirect(route('host.checkouts.show', $booking))
            ->assertSessionHas('success');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.paymongo.com/v1/refunds'
                && $request['data']['attributes']['payment_id'] === 'pay_gcash_001'
                && $request['data']['attributes']['amount'] === 50000;
        });

        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'deposit_refund',
            'amount' => 500.00,
            'reference_number' => 'ref_gcash_12345',
        ]);
    }

    public function test_checkout_inspection_generates_and_displays_qrph_refund_transfer_link(): void
    {
        Config::set('services.paymongo.secret_key', 'sk_test_dummy_key');
        Config::set('services.paymongo.refunds_url', 'https://refunds-api.paymongo.com/v1');

        Http::preventStrayRequests();
        Http::fake([
            'refunds-api.paymongo.com/v1/refunds' => Http::response([
                'data' => [
                    'id' => 'ref_qrph_98765',
                    'type' => 'refund',
                    'attributes' => [
                        'amount' => 100000,
                        'status' => 'processing',
                        'payment_id' => 'pay_qrph_002',
                        'transfer_link' => 'https://transfer.paymongo.com/claim-98765',
                    ],
                ],
            ], 200),
        ]);

        $host = User::where('email', 'host@directstay.local')->firstOrFail();
        $booking = $this->createUnpaidBooking('DS-REF-QRPH');
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'paymongo_payment_id' => 'pay_qrph_002',
            'payment_method' => 'paymongo_qrph',
            'paid_amount' => 1000.00,
        ]);

        $this->actingAs($host)
            ->post(route('host.checkouts.store', $booking), [
                'inventory_status' => ['aircon' => 'intact'],
                'cleaning_type' => 'none',
            ])
            ->assertRedirect(route('host.checkouts.show', $booking))
            ->assertSessionHas('success');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://refunds-api.paymongo.com/v1/refunds'
                && $request['data']['attributes']['payment_id'] === 'pay_qrph_002'
                && $request['data']['attributes']['amount'] === 100000;
        });

        $this->actingAs($host)
            ->get(route('host.checkouts.show', $booking))
            ->assertOk()
            ->assertSee('ref_qrph_98765')
            ->assertSee('https://transfer.paymongo.com/claim-98765');

        $this->get(route('compliance.status', $booking->booking_code))
            ->assertOk()
            ->assertSee('https://transfer.paymongo.com/claim-98765')
            ->assertSee('Claim Refund');
    }

    public function test_checkout_inspection_still_settles_if_paymongo_refund_api_fails(): void
    {
        Config::set('services.paymongo.secret_key', 'sk_test_dummy_key');
        Config::set('services.paymongo.base_url', 'https://api.paymongo.com/v1');

        Http::preventStrayRequests();
        Http::fake([
            'api.paymongo.com/v1/refunds' => Http::response([
                'errors' => [['detail' => 'Insufficient wallet balance for refund.']],
            ], 422),
        ]);

        $host = User::where('email', 'host@directstay.local')->firstOrFail();
        $booking = $this->createUnpaidBooking('DS-REF-FAIL');
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'paymongo_payment_id' => 'pay_gcash_fail',
            'payment_method' => 'paymongo_gcash',
            'paid_amount' => 1000.00,
        ]);

        $this->actingAs($host)
            ->post(route('host.checkouts.store', $booking), [
                'inventory_status' => ['aircon' => 'intact'],
                'cleaning_type' => 'none',
            ])
            ->assertRedirect(route('host.checkouts.show', $booking))
            ->assertSessionHas('error');

        $this->assertSame('checked_out', $booking->fresh()->status);
        $this->assertDatabaseHas('checkouts', [
            'booking_id' => $booking->id,
            'deposit_refunded' => 1000.00,
        ]);
    }
}
