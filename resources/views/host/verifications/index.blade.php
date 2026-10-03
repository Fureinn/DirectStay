@extends('layouts.app')

@section('title', 'Verifications Queue - DirectStay')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">Security Compliance</div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pending Verifications Queue</h1>
            <p class="text-xs text-slate-500">Review guest GCash payments, Gov't ID files, and issue building gate passes</p>
        </div>
        <a href="{{ route('host.dashboard') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[11px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Booking Ref</th>
                        <th class="py-3.5 px-6">Building & Unit</th>
                        <th class="py-3.5 px-6">Guest Info</th>
                        <th class="py-3.5 px-6">Dates</th>
                        <th class="py-3.5 px-6">Deposit (₱1,000)</th>
                        <th class="py-3.5 px-6">Vault Documents</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($bookings as $b)
                        @php
                            $hasReceipt = $b->complianceDocuments->contains('document_type', 'payment_receipt');
                            $governmentIdCount = $b->complianceDocuments->filter(fn ($document) => str_starts_with($document->document_type, 'gov_id_'))->count();
                            $hasIds = $governmentIdCount === $b->guest_count;
                            $hasSelfie = $b->complianceDocuments->contains('document_type', 'selfie');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                {{ $b->booking_code }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900">{{ $b->unit->building->name }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $b->unit->unit_number }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900">{{ $b->guest_name }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $b->guest_email }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div>{{ $b->check_in_date->format('M d') }} - {{ $b->check_out_date->format('M d, Y') }}</div>
                                <span class="text-[11px] text-slate-400">{{ $b->nights_count }} Night(s)</span>
                            </td>
                            <td class="py-4 px-6">
                                @if($hasReceipt)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        Receipt Uploaded
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                        Unpaid / Missing
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $hasIds ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-400' }}">
                                        IDs {{ $governmentIdCount }}/{{ $b->guest_count }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $hasSelfie ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-400' }}">
                                        Selfie
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $b->waiver_accepted_at ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400' }}">
                                        Waiver
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('host.verifications.show', $b) }}"
                                   class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all">
                                    <span>Triage & Issue Gate Pass</span>
                                    <span>&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <p class="text-sm font-semibold text-slate-600 mb-1">Queue is clear!</p>
                                <p class="text-xs">No reservations currently awaiting compliance review.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
