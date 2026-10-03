@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('host.units.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-700 mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Units List
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Edit Unit: {{ $unit->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">Manage unit details, rental rates, and visual gallery photos</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('units.show', $unit) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Public Listing
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-3 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Unit Details Form -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Unit Information
                </h2>

                <form action="{{ route('host.units.update', $unit) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unit Title</label>
                        <input type="text" name="title" value="{{ old('title', $unit->title) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unit Number</label>
                        <input type="text" name="unit_number" value="{{ old('unit_number', $unit->unit_number) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Building</label>
                        <input type="text" disabled value="{{ $unit->building ? $unit->building->name . ' (' . $unit->building->code . ')' : 'Urban Deca Homes Ortigas' }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm shadow-xs">
                        <p class="text-[11px] text-slate-400 mt-1">{{ $unit->building ? $unit->building->address : 'Ortigas Avenue Extension, Pasig City' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nightly Rate (₱)</label>
                            <input type="number" step="0.01" name="base_price_per_night" value="{{ old('base_price_per_night', $unit->base_price_per_night) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs font-semibold text-slate-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Deposit (₱)</label>
                            <input type="number" step="0.01" name="advance_deposit_required" value="{{ old('advance_deposit_required', $unit->advance_deposit_required) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Max Guests</label>
                        <input type="number" name="max_guests" value="{{ old('max_guests', $unit->max_guests) }}" min="1" max="20" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description & Highlights</label>
                        <textarea name="description" rows="4"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs">{{ old('description', $unit->description) }}</textarea>
                    </div>

                    <button type="submit" class="w-full mt-4 py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm shadow-xs transition cursor-pointer">
                        Save Unit Details
                    </button>
                </form>
            </div>
        </div>

        <!-- Photo Gallery & Upload Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Upload Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upload Unit Photos
                </h2>
                <p class="text-sm text-slate-500 mb-5">Add high quality photos of the living room, bedrooms, kitchen, views, and amenities. Supported formats: JPG, PNG, WEBP (up to 5MB each).</p>

                <form action="{{ route('host.units.photos.upload', $unit) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-emerald-500 transition bg-slate-50/50">
                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="mt-4 flex text-sm leading-6 text-slate-600 justify-center">
                            <label for="photos" class="relative cursor-pointer rounded-md font-semibold text-emerald-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-emerald-600 focus-within:ring-offset-2 hover:text-emerald-500">
                                <span>Choose files to upload</span>
                                <input id="photos" name="photos[]" type="file" multiple accept="image/*" class="sr-only" required onchange="updateSelectedFiles(this)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p id="fileSelectionText" class="text-xs text-slate-400 mt-2">You can select multiple photos at once</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Upload Selected Photos
                        </button>
                    </div>
                </form>
            </div>

            <!-- Existing Photos Gallery -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Current Unit Photos</h2>
                        <p class="text-xs text-slate-500">The cover photo is featured as the main card thumbnail.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                        {{ count($unit->images ?? []) }} {{ Str::plural('Photo', count($unit->images ?? [])) }}
                    </span>
                </div>

                @if (empty($unit->images) || count($unit->images) === 0)
                    <div class="text-center py-12 px-4 border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-semibold text-slate-700">No photos uploaded yet</p>
                        <p class="text-xs text-slate-500 mt-1">Upload images above to showcase this unit to prospective guests.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($unit->images as $photo)
                            @php
                                $isCover = ($unit->cover_image === $photo);
                            @endphp
                            <div class="group relative rounded-xl overflow-hidden border {{ $isCover ? 'border-2 border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200' }} bg-slate-100 flex flex-col justify-between shadow-sm">
                                <div class="relative h-44 w-full overflow-hidden bg-slate-200">
                                    <img src="{{ asset($photo) }}" alt="{{ $unit->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    
                                    @if ($isCover)
                                        <span class="absolute top-2 left-2 px-2.5 py-1 rounded-md bg-emerald-600 text-white text-[11px] font-bold shadow tracking-wide uppercase">
                                            Cover Photo
                                        </span>
                                    @endif
                                </div>

                                <div class="p-3 bg-white flex items-center justify-between gap-2 border-t border-slate-100">
                                    @if (!$isCover)
                                        <form action="{{ route('host.units.photos.cover', $unit) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="image" value="{{ $photo }}">
                                            <button type="submit" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                                Set as Cover
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Main Cover
                                        </span>
                                    @endif

                                    <form action="{{ route('host.units.photos.delete', $unit) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this photo?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="image" value="{{ $photo }}">
                                        <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function updateSelectedFiles(input) {
    const textElement = document.getElementById('fileSelectionText');
    if (input.files && input.files.length > 0) {
        textElement.textContent = input.files.length === 1 
            ? input.files[0].name 
            : `${input.files.length} photos selected ready to upload`;
        textElement.classList.add('text-emerald-600', 'font-medium');
        textElement.classList.remove('text-slate-400');
    }
}
</script>
@endsection
