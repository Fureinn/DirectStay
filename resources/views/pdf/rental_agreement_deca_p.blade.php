<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Building P Rental Agreement - {{ $booking->booking_code }}</title>
    <style>
        @page {
            margin: 43.2pt;
        }
        body {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            color: #000000;
            font-size: 10pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .doc-title-box {
            text-align: center;
        }
        .doc-main-title {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .p-clause {
            margin: 0 0 6px 0;
            text-align: justify;
        }
        .bold {
            font-weight: bold;
        }
        .underlined {
            text-decoration: underline;
        }
        .indent-p {
            margin: 0 0 5px 24px;
        }
        .peso {
            font-family: 'DejaVu Sans', sans-serif;
            font-weight: bold;
        }
        .table-checklist {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .table-checklist td {
            width: 33.33%;
            border: 1px solid #000000;
            padding: 3px 5px;
            font-size: 8pt;
            vertical-align: middle;
        }
        .table-guests {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 12px;
        }
        .table-guests th, .table-guests td {
            border: 1px solid #000000;
            padding: 4px 6px;
            font-size: 9pt;
        }
        .table-guests th {
            text-align: left;
            font-weight: normal;
        }
        .sig-block {
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 11pt;
            line-height: 1.4;
        }
        .sig-row {
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

    <!-- Header with Authentic Miranda Logo & Title -->
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: left;">
                @if(!empty($mirandaLogoBase64))
                    <img src="{{ $mirandaLogoBase64 }}" style="height: 62px; width: auto;" alt="Miranda PadUno / PadDos">
                @endif
            </td>
            <td style="width: 75%;" class="doc-title-box">
                <div class="doc-main-title">STAYCATION</div>
                <div class="doc-main-title">RENTAL AGREEMENT</div>
            </td>
        </tr>
    </table>

    <!-- Parties Statement (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">I. THE PARTIES</span>. This AF Staycation Rental Agreement (“Agreement") made on <span class="underlined">&nbsp;{{ $booking->created_at->format('F d') }}&nbsp;</span>, 20<span class="underlined">{{ $booking->created_at->format('y') }}</span> between the following:
    </p>
    <p class="indent-p">
        <span class="bold">TENANT:</span> <span class="underlined">&nbsp;{{ $booking->guest_name }}&nbsp;</span>, with a mailing address of <span class="underlined">&nbsp;{{ $booking->guest_email }} &bull; {{ $booking->guest_phone }}&nbsp;</span> (“Tenant”), and
    </p>
    <p class="indent-p">
        <span class="bold">OWNER:</span> <span class="underlined">&nbsp;Ferlyn Miranda&nbsp;</span>, with a mailing address of <span class="underlined">&nbsp;Building P, Unit 718, Urban Decahomes Ortigas&nbsp;</span> (“Landlord”).
    </p>

    <!-- Premises Statement (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">II. THE PREMISES</span>. The Landlord agrees to lease the described property below to the Tenant, and the Tenant agrees to rent from the Landlord:
    </p>
    <p class="indent-p">
        a.) Residence Type: <span style="font-family: 'DejaVu Sans'; font-size: 9.5pt;">&#9745;</span> Condo<br>
        b.) Bedroom(s): <span class="underlined">&nbsp;2&nbsp;</span><br>
        c.) Bathroom(s): <span class="underlined">&nbsp;1.&nbsp;</span>
    </p>
    <p class="p-clause">Hereinafter known as the “Premises.”</p>

    <!-- Lease Term Statement (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">III. LEASE TERM</span>. The Tenant shall have access to the Premises under the terms of this Agreement for the following time period:<br>
        <span class="bold" style="margin-left: 20px;">Daily, Weekly and Month to Month Terms.</span><br>
        <span style="margin-left: 20px;"><span class="bold">Date of Check in/Time:</span> <span class="underlined">&nbsp;{{ $booking->check_in_date->format('F d, Y') }} (2:00 PM onwards)&nbsp;</span></span><br>
        <span style="margin-left: 20px;"><span class="bold">Date of Check out/Time:</span> <span class="underlined">&nbsp;{{ $booking->check_out_date->format('F d, Y') }} (12:00 NN)&nbsp;</span></span>
    </p>

    <!-- Occupants Statement (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">IV. OCCUPANTS</span>. The total number of individuals staying on the Premises during the Lease Term shall be a total of <span class="underlined">&nbsp;{{ $booking->guest_count }}&nbsp;</span> Guests/Visitors. If more than the authorized number of guests listed above are found on the Premises, this Agreement will be subject to termination by the Landlord.
    </p>

    <!-- Rent Statement (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">V. RENT</span>. The Tenant shall pay the Landlord, <span class="peso">&#8369;</span><span class="bold">1000.00 Advance Deposit</span> remaining balance upon check in non refundable or cancellation and no rescheduling
    </p>

    <!-- Security Deposit Statement (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">VI. SECURITY DEPOSIT</span>. The Tenant shall be obligated to pay the following amounts upon the execution of this Agreement:<br>
        <span class="bold">Security Deposit:</span> The Security Deposit is for the faithful performance of the Tenant under the terms and conditions of this Agreement. The Tenant must pay the Security Deposit at the execution of this Agreement. The Security Deposit shall be returned to the Tenant within the State's requirements after the end of the Lease Term less any itemized deductions
    </p>

    <!-- Smoking & Pets Policy (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">VII. SMOKING POLICY</span>. Smoking is <span class="bold">Prohibited</span>
    </p>
    <p class="p-clause">
        <span class="bold">VIII.</span> Pets are not allowed
    </p>

    <!-- Inspection Clause (Verbatim Docx) -->
    <p class="p-clause">
        <span class="bold">IX. INSPECTION</span>. The Landlord has the right to inspect the Premises with prior notice as in accordance with Agreement. Should the Tenant violate any of the terms of this Agreement, the rental period shall be terminated immediately in accordance with State law. The tenant waives all rights to process if they fail to vacate the premises upon termination of the rental period. The Tenant shall vacate the Premises at the expiration time and date of this agreement.
    </p>

    <p class="p-clause">
        <span class="bold">X.</span> Agree and check that all.equipment is in good conditions and House rules
    </p>

    <!-- Page Break to Page 2 (Checklist, Roster, Signatures) -->
    <div style="page-break-before: always;"></div>

    <div style="font-size: 10pt; font-weight: normal; margin-top: 8px; margin-bottom: 4px;">Checklist:</div>

    <!-- Authentic 12-Row 3-Column Checklist for Building P -->
    <table class="table-checklist">
        <tr>
            <td>2 Rice Cooker</td>
            <td>Inverter Air Conditioner Kolin</td>
            <td>2 Seater Sofa Bed</td>
        </tr>
        <tr>
            <td>40 Inch Android Television</td>
            <td>Japanese Doll</td>
            <td>Side Table</td>
        </tr>
        <tr>
            <td>LG Inverter Refrigerator</td>
            <td>Wine Rack</td>
            <td>2 Bar Stools</td>
        </tr>
        <tr>
            <td>2 Starbucks cups display</td>
            <td>Sharp Speaker</td>
            <td>Electric Kettle</td>
        </tr>
        <tr>
            <td>Air Pryer</td>
            <td>Kitchen Range Hood</td>
            <td>Wall Clock</td>
        </tr>
        <tr>
            <td>Induction Cooker</td>
            <td>3 Wines Display</td>
            <td>Shoe Rack</td>
        </tr>
        <tr>
            <td>Microwave</td>
            <td>Dining Set Table and 4 ChairExtension Cord</td>
            <td>KitchenWareCooking PotFrying Pan</td>
        </tr>
        <tr>
            <td>2 Wall Fan/ Asahi Stand Fan</td>
            <td>Scrabble</td>
            <td>Queen Size Mattress Bed / Loft Bed Double and Double Bed Extra</td>
        </tr>
        <tr>
            <td>6 Glass of Water Goblet</td>
            <td>Ladder</td>
            <td>French Mirror</td>
        </tr>
        <tr>
            <td>2 Containers of 5 Gallons Mineral</td>
            <td>4 Starbucks Tumbler Cups</td>
            <td>22 pcs Disney Magnet Display</td>
        </tr>
        <tr>
            <td>Non Inverter Aircon Kolin</td>
            <td>Extra Induction La Germania</td>
            <td>2 Ships Display</td>
        </tr>
        <tr>
            <td>Microphones</td>
            <td>Remote controls</td>
            <td>Drying rack</td>
        </tr>
    </table>

    <p class="p-clause" style="margin-top: 8px;">
        <span class="bold">XI. Notes : Clean as you go</span>
    </p>
    <p class="p-clause">
        <span class="bold">XII.</span> ID’s of all Guest
    </p>

    <!-- Identification Roster Table -->
    <table class="table-guests">
        <thead>
            <tr>
                <th style="width: 40%;">Guest and Visitors Name</th>
                <th style="width: 30%;">Valid Id / Vaccine</th>
                <th style="width: 30%;">Signature</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ $booking->guest_name }}</strong></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @if(!empty($booking->guest_roster) && is_array($booking->guest_roster))
                @foreach($booking->guest_roster as $companion)
                    @if(!empty($companion['name']))
                        <tr>
                            <td>{{ $companion['name'] }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endif
                @endforeach
            @endif
            @for($i = (empty($booking->guest_roster) ? 1 : count($booking->guest_roster) + 1); $i < 5; $i++)
                <tr>
                    <td style="height: 18px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Verbatim Signatures Block -->
    <div class="sig-block">
        <div class="sig-row">
            <span class="bold">Landlord’s Signature:</span> <span class="underlined">&nbsp;&nbsp;&nbsp;&nbsp;Ferlyn Miranda&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="bold">Date:</span> <span class="underlined">&nbsp;&nbsp;{{ $booking->created_at->format('F d, Y') }}&nbsp;&nbsp;</span><br>
            <span>Print Name:</span> <span class="underlined">&nbsp;&nbsp;&nbsp;&nbsp;Ferlyn Miranda&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </div>
        <br>
        <div class="sig-row">
            <span class="bold">Tenant’s Signature:</span> <span class="underlined">&nbsp;&nbsp;&nbsp;&nbsp;{{ $booking->guest_name }}&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="bold">Date:</span> <span class="underlined">&nbsp;&nbsp;{{ $booking->waiver_accepted_at ? $booking->waiver_accepted_at->format('F d, Y') : $booking->created_at->format('F d, Y') }}&nbsp;&nbsp;</span><br>
            <span>Print Name:</span> <span class="underlined">&nbsp;&nbsp;&nbsp;&nbsp;{{ $booking->guest_name }}&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </div>
        <br>
        <div class="sig-row">
            <span class="bold">Tenant’s Signature:</span> <span class="underlined">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="bold">Date:</span> <span class="underlined">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br>
            <span>Print Name:</span> <span class="underlined">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </div>
    </div>

</body>
</html>
