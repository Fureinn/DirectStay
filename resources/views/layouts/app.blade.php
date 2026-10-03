<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'DirectStay - Direct-Booking & Property Compliance Engine (Urban Deca Homes Ortigas)')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen font-sans antialiased selection:bg-emerald-600 selection:text-white">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-50 bg-white/85 backdrop-blur-xl border-b border-white/70 shadow-[0_1px_0_rgb(15_23_42_/_0.04)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('units.index') }}" class="flex items-center gap-3 group">
                        <div class="brand-mark w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white font-black text-xl group-hover:scale-105 transition-transform duration-200">
                            DS
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight text-slate-900">Direct<span class="text-emerald-600">Stay</span></span>
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400">Urban Deca Homes Ortigas</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="desktop-nav hidden md:flex items-center text-sm font-medium">
                    <a href="{{ route('units.index') }}" class="text-slate-600 hover:text-emerald-600 transition-colors {{ request()->routeIs('units.*') && !request()->routeIs('host.units.*') ? 'text-emerald-600 font-semibold' : '' }}">
                        Explore Units
                    </a>

                    @auth
                        @if (auth()->user()->isHost())
                            <span class="text-slate-200">|</span>
                            <a href="{{ route('host.dashboard') }}" class="text-slate-600 hover:text-emerald-600 transition-colors {{ request()->routeIs('host.dashboard') ? 'text-emerald-600 font-semibold' : '' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('host.units.index') }}" class="text-slate-600 hover:text-emerald-600 transition-colors {{ request()->routeIs('host.units.*') ? 'text-emerald-600 font-semibold' : '' }}">
                                Manage Units
                            </a>
                            <a href="{{ route('host.verifications.index') }}" class="text-slate-600 hover:text-emerald-600 transition-colors {{ request()->routeIs('host.verifications.*') ? 'text-emerald-600 font-semibold' : '' }}">
                                Verifications
                            </a>
                            <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                                <span class="text-xs font-semibold px-2 py-1 rounded-md bg-emerald-100 text-emerald-800">
                                    Host: {{ auth()->user()->name }}
                                </span>
                                <form method="POST" action="{{ route('host.logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 transition-colors">
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('customer.bookings') }}" class="text-slate-600 hover:text-emerald-600 transition-colors {{ request()->routeIs('customer.bookings') ? 'text-emerald-600 font-semibold' : '' }}">
                                My Bookings
                            </a>
                            <a href="{{ route('customer.settings.edit') }}" class="text-slate-600 hover:text-emerald-600 transition-colors {{ request()->routeIs('customer.settings.*') ? 'text-emerald-600 font-semibold' : '' }}">
                                Account Settings
                            </a>
                            <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                                <div class="text-left">
                                    <span class="block text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                                    <span class="block text-[11px] text-slate-400">Guest</span>
                                </div>
                                <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg border border-slate-200 transition-colors">
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
                            <a href="{{ route('customer.login') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 hover:text-emerald-600 rounded-lg transition-colors">
                                Customer Login
                            </a>
                            <a href="{{ route('customer.register') }}" class="px-3.5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-500/20 transition-all">
                                Register
                            </a>
                            <a href="{{ route('host.login') }}" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Host Login
                            </a>
                        </div>
                    @endauth
                </nav>

                <!-- Mobile Hamburger Button -->
                <div class="hidden md:hidden items-center">
                    <button id="mobileMenuButton" type="button" class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" aria-label="Toggle navigation">
                        <svg id="hamburgerIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobileMenu" class="hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-3">
            <a href="{{ route('units.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-emerald-600">
                Explore Units
            </a>

            @auth
                @if (auth()->user()->isHost())
                    <div class="pt-2 border-t border-slate-100">
                        <div class="px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-400">Host Management</div>
                        <a href="{{ route('host.dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-emerald-600">
                            Dashboard
                        </a>
                        <a href="{{ route('host.units.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-emerald-600">
                            Manage Units & Photos
                        </a>
                        <a href="{{ route('host.verifications.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-emerald-600">
                            Verifications Queue
                        </a>
                        <form method="POST" action="{{ route('host.logout') }}" class="pt-2 px-3">
                            @csrf
                            <button type="submit" class="w-full text-left font-medium text-rose-600 py-1">
                                Sign Out (Host)
                            </button>
                        </form>
                    </div>
                @else
                    <div class="pt-2 border-t border-slate-100">
                        <div class="px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-400">Guest: {{ auth()->user()->name }}</div>
                        <a href="{{ route('customer.bookings') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-emerald-600">
                            My Bookings
                        </a>
                        <a href="{{ route('customer.settings.edit') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-emerald-600">
                            Account Settings
                        </a>
                        <form method="POST" action="{{ route('customer.logout') }}" class="pt-2 px-3">
                            @csrf
                            <button type="submit" class="w-full text-left font-medium text-rose-600 py-1">
                                Sign Out
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <a href="{{ route('customer.login') }}" class="block w-full text-center px-4 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-700 hover:bg-slate-50 text-sm">
                        Customer Login
                    </a>
                    <a href="{{ route('customer.register') }}" class="block w-full text-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 text-sm shadow-sm">
                        Register Account
                    </a>
                    <a href="{{ route('host.login') }}" class="block w-full text-center px-4 py-2 text-xs font-medium text-slate-500 hover:text-slate-800">
                        Host Portal Access
                    </a>
                </div>
            @endauth
        </div>
    </header>

    <!-- Notifications / Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 shadow-xs">
                <div class="flex items-center gap-2 font-semibold text-rose-900 mb-1">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Please correct the following errors:
                </div>
                <ul class="list-disc list-inside space-y-0.5 ml-7 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 pb-20 md:pb-0">
        @yield('content')
    </main>

    <!-- Mobile bottom navigation -->
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200/80 bg-white/95 px-3 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur-xl md:hidden" aria-label="Mobile navigation">
        @auth
            @if (auth()->user()->isHost())
                <div class="grid grid-cols-4 gap-1">
                    <a href="{{ route('host.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('host.dashboard') ? 'mobile-nav-item-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 11.5 12 4l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-8.5Z" stroke-width="1.8" stroke-linejoin="round"/></svg><span>Home</span>
                    </a>
                    <a href="{{ route('host.units.index') }}" class="mobile-nav-item {{ request()->routeIs('host.units.*') ? 'mobile-nav-item-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 20V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v15M2 20h20M8 7h2m4 0h2M8 11h2m4 0h2M8 15h2m4 0h2" stroke-width="1.8" stroke-linecap="round"/></svg><span>Units</span>
                    </a>
                    <a href="{{ route('host.verifications.index') }}" class="mobile-nav-item {{ request()->routeIs('host.verifications.*') ? 'mobile-nav-item-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Review</span>
                    </a>
                    <form method="POST" action="{{ route('host.logout') }}">
                        @csrf
                        <button type="submit" class="mobile-nav-item w-full text-rose-600">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 8V6a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h5a3 3 0 0 0 3-3v-2m-5-4h10m-3-3 3 3-3 3" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Sign out</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="grid grid-cols-4 gap-1">
                    <a href="{{ route('units.index') }}" class="mobile-nav-item {{ request()->routeIs('units.*') ? 'mobile-nav-item-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m3 11 9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z" stroke-width="1.8" stroke-linejoin="round"/></svg><span>Explore</span>
                    </a>
                    <a href="{{ route('customer.bookings') }}" class="mobile-nav-item {{ request()->routeIs('customer.bookings') ? 'mobile-nav-item-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8"/><path d="M8 3v4m8-4v4M4 10h16m-8 4h4" stroke-width="1.8" stroke-linecap="round"/></svg><span>Trips</span>
                    </a>
                    <a href="{{ route('customer.settings.edit') }}" class="mobile-nav-item {{ request()->routeIs('customer.settings.*') ? 'mobile-nav-item-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="3.25" stroke-width="1.8"/><path d="M5 21c.7-3.3 3.1-5 7-5s6.3 1.7 7 5" stroke-width="1.8" stroke-linecap="round"/></svg><span>Account</span>
                    </a>
                    <form method="POST" action="{{ route('customer.logout') }}">
                        @csrf
                        <button type="submit" class="mobile-nav-item w-full text-rose-600">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 8V6a3 3 0 0 0-3-3H7a3 3 0 0 0 3 3h5a3 3 0 0 0 3-3v-2m-5-4h10m-3-3 3 3-3 3" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Sign out</span>
                        </button>
                    </form>
                </div>
            @endif
        @else
            <div class="grid grid-cols-3 gap-1">
                <a href="{{ route('units.index') }}" class="mobile-nav-item {{ request()->routeIs('units.*') ? 'mobile-nav-item-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m3 11 9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z" stroke-width="1.8" stroke-linejoin="round"/></svg><span>Explore</span>
                </a>
                <a href="{{ route('customer.login') }}" class="mobile-nav-item {{ request()->routeIs('customer.login*') ? 'mobile-nav-item-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 8V6a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h5a3 3 0 0 0 3-3v-2m-5-4h10m-3-3 3 3-3 3" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Sign in</span>
                </a>
                <a href="{{ route('customer.register') }}" class="mobile-nav-item {{ request()->routeIs('customer.register*') ? 'mobile-nav-item-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="3.25" stroke-width="1.8"/><path d="M5 21c.7-3.3 3.1-5 7-5s6.3 1.7 7 5m3-10v6m3-3h-6" stroke-width="1.8" stroke-linecap="round"/></svg><span>Join</span>
                </a>
            </div>
        @endauth
    </nav>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div>
                &copy; {{ date('Y') }} <strong>DirectStay</strong> &bull; Direct-Booking & Property Compliance Platform (Urban Deca Homes Ortigas).
            </div>
            <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                <span>KM 19 Ortigas Ave Ext, Brgy. Rosario, Pasig City</span>
                <span>&bull;</span>
                <span>Zero OTA Commissions</span>
                <span>&bull;</span>
                <span class="text-emerald-600 font-semibold">Lean 5% Service Fee (vs 17-20% OTA)</span>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobileMenuButton');
            const menu = document.getElementById('mobileMenu');
            const hamburger = document.getElementById('hamburgerIcon');
            const close = document.getElementById('closeIcon');

            if (btn && menu) {
                btn.addEventListener('click', function () {
                    const isHidden = menu.classList.contains('hidden');
                    if (isHidden) {
                        menu.classList.remove('hidden');
                        hamburger.classList.add('hidden');
                        close.classList.remove('hidden');
                    } else {
                        menu.classList.add('hidden');
                        hamburger.classList.remove('hidden');
                        close.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
