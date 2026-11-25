@if(!empty($guestId['data_uri']))
    <div style="text-align: center;">
        <img src="{{ $guestId['data_uri'] }}" alt="ID" style="max-width: 80pt; max-height: 28pt; border: 0.5px solid #94a3b8;">
    </div>
@else
    &nbsp;
@endif
