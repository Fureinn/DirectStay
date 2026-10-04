<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationCodeMailable;
use App\Mail\GuestGatePassMailable;
use App\Models\AddOn;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DirectStaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectStayWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DirectStaySeeder::class);
        Storage::fake('local');
        Mail::fake();
    }

    /**
     * Test catalog loads and displays seeded buildings and units.
     */
    public function test_catalog_displays_units_and_buildings(): void
    {
        $response = $this->get(route('units.index'));

        $response->assertStatus(200);
        $response->assertSee('Building N');
        $response->assertSee('Building P');
        $response->assertSee('Unit N343');
        $response->assertSee('Unit P718');
    }

    /**
     * Test a guest can view and update their account settings.
     */
    public function test_guest_can_update_account_settings(): void
    {
        $guest = User::factory()->create([
            'role' => 'guest',
            'phone' => '09170000000',
        ]);

        $this->actingAs($guest)
            ->get(route('customer.settings.edit'))
            ->assertOk()
            ->assertSee('Account settings');

        $this->actingAs($guest)
            ->put(route('customer.settings.update'), [
                'name' => 'Updated Guest',
                'email' => 'updated-guest@example.com',
                'phone' => '09179999999',
            ])
            ->assertSessionHas('success');

        $guest->refresh();

        $this->assertSame('Updated Guest', $guest->name);
        $this->assertSame('updated-guest@example.com', $guest->email);
        $this->assertSame('09179999999', $guest->phone);
    }

    /**
     * Test hosts cannot access guest account settings.
     */
    public function test_host_is_forbidden_from_guest_account_settings(): void
    {
        $host = User::factory()->create(['role' => 'host']);

        $this->actingAs($host)
            ->get(route('customer.settings.edit'))
            ->assertForbidden();
    }

    /**
     * Test booking creation properly calculates base rate, add-ons, ₱1,000 deposit, and ₱100 flat fee.
     */
    public function test_booking_creation_calculates_fees_deposit_and_addons(): void
    {
        $unit = Unit::where('unit_number', 'Unit N343')->firstOrFail();
        $pillow = AddOn::where('name', 'Extra Pillow')->firstOrFail();

        $checkIn = Carbon::tomorrow()->toDateString();
        $checkOut = Carbon::tomorrow()->addDays(2)->toDateString(); // 2 nights

        $response = $this->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'guest_name' => 'Maria Santos',
            'guest_email' => 'maria@example.com',
            'guest_phone' => '09171112233',
            'guest_count' => 2,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'add_ons' => [
                $pillow->id => 2, // 2 x 25.00 = ₱50.00
            ],
        ]);

        $booking = Booking::latest()->first();

        $response->assertRedirect(route('compliance.portal', ['bookingCode' => $booking->booking_code]));

        // Math: 2 nights x ₱1,500 = ₱3,000
        // Add-ons = ₱50
        // Deposit = ₱1,000
        // Platform 5% fee = ₱150 (5% of ₱3,000)
        // Total = ₱4,200
        $this->assertEquals(2, $booking->nights_count);
        $this->assertEquals(3000.00, (float) $booking->base_amount);
        $this->assertEquals(50.00, (float) $booking->add_ons_amount);
        $this->assertEquals(1000.00, (float) $booking->advance_deposit_amount);
        $this->assertEquals(150.00, (float) $booking->platform_fee);
        $this->assertEquals(4200.00, (float) $booking->total_amount);

        // Verify ledger transactions
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'advance_deposit',
            'amount' => 1000.00,
        ]);
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'platform_fee',
            'amount' => 150.00,
        ]);
    }

    /**
     * Test double-booking prevention blocks overlapping reservations.
     */
    public function test_prevents_double_booking_for_overlapping_dates(): void
    {
        $unit = Unit::firstOrFail();

        Booking::create([
            'booking_code' => 'DS-TEST-EXISTING',
            'unit_id' => $unit->id,
            'guest_name' => 'Existing Guest',
            'guest_email' => 'existing@example.com',
            'guest_phone' => '09170000000',
            'guest_count' => 2,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(3)->toDateString(),
            'nights_count' => 3,
            'base_amount' => 4500.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 100.00,
            'total_amount' => 5600.00,
            'status' => 'confirmed',
            'payment_status' => 'verified',
        ]);

        // Attempt overlapping reservation
        $response = $this->from(route('units.show', $unit))->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'guest_name' => 'Conflicting Guest',
            'guest_email' => 'conflict@example.com',
            'guest_phone' => '09172223344',
            'guest_count' => 2,
            'check_in_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(4)->toDateString(),
        ]);

        $response->assertSessionHasErrors('check_in_date');
    }

    /**
     * Test guest compliance document uploads and private vault storage.
     */
    public function test_compliance_file_uploads_store_securely_in_private_vault(): void
    {
        $unit = Unit::firstOrFail();
        $booking = Booking::create([
            'booking_code' => 'DS-VAULT-01',
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
            'platform_fee' => 100.00,
            'total_amount' => 4100.00,
            'status' => 'pending_verification',
            'payment_status' => 'unpaid',
        ]);

        // Upload Payment Receipt
        $receipt = UploadedFile::fake()->image('gcash_receipt.jpg');
        $this->post(route('compliance.payment', $booking->booking_code), [
            'payment_receipt' => $receipt,
            'reference_number' => 'GCASH-998877',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'payment_receipt',
        ]);

        // Upload Gov ID & Selfie
        $leadGuestId = UploadedFile::fake()->image('passport.jpg');
        $companionId = UploadedFile::fake()->image('companion-id.jpg');
        $selfie = UploadedFile::fake()->image('selfie.jpg');
        $this->post(route('compliance.identity', $booking->booking_code), [
            'gov_ids' => [$leadGuestId, $companionId],
            'selfie' => $selfie,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'gov_id_0',
        ]);
        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'gov_id_1',
        ]);
        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'selfie',
        ]);

        // Accept Waiver & Roster
        $this->post(route('compliance.waiver', $booking->booking_code), [
            'agree_rules' => '1',
            'companion_names' => ['Ana Dela Cruz'],
            'companion_relationships' => ['Spouse'],
        ])->assertRedirect(route('compliance.status', $booking->booking_code));

        $booking->refresh();
        $this->assertNotNull($booking->waiver_accepted_at);
        $this->assertCount(1, $booking->guest_roster);
    }

    /**
     * Test host approval compiles building gate pass and sends email.
     */
    public function test_host_approval_triggers_gate_pass_compilation_and_confirmation(): void
    {
        $host = User::where('role', 'host')->firstOrFail();
        $unit = Unit::where('unit_number', 'Unit N343')->firstOrFail();

        $booking = Booking::create([
            'booking_code' => 'DS-APPROVE-01',
            'unit_id' => $unit->id,
            'guest_name' => 'Carlos Dizon',
            'guest_email' => 'carlos@example.com',
            'guest_phone' => '09173334455',
            'guest_count' => 1,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'nights_count' => 1,
            'base_amount' => 1500.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 100.00,
            'total_amount' => 2600.00,
            'status' => 'pending_verification',
            'payment_status' => 'submitted',
        ]);

        $booking->complianceDocuments()->createMany([
            [
                'document_type' => 'gov_id_0',
                'file_path' => 'compliance/'.$booking->booking_code.'/lead-guest-id.jpg',
                'original_filename' => 'lead-guest-id.jpg',
                'mime_type' => 'image/jpeg',
            ],
            [
                'document_type' => 'selfie',
                'file_path' => 'compliance/'.$booking->booking_code.'/lead-guest-selfie.jpg',
                'original_filename' => 'lead-guest-selfie.jpg',
                'mime_type' => 'image/jpeg',
            ],
        ]);

        $response = $this->actingAs($host)->post(route('host.verifications.approve', $booking));

        $response->assertRedirect(route('host.verifications.show', $booking));

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals('verified', $booking->payment_status);
        $this->assertNotNull($booking->verified_at);

        // Verify Mailable sent to guest with CC to building lobby security
        Mail::assertSent(GuestGatePassMailable::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->guest_email) &&
                   $mail->hasCc($booking->unit->building->admin_email);
        });
    }

    /**
     * Test digital checkout inspection and automated penalty deduction against deposit.
     */
    public function test_checkout_inspection_and_penalty_engine_calculates_net_refund(): void
    {
        $host = User::where('role', 'host')->firstOrFail();
        $unit = Unit::firstOrFail();

        $booking = Booking::create([
            'booking_code' => 'DS-CHECKOUT-01',
            'unit_id' => $unit->id,
            'guest_name' => 'Elena Gomez',
            'guest_email' => 'elena@example.com',
            'guest_phone' => '09175556677',
            'guest_count' => 2,
            'check_in_date' => Carbon::yesterday()->toDateString(),
            'check_out_date' => Carbon::today()->toDateString(),
            'nights_count' => 1,
            'base_amount' => 1500.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 100.00,
            'total_amount' => 2600.00,
            'status' => 'confirmed',
            'payment_status' => 'verified',
        ]);

        // Post checkout with:
        // - Unthrown garbage fine: ₱500
        // - Basic cleaning fee: ₱500
        // - Net deposit refund = ₱1,000 - ₱500 penalties = ₱500.00
        $response = $this->actingAs($host)->post(route('host.checkouts.store', $booking), [
            'inventory_status' => [
                'inverter_air_conditioner' => 'intact',
                '40_inch_android_tv' => 'intact',
            ],
            'cleaning_type' => 'basic',
            'penalty_garbage' => '1',
            'penalty_lost_key' => '0',
            'notes' => 'Left kitchen trash unthrown. Basic turnover cleaning dispatched.',
        ]);

        $response->assertRedirect(route('host.checkouts.show', $booking));

        $booking->refresh();
        $this->assertEquals('checked_out', $booking->status);

        $checkout = $booking->checkout;
        $this->assertNotNull($checkout);
        $this->assertEquals(500.00, (float) $checkout->cleaning_fee);
        $this->assertEquals(500.00, (float) $checkout->total_penalties);
        $this->assertEquals(500.00, (float) $checkout->deposit_refunded);

        // Verify penalty and refund transactions
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'penalty_deduction',
            'amount' => 500.00,
        ]);
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'type' => 'deposit_refund',
            'amount' => 500.00,
        ]);
    }

    /**
     * Test customer can register, log in, and view personal bookings.
     */
    public function test_customer_can_register_login_and_view_bookings(): void
    {
        // 1. Customer Registration
        $registerResponse = $this->post(route('customer.register.post'), [
            'name' => 'Ana Batungbakal',
            'email' => 'ana@example.com',
            'phone' => '09179998888',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $registerResponse->assertRedirect(route('customer.verify.show'));
        $this->assertGuest();

        $user = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertEquals('guest', $user->role);
        $this->assertEquals('09179998888', $user->phone);
        $this->assertNotNull($user->verification_code);

        Mail::assertSent(EmailVerificationCodeMailable::class, function ($mail) use ($user) {
            return $mail->hasTo('ana@example.com') && $mail->code === $user->verification_code;
        });

        // 1b. Submit Verification Code
        $verifyResponse = $this->post(route('customer.verify.post'), [
            'code' => $user->verification_code,
        ]);
        $verifyResponse->assertRedirect(route('customer.bookings'));
        $this->assertAuthenticated();

        // 2. Customer can view my-bookings
        $myBookingsResponse = $this->get(route('customer.bookings'));
        $myBookingsResponse->assertStatus(200);
        $myBookingsResponse->assertSee('Ana Batungbakal');

        // 3. Logout and Login
        $this->post(route('customer.logout'));
        $this->assertGuest();

        $loginResponse = $this->post(route('customer.login.post'), [
            'email' => 'ana@example.com',
            'password' => 'secret123',
        ]);
        $loginResponse->assertRedirect(route('customer.bookings'));
        $this->assertAuthenticated();
    }

    /**
     * Test host can upload unit photos and manage cover image.
     */
    public function test_host_can_upload_and_manage_unit_photos(): void
    {
        $host = User::where('role', 'host')->firstOrFail();
        $unit = Unit::firstOrFail();

        $photo1 = UploadedFile::fake()->image('living_room.jpg');
        $photo2 = UploadedFile::fake()->image('bedroom.jpg');

        $response = $this->actingAs($host)->post(route('host.units.photos.upload', $unit), [
            'photos' => [$photo1, $photo2],
        ]);

        $response->assertSessionHas('success');

        $unit->refresh();
        $this->assertNotNull($unit->cover_image);
        $this->assertGreaterThanOrEqual(2, count($unit->images));

        // Test set cover photo
        $newCover = $unit->images[1];
        $this->actingAs($host)->post(route('host.units.photos.cover', $unit), [
            'image' => $newCover,
        ])->assertSessionHas('success');

        $unit->refresh();
        $this->assertEquals($newCover, $unit->cover_image);
    }

    /**
     * Test confirmed guest can download both Deca Guest Form (Gate Pass) and Rental Agreement (Contract).
     */
    public function test_confirmed_guest_can_download_gate_pass_and_rental_agreement(): void
    {
        $unit = Unit::where('unit_number', 'Unit N343')->firstOrFail();

        $booking = Booking::create([
            'booking_code' => 'DS-DOWNLOAD-TEST',
            'unit_id' => $unit->id,
            'guest_name' => 'Kenry M. Cui',
            'guest_email' => 'kenry@example.com',
            'guest_phone' => '09178889911',
            'guest_count' => 2,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'nights_count' => 2,
            'base_amount' => 3000.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 150.00,
            'total_amount' => 4150.00,
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'waiver_accepted_at' => now(),
        ]);

        // Download Gate Pass
        $gatePassResponse = $this->get(route('compliance.downloadGatePass', $booking->booking_code));
        $gatePassResponse->assertStatus(200);
        $this->assertStringContainsString('pdf', $gatePassResponse->headers->get('content-type'));
        $this->assertStringContainsString('/MediaBox [0.000 0.000 612.000 792.000]', $gatePassResponse->getContent());
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page\b/', $gatePassResponse->getContent()));

        // Download Rental Agreement
        $agreementResponse = $this->get(route('compliance.downloadRentalAgreement', $booking->booking_code));
        $agreementResponse->assertStatus(200);
        $this->assertStringContainsString('pdf', $agreementResponse->headers->get('content-type'));
        $this->assertStringContainsString('/MediaBox [0.000 0.000 595.280 841.890]', $agreementResponse->getContent());
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page\b/', $agreementResponse->getContent()));
        $agreementResponse->assertStatus(200);
        $this->assertStringContainsString('pdf', $agreementResponse->headers->get('content-type'));
    }
}
