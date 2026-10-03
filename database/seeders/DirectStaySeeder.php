<?php

namespace Database\Seeders;

use App\Models\AddOn;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Review;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DirectStaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Host Lessor User (Ferlyn Miranda & Aurelio J. Budol Sr.)
        $host = User::updateOrCreate(
            ['email' => 'host@directstay.local'],
            [
                'name' => 'Ferlyn Miranda & Aurelio J. Budol Sr. (A-F Staycation)',
                'phone' => '09478847463',
                'password' => Hash::make('password'),
                'role' => 'host',
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo Customer / Guest User
        User::updateOrCreate(
            ['email' => 'customer@directstay.local'],
            [
                'name' => 'Kenry M. Cui',
                'phone' => '09178889911',
                'password' => Hash::make('password'),
                'role' => 'guest',
                'email_verified_at' => now(),
            ]
        );

        // Common House Rules text from Deca Guest Form & Reminders
        $buildingNRules = "1. Valid Government ID is strictly required for building gate entry and elevator pass release.\n"
            ."2. Check-in: 2:00 PM onwards | Check-out: 12:00 NN.\n"
            ."3. Urban Deca Homes Ortigas is a strictly NO SMOKING complex; smoking or vaping is strictly prohibited in all units and common areas (Violation fine: ₱2,000). Smoking area is located at the back of Building N.\n"
            ."4. Garbage disposal: Dispose of all trash properly at the collection area at the back of Building N near the Smoking Area. Unthrown garbage fine is ₱500.\n"
            ."5. Replacement fee for lost physical key or elevator RFID pass is ₱500 each.\n"
            ."6. Building-wide quiet hours: Sunday to Thursday: 10:00 PM to 8:00 AM | Friday to Saturday: 12:00 AM to 9:00 AM.\n"
            ."7. No pets of any kind (except tropical fishes) are allowed within the property.\n"
            ."8. Noise control: Avoid loud music or use of amplified audio equipment.\n"
            ."9. No cooking of foul-smelling food (e.g., tuyo, tinapa, binagoongan). Turn on range hood while cooking.\n"
            ."10. Turn off / unplug lights, air conditioning, water heater, and electronics when leaving or checking out.\n"
            ."11. Food delivery & rider meetup point: Delivery Area between Buildings M & N parking garages.\n"
            .'12. Clean As You Go (CLAYGO) to keep the unit tidy and prevent pests.';

        $buildingPRules = "1. Valid Government ID is strictly required for building gate entry and elevator pass release.\n"
            ."2. Check-in: 2:00 PM onwards | Check-out: 12:00 NN.\n"
            ."3. Urban Deca Homes Ortigas is a strictly NO SMOKING complex; smoking or vaping is strictly prohibited in all units and common areas (Violation fine: ₱2,000). Smoking area is located at the back of Building P.\n"
            ."4. Garbage disposal: Dispose of all trash properly at the collection area at the back of Building P near the Smoking Area. Unthrown garbage fine is ₱500.\n"
            ."5. Replacement fee for lost physical key or elevator RFID pass is ₱500 each.\n"
            ."6. Building-wide quiet hours: Sunday to Thursday: 10:00 PM to 8:00 AM | Friday to Saturday: 12:00 AM to 9:00 AM.\n"
            ."7. No pets of any kind (except tropical fishes) are allowed within the property.\n"
            ."8. Noise control: Avoid loud music or use of amplified audio equipment.\n"
            ."9. No cooking of foul-smelling food (e.g., tuyo, tinapa, binagoongan). Turn on range hood while cooking.\n"
            ."10. Turn off / unplug lights, air conditioning, water heater, and electronics when leaving or checking out.\n"
            ."11. Food delivery & rider meetup point: Delivery Area between Buildings M & N parking garages.\n"
            .'12. Clean As You Go (CLAYGO) to keep the unit tidy and prevent pests.';

        // 3. Urban Deca Homes Ortigas - Building N
        $buildingN = Building::updateOrCreate(
            ['code' => 'UDH-N'],
            [
                'name' => 'Urban Deca Homes Ortigas - Building N',
                'address' => 'KM 19 Ortigas Avenue Extension, Brgy. Rosario, Pasig City, Metro Manila',
                'admin_email' => 'security.bldg.n@udhortigas.local',
                'gate_pass_template' => 'pdf.gate_pass_deca_n',
                'house_rules' => $buildingNRules,
            ]
        );

        // 4. Urban Deca Homes Ortigas - Building P
        $buildingP = Building::updateOrCreate(
            ['code' => 'UDH-P'],
            [
                'name' => 'Urban Deca Homes Ortigas - Building P',
                'address' => 'KM 19 Ortigas Avenue Extension, Brgy. Rosario, Pasig City, Metro Manila',
                'admin_email' => 'security.bldg.p@udhortigas.local',
                'gate_pass_template' => 'pdf.gate_pass_deca_p',
                'house_rules' => $buildingPRules,
            ]
        );

        // 5. Units with Real High-Res Photos matching Rental Agreements (Pad Dos & Pad Uno)
        Unit::updateOrCreate(
            ['unit_number' => 'Unit N343', 'building_id' => $buildingN->id],
            [
                'user_id' => $host->id,
                'title' => 'A-F Staycation Pad Dos (2BR) - Bldg N',
                'description' => 'Comfortable 2-bedroom unit in Urban Deca Homes Ortigas (Bldg N, Unit 343). Features 2 Hitachi inverter aircons, 32-inch Android TV, Whirlpool washing machine, American Home microwave, Blakk induction cooker, sofa bed, queen bed with loft bed, and high-speed WiFi (AFSaltLifePadDos).',
                'cover_image' => 'images/units/unit_n412.jpg',
                'images' => [
                    'images/units/unit_n412.jpg',
                    'images/units/unit_p718.jpg',
                ],
                'base_price_per_night' => 1500.00,
                'advance_deposit_required' => 1000.00,
                'max_guests' => 6,
                'inventory_items' => [
                    'Hitachi Inverter Air Conditioner (2 units)',
                    '32-inch Android Television',
                    'LG Refrigerator',
                    'American Home Microwave',
                    'Blakk Induction Cooker',
                    'Whirlpool Washing Machine',
                    'Kitchen Range Hood',
                    'Rice Cooker & Electric Kettle',
                    '2-Seater Sofa Bed',
                    'Dining Set Table & 4 Chairs',
                    'Queen Size Bed & Loft Bed Double',
                    'Tri-fold Floor Mattress & 6 Pillows (4 Extra)',
                    'Asahi Wall Fans (2 units) & 3D Turbo Fan',
                    'Acer Pure Electric Fan',
                    'Clothes Iron & Ironing Board',
                    'Wine Rack with 4 Wines Display',
                    '14 pcs Glass Water Goblets',
                    '2 Containers of 5 Gallons Mineral Water',
                    'Ladder Clothes Rack & Folding Mirror',
                    'JBL Speaker / OKKO Speaker',
                    'Physical Key & RFID Elevator Pass',
                ],
                'is_active' => true,
            ]
        );

        Unit::updateOrCreate(
            ['unit_number' => 'Unit P718', 'building_id' => $buildingP->id],
            [
                'user_id' => $host->id,
                'title' => 'A-F Staycation Pad Uno (2BR) - Bldg P',
                'description' => 'Bright high-floor 2-bedroom suite in Urban Deca Homes Ortigas (Bldg P, Unit 718). Features Kolin inverter aircon, 40-inch Android TV, air fryer, microwave, induction cooker, dining set, sofa bed, queen bed with extra double bed, and high-speed WiFi (AFSaltLifePadUno2g).',
                'cover_image' => 'images/units/unit_p718.jpg',
                'images' => [
                    'images/units/unit_p718.jpg',
                    'images/units/unit_n412.jpg',
                ],
                'base_price_per_night' => 1650.00,
                'advance_deposit_required' => 1000.00,
                'max_guests' => 6,
                'inventory_items' => [
                    'Kolin Inverter Air Conditioner & Non-Inverter Aircon',
                    '40-inch Android Television',
                    'LG Inverter Refrigerator',
                    'Microwave Oven',
                    'Induction Cooker & Extra La Germania Induction',
                    'Air Fryer',
                    'Kitchen Range Hood',
                    'Rice Cooker & Electric Kettle',
                    '2-Seater Sofa Bed',
                    'Dining Set Table & 4 Chairs',
                    'Queen Size Bed & Loft Bed Double',
                    'Wall Fans (2 units) & Asahi Stand Fan',
                    'Wine Rack with 3 Wines Display',
                    '6 pcs Glass Water Goblets',
                    '2 Containers of 5 Gallons Mineral Water',
                    'Ladder French Mirror',
                    '22 pcs Disney Magnet Display & Japanese Doll',
                    'Scrabble Board Game',
                    'Sharp Speaker & Microphones',
                    'Drying Rack & Shoe Rack',
                    'Physical Key & RFID Elevator Pass',
                ],
                'is_active' => true,
            ]
        );

        // 6. Add-ons matching Deca Homes Reminders & Services
        $addOns = [
            ['name' => 'Extra Pillow', 'price' => 25.00],
            ['name' => 'Pillow Case Set', 'price' => 50.00],
            ['name' => 'Extra Duvet', 'price' => 30.00],
            ['name' => 'Bath Towel', 'price' => 30.00],
            ['name' => 'Bed Linen', 'price' => 50.00],
            ['name' => 'Extra Floor Mattress', 'price' => 200.00],
            ['name' => 'On-Call Basic Cleaning (with Transpo)', 'price' => 500.00],
            ['name' => 'On-Call Deep Cleaning (with Transpo)', 'price' => 1300.00],
        ];

        foreach ($addOns as $addOnData) {
            AddOn::updateOrCreate(
                ['name' => $addOnData['name']],
                [
                    'unit_id' => null,
                    'price' => $addOnData['price'],
                    'is_active' => true,
                ]
            );
        }

        // 7. Seed completed booking reservations and authentic guest reviews
        $unitN = Unit::where('unit_number', 'Unit N343')->first();
        $unitP = Unit::where('unit_number', 'Unit P718')->first();

        if ($unitN) {
            $booking1 = Booking::updateOrCreate(
                ['booking_code' => 'DS-SEED-REV-01'],
                [
                    'unit_id' => $unitN->id,
                    'user_id' => null,
                    'guest_name' => 'Maria Cristina Reyes',
                    'guest_email' => 'maria.reyes@example.com',
                    'guest_phone' => '09171239988',
                    'guest_count' => 4,
                    'check_in_date' => Carbon::now()->subDays(10)->toDateString(),
                    'check_out_date' => Carbon::now()->subDays(8)->toDateString(),
                    'nights_count' => 2,
                    'base_amount' => 3000.00,
                    'add_ons_amount' => 50.00,
                    'advance_deposit_amount' => 1000.00,
                    'platform_fee' => 150.00,
                    'total_amount' => 4200.00,
                    'status' => 'checked_out',
                    'payment_status' => 'verified',
                    'waiver_accepted_at' => Carbon::now()->subDays(10),
                    'verified_at' => Carbon::now()->subDays(10),
                    'created_at' => Carbon::now()->subDays(12),
                    'updated_at' => Carbon::now()->subDays(8),
                ]
            );

            Review::updateOrCreate(
                ['booking_id' => $booking1->id],
                [
                    'unit_id' => $unitN->id,
                    'user_id' => null,
                    'guest_name' => 'Maria Cristina Reyes',
                    'rating' => 5,
                    'cleanliness_rating' => 5,
                    'communication_rating' => 5,
                    'accuracy_rating' => 5,
                    'value_rating' => 5,
                    'comment' => 'Sobrang ganda and linis ng unit! Very cold aircon and the WiFi was super fast for work-from-home. Host Sir Aurelio and Ma\'am Ferlyn were very accommodating and made our Deca gate pass clearance hassle-free. Will definitely book again!',
                    'created_at' => Carbon::now()->subDays(8),
                    'updated_at' => Carbon::now()->subDays(8),
                ]
            );

            $booking2 = Booking::updateOrCreate(
                ['booking_code' => 'DS-SEED-REV-02'],
                [
                    'unit_id' => $unitN->id,
                    'user_id' => null,
                    'guest_name' => 'Joshua Villanueva',
                    'guest_email' => 'joshua.v@example.com',
                    'guest_phone' => '09187766554',
                    'guest_count' => 5,
                    'check_in_date' => Carbon::now()->subDays(6)->toDateString(),
                    'check_out_date' => Carbon::now()->subDays(5)->toDateString(),
                    'nights_count' => 1,
                    'base_amount' => 1500.00,
                    'add_ons_amount' => 0.00,
                    'advance_deposit_amount' => 1000.00,
                    'platform_fee' => 75.00,
                    'total_amount' => 2575.00,
                    'status' => 'checked_out',
                    'payment_status' => 'verified',
                    'waiver_accepted_at' => Carbon::now()->subDays(6),
                    'verified_at' => Carbon::now()->subDays(6),
                    'created_at' => Carbon::now()->subDays(7),
                    'updated_at' => Carbon::now()->subDays(5),
                ]
            );

            Review::updateOrCreate(
                ['booking_id' => $booking2->id],
                [
                    'unit_id' => $unitN->id,
                    'user_id' => null,
                    'guest_name' => 'Joshua Villanueva',
                    'rating' => 5,
                    'cleanliness_rating' => 5,
                    'communication_rating' => 5,
                    'accuracy_rating' => 5,
                    'value_rating' => 5,
                    'comment' => 'Great staycation for our barkada! The 2-bedroom setup with the loft bed fit us comfortably. We saved a lot booking directly through DirectStay without the expensive Airbnb fees. Clean towels and complete kitchen cookware. 10/10!',
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(5),
                ]
            );
        }

        if ($unitP) {
            $booking3 = Booking::updateOrCreate(
                ['booking_code' => 'DS-SEED-REV-03'],
                [
                    'unit_id' => $unitP->id,
                    'user_id' => null,
                    'guest_name' => 'Angelica Santos',
                    'guest_email' => 'angelica.s@example.com',
                    'guest_phone' => '09228833441',
                    'guest_count' => 2,
                    'check_in_date' => Carbon::now()->subDays(4)->toDateString(),
                    'check_out_date' => Carbon::now()->subDays(2)->toDateString(),
                    'nights_count' => 2,
                    'base_amount' => 3300.00,
                    'add_ons_amount' => 0.00,
                    'advance_deposit_amount' => 1000.00,
                    'platform_fee' => 165.00,
                    'total_amount' => 4465.00,
                    'status' => 'checked_out',
                    'payment_status' => 'verified',
                    'waiver_accepted_at' => Carbon::now()->subDays(4),
                    'verified_at' => Carbon::now()->subDays(4),
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(2),
                ]
            );

            Review::updateOrCreate(
                ['booking_id' => $booking3->id],
                [
                    'unit_id' => $unitP->id,
                    'user_id' => null,
                    'guest_name' => 'Angelica Santos',
                    'rating' => 5,
                    'cleanliness_rating' => 5,
                    'communication_rating' => 5,
                    'accuracy_rating' => 5,
                    'value_rating' => 5,
                    'comment' => 'Unit P718 was very cozy and peaceful on a high floor! Super easy elevator RFID access and the Netflix TV was great for movie night. Deca pool was refreshing and check-in was seamless. Highly recommended!',
                    'created_at' => Carbon::now()->subDays(2),
                    'updated_at' => Carbon::now()->subDays(2),
                ]
            );

            $booking4 = Booking::updateOrCreate(
                ['booking_code' => 'DS-SEED-REV-04'],
                [
                    'unit_id' => $unitP->id,
                    'user_id' => null,
                    'guest_name' => 'Mark Anthony Delos Santos',
                    'guest_email' => 'mark.delossantos@example.com',
                    'guest_phone' => '09339944112',
                    'guest_count' => 3,
                    'check_in_date' => Carbon::now()->subDays(14)->toDateString(),
                    'check_out_date' => Carbon::now()->subDays(12)->toDateString(),
                    'nights_count' => 2,
                    'base_amount' => 3300.00,
                    'add_ons_amount' => 0.00,
                    'advance_deposit_amount' => 1000.00,
                    'platform_fee' => 165.00,
                    'total_amount' => 4465.00,
                    'status' => 'checked_out',
                    'payment_status' => 'verified',
                    'waiver_accepted_at' => Carbon::now()->subDays(14),
                    'verified_at' => Carbon::now()->subDays(14),
                    'created_at' => Carbon::now()->subDays(14),
                    'updated_at' => Carbon::now()->subDays(12),
                ]
            );

            Review::updateOrCreate(
                ['booking_id' => $booking4->id],
                [
                    'unit_id' => $unitP->id,
                    'user_id' => null,
                    'guest_name' => 'Mark Anthony Delos Santos',
                    'rating' => 5,
                    'cleanliness_rating' => 5,
                    'communication_rating' => 5,
                    'accuracy_rating' => 5,
                    'value_rating' => 5,
                    'comment' => 'Very spacious unit and seamless direct check-in. The security gate pass was already confirmed at the lobby desk when we arrived. Host responds in minutes on GCash confirmation. Thank you DirectStay!',
                    'created_at' => Carbon::now()->subDays(12),
                    'updated_at' => Carbon::now()->subDays(12),
                ]
            );
        }
    }
}
