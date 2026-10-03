<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\Building;
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
}
