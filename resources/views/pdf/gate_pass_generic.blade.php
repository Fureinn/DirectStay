<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Gate Pass - {{ $booking->booking_code }}</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1a202c; font-size: 11px; line-height: 1.35; }
        .header-table { width: 100%; border-bottom: 2px solid #334155; padding-bottom: 8px; margin-bottom: 12px; }
        .logo-title { font-size: 16px; font-weight: bold; color: #1e293b; text-transform: uppercase; }
        .badge { background-color: #3b82f6; color: #ffffff; font-weight: bold; font-size: 10px; padding: 3px 8px; border-radius: 4px; display: inline-block; }
        .section-title { background-color: #f1f5f9; color: #0f172a; font-size: 10.5px; font-weight: bold; text-transform: uppercase; padding: 4px 8px; border-left: 3px solid #3b82f6; margin-top: 10px; margin-bottom: 6px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table td { padding: 4px 6px; border: 1px solid #e2e8f0; vertical-align: top; }
        .label { font-weight: bold; color: #475569; width: 28%; background-color: #f8fafc; }
        .highlight { font-weight: bold; color: #1e40af; }
        .roster-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 8px; }
        .roster-table th { background-color: #e2e8f0; padding: 4px 6px; border: 1px solid #cbd5e1; font-size: 9.5px; text-align: left; }
        .roster-table td { padding: 4px 6px; border: 1px solid #cbd5e1; font-size: 9.5px; }
        .signature-table { width: 100%; margin-top: 16px; border-collapse: collapse; }
        .signature-cell { width: 33.3%; text-align: center; vertical-align: top; padding: 4px 8px; }
        .sig-line { border-top: 1px solid #0f172a; margin-top: 36px; padding-top: 3px; font-size: 9px; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="logo-title">{{ $building->name }}</div>
                <div style="font-weight: bold; font-size: 11px; color: #334155;">OFFICIAL GUEST GATE PASS & CLEARANCE</div>
                <div style="font-size: 9px; color: #64748b;">DirectStay SaaS Integration</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <span class="badge">REF: {{ $booking->booking_code }}</span>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 4px;">Issued: {{ $generatedAt }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Property & Accommodation Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Building & Unit:</td>
            <td class="value highlight">{{ $building->name }} - {{ $unit->unit_number }}</td>
            <td class="label">Property Management:</td>
            <td class="value">{{ $building->admin_email }}</td>
        </tr>
        <tr>
            <td class="label">Host / Authorized Lessor:</td>
            <td class="value">{{ $host->name }}</td>
            <td class="label">Host Contact / Email:</td>
            <td class="value">{{ $host->email }}</td>
        </tr>
    </table>

    <div class="section-title">Stay Dates & Primary Guest</div>
    <table class="info-table">
        <tr>
            <td class="label">Lead Guest:</td>
            <td class="value highlight">{{ $booking->guest_name }}</td>
            <td class="label">Contact / Phone:</td>
            <td class="value">{{ $booking->guest_phone }}</td>
        </tr>
        <tr>
            <td class="label">Check-in:</td>
            <td class="value">{{ $booking->check_in_date->format('F d, Y') }} (2:00 PM)</td>
            <td class="label">Check-out:</td>
            <td class="value">{{ $booking->check_out_date->format('F d, Y') }} (12:00 PM)</td>
        </tr>
        <tr>
            <td class="label">Deposit Status:</td>
            <td class="value">₱{{ number_format($booking->advance_deposit_amount, 2) }} (Verified)</td>
            <td class="label">Security Clearance:</td>
            <td class="value"><span style="color: #15803d; font-weight: bold;">APPROVED & CLEARED</span></td>
        </tr>
    </table>

    <div class="section-title">Guest Roster</div>
    <table class="roster-table">
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 50%;">Name</th>
                <th style="width: 40%;">Designation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><strong>{{ $booking->guest_name }}</strong></td>
                <td>Lead Guest (Verified)</td>
            </tr>
            @if(!empty($booking->guest_roster) && is_array($booking->guest_roster))
                @foreach($booking->guest_roster as $index => $companion)
                    @if(!empty($companion['name']))
                        <tr>
                            <td>{{ $index + 2 }}</td>
                            <td>{{ $companion['name'] }}</td>
                            <td>{{ $companion['relationship'] ?? 'Guest' }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="sig-line">{{ $booking->guest_name }}</div>
                <div style="font-size: 8px; color: #64748b;">Guest Signature</div>
            </td>
            <td class="signature-cell">
                <div class="sig-line">{{ $host->name }}</div>
                <div style="font-size: 8px; color: #64748b;">Authorized Host Signature</div>
            </td>
            <td class="signature-cell">
                <div class="sig-line">LOBBY DESK</div>
                <div style="font-size: 8px; color: #64748b;">Building Security</div>
            </td>
        </tr>
    </table>
</body>
</html>
