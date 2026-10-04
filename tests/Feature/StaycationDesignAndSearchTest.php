<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Building;
use App\Models\Unit;
use Carbon\Carbon;
use Database\Seeders\DirectStaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaycationDesignAndSearchTest extends TestCase
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
     * Test catalog homepage loads with modern hero elements and active unit listings.
     */
    public function test_catalog_homepage_renders_successfully_with_hero_search_and_units(): void
    {
        $response = $this->get(route('units.index'));

        $response->assertOk();
        $response->assertSee('DirectStay');
        $response->assertSee('Staycation');
        $response->assertSee('Urban Deca Homes Ortigas');
        $response->assertSee('Building N &amp; P Gate Pass Certified', false);
        $response->assertSee('All Towers (Deca Ortigas)');

        $units = Unit::where('is_active', true)->get();
        foreach ($units->take(3) as $unit) {
            $response->assertSee($unit->title);
            $response->assertSee(number_format($unit->base_price_per_night, 0));
        }
    }

    /**
     * Test catalog search correctly filters units by building code.
     */
    public function test_catalog_search_filters_by_building(): void
    {
        $buildingA = Building::firstOrFail();
        $buildingB = Building::where('id', '!=', $buildingA->id)->first();

        $response = $this->get(route('units.index', ['building' => $buildingA->code]));
        $response->assertOk();

        // Building A units should be visible
        $unitsInA = Unit::where('building_id', $buildingA->id)->where('is_active', true)->get();
        foreach ($unitsInA as $unit) {
            $response->assertSee($unit->unit_number);
        }

        // If Building B exists, its units should not be returned in the filtered list
        if ($buildingB) {
            $unitsInB = Unit::where('building_id', $buildingB->id)->where('is_active', true)->get();
            foreach ($unitsInB as $unit) {
                $response->assertDontSee($unit->title);
            }
        }
    }

    /**
     * Test catalog search filters out units that cannot accommodate the requested guest count.
     */
    public function test_catalog_search_filters_by_guest_capacity(): void
    {
        $unitLow = Unit::firstOrFail();
        $unitLow->update(['max_guests' => 2, 'title' => 'Cozy Studio 2-Guest Unit']);

        $unitHigh = Unit::where('id', '!=', $unitLow->id)->firstOrFail();
        $unitHigh->update(['max_guests' => 6, 'title' => 'Spacious Family Loft 6-Guest Unit']);

        $response = $this->get(route('units.index', ['guests' => 4]));
        $response->assertOk();

        $response->assertSee('Spacious Family Loft 6-Guest Unit');
        $response->assertDontSee('Cozy Studio 2-Guest Unit');
    }

    /**
     * Test catalog search filters out units with existing overlapping bookings or blocked dates.
     */
    public function test_catalog_search_filters_out_booked_units_for_given_dates(): void
    {
        $unitBooked = Unit::firstOrFail();
        $unitAvailable = Unit::where('id', '!=', $unitBooked->id)->firstOrFail();

        $checkIn = Carbon::tomorrow()->toDateString();
        $checkOut = Carbon::tomorrow()->addDays(3)->toDateString();

        // Create confirmed booking on unitBooked overlapping the search window
        Booking::create([
            'booking_code' => 'DS-OVERLAP-QA',
            'unit_id' => $unitBooked->id,
            'guest_name' => 'Booked Person',
            'guest_email' => 'booked@example.com',
            'guest_phone' => '09171112233',
            'guest_count' => 2,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'nights_count' => 3,
            'base_amount' => 3000.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 1000.00,
            'platform_fee' => 150.00,
            'total_amount' => 4150.00,
            'status' => 'confirmed',
            'payment_status' => 'verified',
        ]);

        $response = $this->get(route('units.index', [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]));

        $response->assertOk();
        $response->assertDontSee($unitBooked->title);
        $response->assertSee($unitAvailable->title);
    }

    /**
     * Test unit detail page renders with 5-photo mosaic data, amenities, and gate pass roadmap.
     */
    public function test_unit_detail_page_renders_with_gallery_and_amenities(): void
    {
        $unit = Unit::firstOrFail();

        $response = $this->get(route('units.show', $unit));

        $response->assertOk();
        $response->assertSee($unit->title);
        $response->assertSee(number_format($unit->base_price_per_night, 0));
        $response->assertSee('Show all');
        $response->assertSee('How Your DirectStay Reservation Works');
        $response->assertSee('Deca PMO Gate Pass Ready');
        $response->assertSee('Featured Amenities');
        $response->assertSee('Location &amp; Pasig Neighborhood Guide', false);
    }

    /**
     * Test unit detail page pre-populates dates and guest count from search query string.
     */
    public function test_unit_detail_page_prepopulates_search_dates_and_guest_count(): void
    {
        $unit = Unit::firstOrFail();
        $checkIn = Carbon::tomorrow()->addDays(5)->toDateString();
        $checkOut = Carbon::tomorrow()->addDays(8)->toDateString();
        $guests = min(3, $unit->max_guests);

        $response = $this->get(route('units.show', [
            'unit' => $unit,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
        ]));

        $response->assertOk();
        $response->assertSee('value="'.$checkIn.'"', false);
        $response->assertSee('value="'.$checkOut.'"', false);
        $response->assertSee('value="'.$guests.'" selected', false);
    }

    /**
     * Test Unit model galleryImages() and featuredAmenities() methods.
     */
    public function test_unit_helpers_gallery_images_and_featured_amenities(): void
    {
        $unit = Unit::firstOrFail();

        $gallery = $unit->galleryImages();
        $this->assertIsArray($gallery);
        $this->assertNotEmpty($gallery);

        $amenities = $unit->featuredAmenities();
        $this->assertIsArray($amenities);
        foreach ($amenities as $amenity) {
            $this->assertArrayHasKey('icon', $amenity);
            $this->assertArrayHasKey('label', $amenity);
            $this->assertArrayHasKey('desc', $amenity);
        }
    }

    /**
     * Test complete booking submission from unit detail page.
     */
    public function test_successful_booking_creation_from_unit_page(): void
    {
        $unit = Unit::firstOrFail();
        $checkIn = Carbon::tomorrow()->addDays(10)->toDateString();
        $checkOut = Carbon::tomorrow()->addDays(12)->toDateString();

        $response = $this->post(route('bookings.store'), [
            'unit_id' => $unit->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guest_count' => 2,
            'guest_name' => 'Maria Santos',
            'guest_email' => 'maria.santos@example.ph',
            'guest_phone' => '09171234567',
        ]);

        $booking = Booking::where('guest_email', 'maria.santos@example.ph')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(2, $booking->nights_count);
        $this->assertEquals('pending_verification', $booking->status);

        $response->assertRedirect(route('compliance.portal', $booking->booking_code));
    }
}
