<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 24px; background-color: #f8fafc; }
        .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: #1e3a8a; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 24px; }
        .badge { background: #dbeafe; color: #1e40af; font-weight: bold; font-size: 12px; padding: 4px 10px; border-radius: 9999px; display: inline-block; }
        .info-row { display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding: 8px 0; font-size: 14px; }
        .footer { padding: 16px 24px; background: #f8fafc; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 style="margin: 0; font-size: 20px;">DirectStay Booking Clearance</h2>
            <p style="margin: 4px 0 0 0; opacity: 0.9; font-size: 13px;">Official Gate Pass Issued &bull; {{ $building->name }}</p>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $booking->guest_name }}</strong>,</p>
            <p>Your payment and identification documents have been verified by the host. Your official building move-in gate pass is attached to this email as a PDF.</p>

            <div style="background-color: #f8fafc; border-radius: 8px; padding: 16px; margin: 16px 0;">
                <div style="font-weight: bold; color: #0f172a; margin-bottom: 8px;">Reservation Summary:</div>
                <div style="font-size: 13px; line-height: 1.8;">
                    <strong>Booking Reference:</strong> {{ $booking->booking_code }}<br>
                    <strong>Building & Unit:</strong> {{ $building->name }} - {{ $unit->unit_number }}<br>
                    <strong>Check-in:</strong> {{ $booking->check_in_date->format('l, F d, Y') }} (2:00 PM)<br>
                    <strong>Check-out:</strong> {{ $booking->check_out_date->format('l, F d, Y') }} (12:00 PM)<br>
                    <strong>Security Deposit:</strong> ₱{{ number_format($booking->advance_deposit_amount, 2) }} (Held / Verified)
                </div>
            </div>

            <div style="background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 12px; font-size: 13px; color: #065f46; border-radius: 4px; margin-bottom: 16px;">
                <strong>Lobby Arrival Instructions:</strong> Please present the attached PDF Gate Pass along with your original valid Government ID to the Lobby Security upon arrival.
            </div>

            <p style="font-size: 13px; color: #64748b;">
                A copy of this gate pass has also been dispatched to the Building Security & Property Management lobby desk ({{ $building->admin_email }}).
            </p>
        </div>
        <div class="footer">
            DirectStay &bull; Automated Direct-Booking & Security Compliance Platform
        </div>
    </div>
</body>
</html>
