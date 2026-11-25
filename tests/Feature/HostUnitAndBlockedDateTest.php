<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DirectStaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostUnitAndBlockedDateTest extends TestCase
{
    use RefreshDatabase;

    protected User $host;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DirectStaySeeder::class);

        $this->host = User::where('role', 'host')->first() ?? User::factory()->create(['role' => 'host']);
    }

    /**
     * Test host can view the create unit page.
     */
    public function test_host_can_view_create_unit_page(): void
    {
        $response = $this->actingAs($this->host)->get(route('host.units.create'));

        $response->assertStatus(200);
        $response->assertSee('Add New Condominium Unit');
        $response->assertSee('Building / Complex Tower');
    }

    /**
     * Test host can successfully store a new unit.
     */
    public function test_host_can_store_new_unit(): void
    {
        $building = Building::first();

        $response = $this->actingAs($this->host)->post(route('host.units.store'), [
            'building_id' => $building->id,
            'unit_number' => 'N-999',
            'title' => 'Brand New Luxury Penthouse Suite',
            'base_price_per_night' => 2500.00,
            'advance_deposit_required' => 1000.00,
            'max_guests' => 6,
            'description' => 'Spectacular skyline view at Urban Deca Homes Ortigas.',
            'inventory_items' => 'Aircon, WiFi, Smart TV, Refrigerator',
        ]);

        $response->assertRedirect(route('host.units.index'));
        $this->assertDatabaseHas('units', [
            'unit_number' => 'N-999',
            'title' => 'Brand New Luxury Penthouse Suite',
            'max_guests' => 6,
        ]);
    }

    /**
     * Test host can manually block a date range.
     */
    public function test_host_can_manually_block_dates(): void
    {
        $unit = Unit::first();
        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(7)->toDateString();

        $response = $this->actingAs($this->host)->post(route('host.blocked-dates.store'), [
            'unit_id' => $unit->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Aircon maintenance & deep cleaning',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('blocked_dates', [
            'unit_id' => $unit->id,
            'reason' => 'Aircon maintenance & deep cleaning',
        ]);
        $block = BlockedDate::where('unit_id', $unit->id)->first();
        $this->assertEquals($startDate, $block->start_date->toDateString());
        $this->assertEquals($endDate, $block->end_date->toDateString());
    }

    /**
     * Test guest cannot book dates that are manually blocked by the host.
     */
    public function test_guest_cannot_book_host_blocked_dates(): void
    {
        $unit = Unit::first();
        $startDate = now()->addDays(10)->toDateString();
        $endDate = now()->addDays(12)->toDateString();

        BlockedDate::create([
            'unit_id' => $unit->id,
            'user_id' => $this->host->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Host staycation',
        ]);

        $response = $this->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'guest_phone' => '09171234567',
            'guest_count' => 2,
            'check_in_date' => $startDate,
            'check_out_date' => $endDate,
        ]);

        $response->assertSessionHasErrors(['check_in_date']);
        $this->assertDatabaseMissing('bookings', [
            'unit_id' => $unit->id,
            'guest_email' => 'jane@example.com',
        ]);
    }

    /**
     * Test host can unblock a date range.
     */
    public function test_host_can_unblock_dates(): void
    {
        $unit = Unit::first();
        $block = BlockedDate::create([
            'unit_id' => $unit->id,
            'user_id' => $this->host->id,
            'start_date' => now()->addDays(20)->toDateString(),
            'end_date' => now()->addDays(22)->toDateString(),
            'reason' => 'Temporary Block',
        ]);

        $response = $this->actingAs($this->host)->delete(route('host.blocked-dates.destroy', $block));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('blocked_dates', [
            'id' => $block->id,
        ]);
    }

    /**
     * Test host can block dates across all units at once.
     */
    public function test_host_can_block_dates_across_all_units(): void
    {
        $startDate = now()->addDays(30)->toDateString();
        $endDate = now()->addDays(32)->toDateString();
        $totalUnits = Unit::count();

        $response = $this->actingAs($this->host)->post(route('host.blocked-dates.store'), [
            'unit_id' => 'all',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Annual Building Electrical Shutdown',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(
            $totalUnits,
            BlockedDate::where('reason', 'Annual Building Electrical Shutdown')->count()
        );
    }

    /**
     * Test host dashboard view renders the interactive calendar and date blocker elements.
     */
    public function test_host_dashboard_renders_interactive_calendar(): void
    {
        $response = $this->actingAs($this->host)->get(route('host.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Interactive Calendar');
        $response->assertSee('Click dates to block');
        $response->assertSee('Confirm &amp; Block Dates', false);
    }

    public function test_host_dashboard_calculates_host_earnings_active_deposits_and_qrph_badge_accurately(): void
    {
        $baselineResponse = $this->actingAs($this->host)->get(route('host.dashboard'));
        $baselineResponse->assertOk();
        $baselineEarnings = (float) $baselineResponse->viewData('totalHostEarnings');
        $baselineFees = (float) $baselineResponse->viewData('totalPlatformFees');
        $baselineDeposits = (float) $baselineResponse->viewData('totalDepositsHeld');

        $unit = Unit::firstOrFail();

        $activeBooking = Booking::create([
            'booking_code' => 'DS-DASH-ACT-01',
            'unit_id' => $unit->id,
            'guest_name' => 'Active Guest',
            'guest_email' => 'active@example.com',
            'guest_phone' => '09170000001',
            'guest_count' => 1,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'nights_count' => 1,
            'base_amount' => 1500.00,
            'add_ons_amount' => 100.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 75.00,
            'total_amount' => 2675.00,
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'payment_method' => 'paymongo_qrph',
            'payment_scope' => 'full',
        ]);

        Transaction::create([
            'booking_id' => $activeBooking->id,
            'type' => 'advance_deposit',
            'amount' => 1000.00,
            'status' => 'completed',
        ]);
        Transaction::create([
            'booking_id' => $activeBooking->id,
            'type' => 'platform_fee',
            'amount' => 75.00,
            'status' => 'completed',
        ]);

        $checkedOutBooking = Booking::create([
            'booking_code' => 'DS-DASH-OUT-01',
            'unit_id' => $unit->id,
            'guest_name' => 'Checked Out Guest',
            'guest_email' => 'out@example.com',
            'guest_phone' => '09170000002',
            'guest_count' => 1,
            'check_in_date' => now()->subDays(2)->toDateString(),
            'check_out_date' => now()->subDay()->toDateString(),
            'nights_count' => 1,
            'base_amount' => 2000.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 100.00,
            'total_amount' => 3100.00,
            'status' => 'checked_out',
            'payment_status' => 'verified',
            'payment_method' => 'paymongo_gcash',
            'payment_scope' => 'full',
        ]);

        Transaction::create([
            'booking_id' => $checkedOutBooking->id,
            'type' => 'advance_deposit',
            'amount' => 1000.00,
            'status' => 'completed',
        ]);
        Transaction::create([
            'booking_id' => $checkedOutBooking->id,
            'type' => 'platform_fee',
            'amount' => 100.00,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->host)->get(route('host.dashboard'));

        $response->assertOk();
        $this->assertEquals($baselineEarnings + 3600.0, (float) $response->viewData('totalHostEarnings'));
        $this->assertEquals($baselineFees + 175.0, (float) $response->viewData('totalPlatformFees'));
        $this->assertEquals($baselineDeposits + 1000.0, (float) $response->viewData('totalDepositsHeld'));
        $response->assertSee(number_format($baselineEarnings + 3600.0, 2));
        $response->assertSee('QR Ph Paid');
        $response->assertSee('View Inspection');
    }

    public function test_host_can_register_a_new_ortigas_pasig_building_when_creating_a_unit(): void
    {
        $response = $this->actingAs($this->host)->post(route('host.units.store'), [
            'building_id' => 'new',
            'new_building_name' => 'Sorrento Oasis - Building F',
            'new_building_code' => 'sorr-f',
            'new_building_address' => 'C. Raymundo Ave, Brgy. Rosario, Pasig City',
            'new_building_admin_email' => 'pmo.sorrento@example.com',
            'unit_number' => 'Unit F408',
            'title' => 'Sorrento Oasis 2BR Garden Suite',
            'base_price_per_night' => 1900.00,
            'advance_deposit_required' => 1000.00,
            'max_guests' => 4,
            'description' => 'Resort-style staycation in Ortigas, Pasig.',
            'inventory_items' => 'Aircon, WiFi, Smart TV',
        ]);

        $response->assertRedirect(route('host.units.index'));

        $this->assertDatabaseHas('buildings', [
            'name' => 'Sorrento Oasis - Building F',
            'code' => 'SORR-F',
            'address' => 'C. Raymundo Ave, Brgy. Rosario, Pasig City',
            'admin_email' => 'pmo.sorrento@example.com',
            'gate_pass_template' => 'pdf.gate_pass_generic',
        ]);

        $building = Building::where('code', 'SORR-F')->firstOrFail();

        $this->assertDatabaseHas('units', [
            'building_id' => $building->id,
            'unit_number' => 'Unit F408',
            'title' => 'Sorrento Oasis 2BR Garden Suite',
        ]);

        $this->get(route('units.index'))
            ->assertOk()
            ->assertSee('Sorrento Oasis - Building F')
            ->assertSee('Sorrento Oasis 2BR Garden Suite');
    }

    public function test_creating_unit_with_new_building_requires_building_details_and_unique_code(): void
    {
        $this->actingAs($this->host)->post(route('host.units.store'), [
            'building_id' => 'new',
            'unit_number' => 'Unit 101',
            'title' => 'Incomplete Condo Unit',
            'base_price_per_night' => 1500,
            'advance_deposit_required' => 1000,
            'max_guests' => 2,
        ])->assertSessionHasErrors(['new_building_name', 'new_building_code', 'new_building_address']);

        $this->actingAs($this->host)->post(route('host.units.store'), [
            'building_id' => 'new',
            'new_building_name' => 'Duplicate Code Tower',
            'new_building_code' => 'udh-n',
            'new_building_address' => 'Ortigas Ave, Pasig City',
            'unit_number' => 'Unit 102',
            'title' => 'Duplicate Building Code Unit',
            'base_price_per_night' => 1500,
            'advance_deposit_required' => 1000,
            'max_guests' => 2,
        ])->assertSessionHasErrors(['new_building_code']);
    }
}
