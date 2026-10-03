<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Unit;
use Database\Seeders\DirectStaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DirectStaySeeder::class);
    }

    /**
     * Helper to create test booking.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function createBooking(array $attributes = []): Booking
    {
        $unit = Unit::firstOrFail();

        return Booking::create(array_merge([
            'unit_id' => $unit->id,
            'booking_code' => 'DS-REV-'.uniqid(),
            'guest_name' => 'Maria Santos',
            'guest_email' => 'maria@example.com',
            'guest_phone' => '09171234567',
            'guest_count' => 2,
            'check_in_date' => now()->subDays(3)->toDateString(),
            'check_out_date' => now()->subDays(1)->toDateString(),
            'nights_count' => 2,
            'base_amount' => 3000.00,
            'add_ons_amount' => 0.00,
            'advance_deposit_amount' => 500.00,
            'platform_fee' => 0.00,
            'total_amount' => 3500.00,
            'payment_status' => 'paid',
            'status' => 'checked_out',
        ], $attributes));
    }

    /**
     * Test guest can submit a review for an eligible stay.
     */
    public function test_guest_can_submit_review_for_eligible_stay(): void
    {
        $booking = $this->createBooking();

        $response = $this->post(route('reviews.store', $booking), [
            'rating' => 5,
            'cleanliness_rating' => 5,
            'communication_rating' => 5,
            'accuracy_rating' => 5,
            'value_rating' => 4,
            'comment' => 'Fantastic stay! Clean room and very reliable host.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Thank you! Your staycation rating and review have been published.');

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'unit_id' => $booking->unit_id,
            'guest_name' => 'Maria Santos',
            'rating' => 5,
            'cleanliness_rating' => 5,
            'comment' => 'Fantastic stay! Clean room and very reliable host.',
        ]);
    }

    /**
     * Test rating validation requires integer between 1 and 5.
     */
    public function test_review_validation_enforces_rating_range(): void
    {
        $booking = $this->createBooking();

        // Test rating 0 (too low)
        $response = $this->post(route('reviews.store', $booking), [
            'rating' => 0,
        ]);
        $response->assertSessionHasErrors(['rating']);

        // Test rating 6 (too high)
        $response = $this->post(route('reviews.store', $booking), [
            'rating' => 6,
        ]);
        $response->assertSessionHasErrors(['rating']);
    }

    /**
     * Test guest cannot submit duplicate review for the same booking.
     */
    public function test_guest_cannot_submit_duplicate_review_for_same_booking(): void
    {
        $booking = $this->createBooking();

        Review::create([
            'booking_id' => $booking->id,
            'unit_id' => $booking->unit_id,
            'guest_name' => $booking->guest_name,
            'rating' => 5,
            'comment' => 'First review submission.',
        ]);

        $response = $this->post(route('reviews.store', $booking), [
            'rating' => 4,
            'comment' => 'Second attempt should fail.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You have already submitted a review for this stay reservation.');
        $this->assertEquals(1, Review::where('booking_id', $booking->id)->count());
    }

    /**
     * Test cancelled booking cannot be reviewed.
     */
    public function test_cancelled_booking_cannot_be_reviewed(): void
    {
        $booking = $this->createBooking([
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
        ]);

        $response = $this->post(route('reviews.store', $booking), [
            'rating' => 5,
            'comment' => 'Should not be allowed to rate cancelled stay.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cancelled bookings cannot be reviewed.');
        $this->assertDatabaseMissing('reviews', [
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Test unit average rating and review count helper methods.
     */
    public function test_unit_average_rating_and_count(): void
    {
        $booking = $this->createBooking();
        $unit = $booking->unit;
        $initialCount = $unit->reviewsCount();

        Review::create([
            'booking_id' => $booking->id,
            'unit_id' => $unit->id,
            'guest_name' => 'Score Test Guest',
            'rating' => 5,
            'comment' => 'A wonderful stay!',
        ]);

        $unit->refresh();
        $this->assertEquals($initialCount + 1, $unit->reviewsCount());
        $this->assertGreaterThanOrEqual(1.0, $unit->averageRating());
        $this->assertLessThanOrEqual(5.0, $unit->averageRating());
    }
}
