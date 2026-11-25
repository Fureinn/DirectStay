@if(!empty($attachedGuestIds))
    <div style="page-break-before: always;"></div>

    <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #0f172a;">
        <table style="width: 100%; border-bottom: 2px solid #0f172a; padding-bottom: 6pt; margin-bottom: 12pt;">
            <tr>
                <td style="vertical-align: bottom;">
                    <div style="font-size: 13pt; font-weight: bold; letter-spacing: 0.3px;">
                        ATTACHED PROOF OF IDENTIFICATION
                    </div>
                    <div style="font-size: 8.5pt; color: #475569; margin-top: 2pt;">
                        {{ $building->name }} &bull; Unit {{ $unit->unit_number }} &bull; Ref: {{ $booking->booking_code }}
                    </div>
                </td>
                <td style="text-align: right; vertical-align: bottom; font-size: 8.5pt; color: #475569;">
                    Stay: {{ $booking->check_in_date->format('M d, Y') }} &ndash; {{ $booking->check_out_date->format('M d, Y') }}<br>
                    Issued: {{ $generatedAt }}
                </td>
            </tr>
        </table>

        @foreach($attachedGuestIds as $guestId)
            <div style="border: 1px solid #cbd5e1; border-radius: 6pt; padding: 10pt; margin-bottom: 12pt; page-break-inside: avoid; background-color: #f8fafc;">
                <div style="font-size: 9.5pt; font-weight: bold; color: #0f172a; margin-bottom: 4pt; border-bottom: 1px solid #e2e8f0; padding-bottom: 4pt;">
                    {{ $guestId['occupant_index'] + 1 }}. {{ $guestId['guest_name'] }}
                    <span style="font-weight: normal; font-size: 8.5pt; color: #475569;">
                        ({{ $guestId['role'] }}) &mdash; {{ $guestId['notes'] ?: 'Government Photo ID' }}
                    </span>
                </div>

                @if(!empty($guestId['data_uri']))
                    <div style="text-align: center; padding: 6pt 0; background-color: #ffffff; border: 1px solid #e2e8f0;">
                        <img src="{{ $guestId['data_uri'] }}"
                             alt="Government ID for {{ $guestId['guest_name'] }}"
                             style="max-width: 95%; max-height: 230pt;">
                    </div>
                @else
                    <div style="font-size: 8.5pt; color: #64748b; padding: 12pt; text-align: center; background-color: #ffffff; border: 1px dashed #cbd5e1;">
                        ID file stored in vault ({{ $guestId['filename'] }}) &mdash; format could not be previewed inline.
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
