<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Booking;
use App\Models\ComplianceDocument;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DirectStaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QualityAssuranceTest extends TestCase
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
     * Test booking rejects reservation when guest count exceeds the unit's maximum occupancy.
     */
    public function test_booking_rejects_guest_count_exceeding_unit_max_occupancy(): void
    {
        $unit = Unit::firstOrFail();
        $unit->update(['max_guests' => 2]);

        $response = $this->from(route('units.show', $unit))->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'guest_name' => 'Overcapacity Guest',
            'guest_email' => 'overcapacity@example.com',
            'guest_phone' => '09170000000',
            'guest_count' => 5, // Exceeds max_guests of 2
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDay()->toDateString(),
        ]);

        $response->assertRedirect(route('units.show', $unit));
        $response->assertSessionHasErrors('guest_count');
        $this->assertDatabaseMissing('bookings', [
            'guest_email' => 'overcapacity@example.com',
        ]);
    }

    /**
     * Test booking validation rejects checkout dates that are before or equal to check-in.
     */
    public function test_booking_rejects_invalid_date_chronology(): void
    {
        $unit = Unit::firstOrFail();

        // 1. Same-day checkout
        $sameDay = Carbon::tomorrow()->toDateString();
        $response1 = $this->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'guest_name' => 'Same Day Guest',
            'guest_email' => 'sameday@example.com',
            'guest_phone' => '09170000000',
            'guest_count' => 1,
            'check_in_date' => $sameDay,
            'check_out_date' => $sameDay,
        ]);
        $response1->assertSessionHasErrors('check_out_date');

        // 2. Checkout before check-in
        $response2 = $this->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'guest_name' => 'Backwards Guest',
            'guest_email' => 'backwards@example.com',
            'guest_phone' => '09170000000',
            'guest_count' => 1,
            'check_in_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::tomorrow()->toDateString(),
        ]);
        $response2->assertSessionHasErrors('check_out_date');
    }

    /**
     * Test booking validation rejects check-in dates in the past.
     */
    public function test_booking_rejects_past_checkin_date(): void
    {
        $unit = Unit::firstOrFail();

        $response = $this->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'guest_name' => 'Past Guest',
            'guest_email' => 'past@example.com',
            'guest_phone' => '09170000000',
            'guest_count' => 1,
            'check_in_date' => Carbon::yesterday()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertSessionHasErrors('check_in_date');
    }

    /**
     * Test booking safely ignores inactive add-ons or add-ons assigned to a different unit.
     */
    public function test_booking_ignores_inactive_or_foreign_add_ons(): void
    {
        $unitA = Unit::firstOrFail();
        $unitB = Unit::where('id', '!=', $unitA->id)->firstOrFail();

        // Create inactive add-on
        $inactiveAddon = AddOn::create([
            'unit_id' => null,
            'name' => 'Inactive Item',
            'price' => 500.00,
            'is_active' => false,
        ]);

        // Create add-on restricted to unit B
        $foreignAddon = AddOn::create([
            'unit_id' => $unitB->id,
            'name' => 'Unit B Private Item',
            'price' => 300.00,
            'is_active' => true,
        ]);

        // Attempt to book unit A with inactive & foreign add-ons
        $checkIn = Carbon::tomorrow()->toDateString();
        $checkOut = Carbon::tomorrow()->addDay()->toDateString();

        $this->post(route('bookings.store'), [
            'unit_id' => $unitA->id,
            'guest_name' => 'Addon Tester',
            'guest_email' => 'addontest@example.com',
            'guest_phone' => '09179998877',
            'guest_count' => 1,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'add_ons' => [
                $inactiveAddon->id => 2,
                $foreignAddon->id => 1,
            ],
        ]);

        $booking = Booking::where('guest_email', 'addontest@example.com')->firstOrFail();

        // Inactive and foreign add-ons should not be tallied
        $this->assertEquals(0.00, (float) $booking->add_ons_amount);
        $this->assertCount(0, $booking->bookingAddOns);
    }

    /**
     * Test non-host guests are forbidden from accessing host routes.
     */
    public function test_guest_is_forbidden_from_host_routes(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);

        $this->actingAs($guest)
            ->get(route('host.dashboard'))
            ->assertForbidden();

        $this->actingAs($guest)
            ->get(route('host.units.index'))
            ->assertForbidden();

        $this->actingAs($guest)
            ->get(route('host.verifications.index'))
            ->assertForbidden();
    }

    /**
     * Test host login rejects customer/guest user credentials.
     */
    public function test_host_login_rejects_guest_user_role(): void
    {
        $guest = User::factory()->create([
            'role' => 'guest',
            'password' => bcrypt('guestpassword123'),
        ]);

        $response = $this->post(route('host.login.post'), [
            'email' => $guest->email,
            'password' => 'guestpassword123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test compliance uploads are rejected if the booking is cancelled.
     */
    public function test_compliance_uploads_are_rejected_for_cancelled_booking(): void
    {
        $unit = Unit::firstOrFail();
        $booking = Booking::create([
            'booking_code' => 'DS-CANCELLED-01',
            'unit_id' => $unit->id,
            'guest_name' => 'Cancelled Guest',
            'guest_email' => 'cancelled@example.com',
            'guest_phone' => '09170001122',
            'guest_count' => 1,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'nights_count' => 1,
            'base_amount' => 1500.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 75.00,
            'total_amount' => 2575.00,
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
        ]);

        // Attempt payment upload
        $receipt = UploadedFile::fake()->image('receipt.jpg');
        $this->post(route('compliance.payment', $booking->booking_code), [
            'payment_receipt' => $receipt,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'payment_receipt',
        ]);

        // Attempt ID upload
        $id = UploadedFile::fake()->image('id.jpg');
        $selfie = UploadedFile::fake()->image('selfie.jpg');
        $this->post(route('compliance.identity', $booking->booking_code), [
            'gov_ids' => [$id],
            'selfie' => $selfie,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'gov_id_0',
        ]);
    }

    /**
     * Test unconfirmed booking cannot download gate pass or rental agreement.
     */
    public function test_unconfirmed_booking_cannot_download_gate_pass_or_rental_agreement(): void
    {
        $unit = Unit::firstOrFail();
        $booking = Booking::create([
            'booking_code' => 'DS-PENDING-DOWNLOAD',
            'unit_id' => $unit->id,
            'guest_name' => 'Pending Guest',
            'guest_email' => 'pending@example.com',
            'guest_phone' => '09170003344',
            'guest_count' => 1,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'nights_count' => 1,
            'base_amount' => 1500.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 75.00,
            'total_amount' => 2575.00,
            'status' => 'pending_verification',
            'payment_status' => 'submitted',
        ]);

        $this->get(route('compliance.downloadGatePass', $booking->booking_code))
            ->assertForbidden();

        $this->get(route('compliance.downloadRentalAgreement', $booking->booking_code))
            ->assertForbidden();
    }

    /**
     * Test host cannot approve a cancelled booking.
     */
    public function test_host_cannot_approve_cancelled_booking(): void
    {
        $host = User::where('role', 'host')->firstOrFail();
        $unit = Unit::firstOrFail();

        $booking = Booking::create([
            'booking_code' => 'DS-HOST-APPROVE-CANCELLED',
            'unit_id' => $unit->id,
            'guest_name' => 'Already Cancelled',
            'guest_email' => 'cancelled_approve@example.com',
            'guest_phone' => '09175551122',
            'guest_count' => 1,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'nights_count' => 1,
            'base_amount' => 1500.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 75.00,
            'total_amount' => 2575.00,
            'status' => 'cancelled',
            'payment_status' => 'submitted',
        ]);

        $this->actingAs($host)
            ->post(route('host.verifications.approve', $booking))
            ->assertSessionHas('error');

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    /**
     * Test photo deletion restricts paths to those in the unit's images array.
     */
    public function test_host_photo_deletion_validates_path_ownership(): void
    {
        $host = User::where('role', 'host')->firstOrFail();
        $unit = Unit::firstOrFail();
        $unit->update(['images' => ['images/units/'.$unit->id.'/valid_photo.jpg']]);

        // Attempt deleting a foreign or malicious path
        $response = $this->actingAs($host)->post(route('host.units.photos.delete', $unit), [
            'image' => '../../../../some_secret_file.jpg',
        ]);

        $response->assertSessionHasErrors('image');
        $unit->refresh();
        $this->assertCount(1, $unit->images);
    }

    /**
     * Test customer settings require current_password to change password.
     */
    public function test_customer_settings_requires_current_password_when_setting_new_password(): void
    {
        $guest = User::factory()->create([
            'role' => 'guest',
            'password' => bcrypt('original_password'),
        ]);

        // Attempt changing password without current_password
        $response = $this->actingAs($guest)->put(route('customer.settings.update'), [
            'name' => $guest->name,
            'email' => $guest->email,
            'phone' => '09171234567',
            'password' => 'new_secret_123',
            'password_confirmation' => 'new_secret_123',
        ]);

        $response->assertSessionHasErrors('current_password');

        // Provide incorrect current_password
        $responseWrongCurrent = $this->actingAs($guest)->put(route('customer.settings.update'), [
            'name' => $guest->name,
            'email' => $guest->email,
            'phone' => '09171234567',
            'current_password' => 'wrong_password_entered',
            'password' => 'new_secret_123',
            'password_confirmation' => 'new_secret_123',
        ]);

        $responseWrongCurrent->assertSessionHasErrors('current_password');
    }

    /**
     * Test progressive occupant ID uploads, companion roster saving, and guest document streaming.
     */
    public function test_identity_vault_progressive_occupant_id_uploads_and_roster(): void
    {
        $unit = Unit::firstOrFail();
        $booking = Booking::create([
            'booking_code' => 'DS-VAULT-TRIO',
            'unit_id' => $unit->id,
            'guest_name' => 'Danilo Reyes',
            'guest_email' => 'danilo@example.com',
            'guest_phone' => '09171112233',
            'guest_count' => 3, // 3 Guests -> 3 IDs required
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

        // 1. Save Companion Roster (Phase 1)
        $rosterResponse = $this->post(route('compliance.roster', $booking->booking_code), [
            'lead_name' => 'Danilo Reyes',
            'lead_phone' => '09171112233',
            'companion_names' => ['Elena Reyes', 'Marco Reyes'],
            'companion_relationships' => ['Spouse', 'Child'],
            'companion_id_types' => ['Philippine Passport', 'School ID'],
        ]);
        $rosterResponse->assertSessionHas('success');
        $booking->refresh();
        $this->assertCount(2, $booking->guest_roster);
        $this->assertEquals('Elena Reyes', $booking->guest_roster[0]['name']);

        // 2. Upload Occupant 0 ID (Lead Guest) individually
        $leadId = UploadedFile::fake()->image('danilo_passport.jpg');
        $this->post(route('compliance.identity', $booking->booking_code), [
            'occupant_index' => 0,
            'id_type' => 'Philippine Passport',
            'id_number' => 'P1234567B',
            'gov_id_single' => $leadId,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'gov_id_0',
        ]);

        // 3. Upload Occupant 1 ID (Companion 1) individually
        $comp1Id = UploadedFile::fake()->image('elena_id.jpg');
        $this->post(route('compliance.identity', $booking->booking_code), [
            'occupant_index' => 1,
            'id_type' => 'Driver\'s License',
            'gov_id_single' => $comp1Id,
        ])->assertSessionHas('success');

        // 4. Upload Occupant 2 ID (Companion 2) individually
        $comp2Id = UploadedFile::fake()->image('marco_id.jpg');
        $this->post(route('compliance.identity', $booking->booking_code), [
            'occupant_index' => 2,
            'id_type' => 'School ID',
            'gov_id_single' => $comp2Id,
        ])->assertSessionHas('success');

        // 5. Upload Biometric Selfie
        $selfie = UploadedFile::fake()->image('danilo_selfie.jpg');
        $this->post(route('compliance.identity', $booking->booking_code), [
            'selfie' => $selfie,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'gov_id_1',
        ]);
        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'gov_id_2',
        ]);
        $this->assertDatabaseHas('compliance_documents', [
            'booking_id' => $booking->id,
            'document_type' => 'selfie',
        ]);

        // 6. Test guest document stream
        $doc = ComplianceDocument::where('booking_id', $booking->id)
            ->where('document_type', 'gov_id_0')
            ->firstOrFail();

        $streamResponse = $this->get(route('compliance.document', [
            'bookingCode' => $booking->booking_code,
            'document' => $doc->id,
        ]));
        $streamResponse->assertStatus(200);
    }
}
