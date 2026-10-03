<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Building P Guest Form - {{ $booking->booking_code }}</title>
    <style>
        @page {
            margin: 37pt 54pt 84pt;
        }
        body {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            color: #000000;
            font-size: 10pt;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .header-box {
            width: 100%;
            margin-bottom: 2px;
        }
        .header-logo-td {
            text-align: right;
            vertical-align: top;
        }
        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 2px;
            margin-bottom: 8px;
        }
        .authorize-p {
            text-align: justify;
            font-size: 10pt;
            line-height: 1.3;
            margin-top: 0;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .underlined {
            text-decoration: underline;
        }
        .guest-table {
            width: 94.25%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .guest-table th, .guest-table td {
            border: 1.2px solid #000000;
            padding: 3px 5px;
            font-size: 9pt;
        }
        .guest-table th {
            height: 24pt;
            text-align: center;
            font-weight: bold;
            background-color: #ffffff;
            text-decoration: underline;
        }
        .certify-p {
            text-align: justify;
            font-size: 10pt;
            line-height: 1.25;
            margin-top: 6px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .date-p {
            font-size: 10pt;
            margin-top: 6px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .sig-container {
            width: 100%;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .sig-box {
            float: right;
            width: 380px;
            text-align: center;
        }
        .sig-name {
            font-size: 10pt;
            font-weight: normal;
            margin-bottom: 3px;
        }
        .sig-line {
            border-top: 1.2px solid #000000;
            padding-top: 2px;
            font-size: 9pt;
            line-height: 1.2;
            font-weight: normal;
        }
        .rules-section {
            clear: both;
            font-size: 9.5pt;
            line-height: 1.2;
            padding-top: 0;
            font-weight: bold;
        }
        .rules-line {
            margin: 0 0 1px 0;
        }
    </style>
</head>
<body>

    <!-- Header with Urban Deca Homes Ortigas Logo right-aligned -->
    <table class="header-box">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;" class="header-logo-td">
                @if(!empty($decaLogoBase64))
                    <img src="{{ $decaLogoBase64 }}" style="width: 172px; height: auto;" alt="Urban Deca Homes Ortigas Condominium Corporation">
                @endif
            </td>
        </tr>
    </table>

    <!-- Official Document Title -->
    <div class="doc-title"><span class="underlined">GUEST FORM</span></div>

    <!-- Authorization Statement (Verbatim Deca) -->
    <p class="authorize-p">
        <span class="underlined">This is to authorize the following guest (s) to occupy and use my unit located at Building</span><br>
        <span class="underlined">&nbsp;{{ $buildingLetter ?? 'P' }}&nbsp;</span> &nbsp; <span class="underlined">Unit</span> &nbsp; <span class="underlined">&nbsp;{{ $unitOnlyNumber ?? '718' }}&nbsp;</span> &nbsp; <span class="underlined">for the period covering form</span> &nbsp; <span class="underlined">&nbsp;{{ $booking->check_in_date->format('M j') }}-{{ $booking->check_out_date->format('j') }}&nbsp;</span>.
    </p>

    <!-- Table of Guests (Verbatim Deca Building P) -->
    <table class="guest-table">
        <thead>
            <tr>
                <th style="width: 30.5%;">List of Guest(s)</th>
                <th style="width: 31.6%;">Signature of Guest(s)</th>
                <th style="width: 37.9%; text-align: center;">
                    Proof of Identification<br>
                    <span style="font-weight: normal; font-size: 8.5pt;">(please attached copy of ID)</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <!-- Primary Guest -->
            <tr>
                <td style="font-weight: bold; text-align: center;">{{ $booking->guest_name }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>

            <!-- Companion Roster -->
            @php $rowCount = 1; @endphp
            @if(!empty($booking->guest_roster) && is_array($booking->guest_roster))
                @foreach($booking->guest_roster as $companion)
                    @if(!empty($companion['name']))
                        @php $rowCount++; @endphp
                        <tr>
                            <td style="text-align: center;">{{ $companion['name'] }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endif
                @endforeach
            @endif

            <!-- Fill remaining empty rows to match authentic Deca Form P (5 rows) -->
            @for($i = $rowCount; $i < 5; $i++)
                <tr>
                    <td style="height: 12pt;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Certification Paragraph (Verbatim Deca) -->
    <p class="certify-p">
        <span class="underlined">This is to certify that I have oriented my guest(s) on the existing house rules and regulations of The Urban Deca Homes Ortigas Condominium Corporation (including some of the most applicable house rules itemized below) and that any violations that will be committed by my guest(s) will be my liability to the Condominium Corporation.</span>
    </p>

    <!-- Execution Date Statement -->
    <p class="date-p">
        <span class="underlined">Given this</span> &nbsp; <span class="underlined">&nbsp;{{ $booking->created_at->format('M') }}&nbsp;</span> &nbsp; <span class="underlined">day of</span> &nbsp; <span class="underlined">&nbsp;{{ $booking->created_at->format('j') }}&nbsp;</span> &nbsp; <span class="underlined">, 20</span><span class="underlined">{{ $booking->created_at->format('y') }}</span>.
    </p>

    <!-- Signature Block (Aurelio J. Budol Sr.) -->
    <div class="sig-container">
        <div class="sig-box">
            <div class="sig-name">Aurelio J. Budol Sr.</div>
            <div class="sig-line">Signature over Printed Name of Unit Owner or Authorized Representative</div>
        </div>
    </div>

    <!-- Abbreviated House Rules (Verbatim Deca Docx) -->
    <div class="rules-section">
        <p class="rules-line"><span class="underlined">Abbreviated house rules for guest(s):</span></p>
        <p class="rules-line"><span class="underlined">1. No pets of any kind (except tropical fishes) is allowed within the property.</span></p>
        <p class="rules-line"><span class="underlined">2. Urban Deca Homes Ortigas is a No-Smoking complex, no smoking policy is observed in all common areas within the property.</span></p>
        <p class="rules-line"><span class="underlined">3. Noise control: Avoid loud music or of use of amplified audio equipment.</span></p>
        <p class="rules-line"><span class="underlined">4. Building wide quiet hours: Sunday to Thursday: 10pm to 8am Friday to Saturday: 12am to 9am</span></p>
        <p class="rules-line"><span class="underlined">5. Smoking is prohibited at all common areas of the building.</span></p>
    </div>

</body>
</html>
