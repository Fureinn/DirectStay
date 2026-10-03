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
    @stack('styles')
</head>
<body class="flex flex-col min-h-screen font-sans antialiased selection:bg-emerald-600 selection:text-white">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-50 glass-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                <!-- Brand -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('units.index') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-blue-700 flex items-center justify-center text-white font-black text-sm shadow-md shadow-blue-600/25 group-hover:scale-105 group-active:scale-95 transition-all duration-200">
                            DS
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-black tracking-tight text-slate-900 leading-none">
                                Direct<span class="text-blue-600">Stay</span>
                            </span>
                            <span class="text-[9px] uppercase tracking-wider font-extrabold text-slate-400 mt-0.5 leading-none">
                                Ortigas &bull; Pasig
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Center Segmented Pill Navigation -->
                <nav class="hidden md:flex items-center p-1 rounded-full bg-slate-200/50 backdrop-blur-md border border-white/60 shadow-inner gap-0.5">
                    <a href="{{ route('units.index') }}"
                       class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all {{ request()->routeIs('units.*') && !request()->routeIs('host.units.*') ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Explore
                    </a>

                    @auth
                        @if (auth()->user()->isHost())
                            <a href="{{ route('host.dashboard') }}"
                               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all {{ request()->routeIs('host.dashboard') ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('host.units.index') }}"
                               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all {{ request()->routeIs('host.units.*') ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                Units
                            </a>
                            <a href="{{ route('host.verifications.index') }}"
                               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all {{ request()->routeIs('host.verifications.*') ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                Verifications
                            </a>
                        @else
                            <a href="{{ route('customer.bookings') }}"
                               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all {{ request()->routeIs('customer.bookings') ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                My Bookings
                            </a>
                        @endif
                    @endauth
                </nav>

                <!-- Right Action & Profile Area -->
                <div class="hidden md:flex items-center gap-2.5">
                    @auth
                        <!-- User Profile Glass Dropdown -->
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen"
                                    @click.outside="userMenuOpen = false"
                                    type="button"
                                    class="flex items-center gap-2 p-1.5 pr-3 rounded-full bg-white/60 hover:bg-white/90 border border-white/70 backdrop-blur-md shadow-xs transition-all btn-press cursor-pointer">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black shadow-xs {{ auth()->user()->isHost() ? 'bg-gradient-to-tr from-emerald-500 to-teal-600 text-white' : 'bg-gradient-to-tr from-blue-600 to-indigo-600 text-white' }}">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-bold text-slate-800 max-w-[120px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full {{ auth()->user()->isHost() ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-700' }}">
                                    {{ auth()->user()->isHost() ? 'Host' : 'Guest' }}
                                </span>
                                <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Floating Frosted Glass Dropdown Menu -->
                            <div x-show="userMenuOpen"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                 x-cloak
                                 class="absolute right-0 mt-2 w-56 rounded-2xl glass-surface-dense border border-white/70 shadow-2xl backdrop-blur-2xl p-1.5 z-50">
                                
                                <div class="px-3 py-2.5 border-b border-slate-200/50">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono truncate">{{ auth()->user()->email ?? auth()->user()->phone ?? 'DirectStay Member' }}</p>
                                </div>

                                <div class="py-1 space-y-0.5">
                                    @if(auth()->user()->isHost())
                                        <a href="{{ route('host.dashboard') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-white/60 rounded-xl transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                            Host Dashboard
                                        </a>
                                        <a href="{{ route('host.units.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-white/60 rounded-xl transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            Manage Units
                                        </a>
                                        <a href="{{ route('host.verifications.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-white/60 rounded-xl transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            Verifications Queue
                                        </a>
                                        <a href="{{ route('units.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-white/60 rounded-xl transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View Guest Portal
                                        </a>
                                    @else
                                        <a href="{{ route('customer.bookings') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-white/60 rounded-xl transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            My Bookings
                                        </a>
                                        <a href="{{ route('customer.settings.edit') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-white/60 rounded-xl transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Account Settings
                                        </a>
                                    @endif
                                </div>

                                <div class="pt-1 border-t border-slate-200/50">
                                    <form method="POST" action="{{ auth()->user()->isHost() ? route('host.logout') : route('customer.logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2 w-full px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50/80 rounded-xl transition-colors cursor-pointer">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('customer.login') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-full transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('customer.register') }}" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 rounded-full shadow-sm shadow-blue-500/25 hover:shadow-md transition-all btn-press">
                            Register
                        </a>
                        <a href="{{ route('host.login') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white/50 hover:bg-white/90 border border-white/70 rounded-full backdrop-blur-md transition-all shadow-xs">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Host Portal
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex md:hidden items-center">
                    <button id="mobileMenuButton" type="button" class="p-2 rounded-xl bg-white/50 hover:bg-white/90 border border-white/60 text-slate-700 hover:text-slate-900 backdrop-blur-md shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all btn-press cursor-pointer" aria-label="Toggle navigation">
                        <svg id="hamburgerIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="closeIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobileMenu" class="glass-mobile-drawer hidden px-4 pt-3 pb-6 space-y-3">
            <a href="{{ route('units.index') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('units.*') && !request()->routeIs('host.units.*') ? 'bg-blue-500/10 text-blue-700 font-bold' : 'text-slate-700 hover:bg-white/60' }}">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Explore Units
            </a>

            @auth
                @if (auth()->user()->isHost())
                    <div class="pt-2 border-t border-slate-200/50 space-y-1">
                        <div class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Host Management</div>
                        <a href="{{ route('host.dashboard') }}" class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('host.dashboard') ? 'bg-blue-500/10 text-blue-700 font-bold' : 'text-slate-700 hover:bg-white/60' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('host.units.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('host.units.*') ? 'bg-blue-500/10 text-blue-700 font-bold' : 'text-slate-700 hover:bg-white/60' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Manage Units & Photos
                        </a>
                        <a href="{{ route('host.verifications.index') }}" class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('host.verifications.*') ? 'bg-blue-500/10 text-blue-700 font-bold' : 'text-slate-700 hover:bg-white/60' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Verifications Queue
                        </a>
                        <form method="POST" action="{{ route('host.logout') }}" class="pt-2">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 w-full px-3.5 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out (Host: {{ auth()->user()->name }})
                            </button>
                        </form>
                    </div>
                @else
                    <div class="pt-2 border-t border-slate-200/50 space-y-1">
                        <div class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Guest Account ({{ auth()->user()->name }})</div>
                        <a href="{{ route('customer.bookings') }}" class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('customer.bookings') ? 'bg-blue-500/10 text-blue-700 font-bold' : 'text-slate-700 hover:bg-white/60' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            My Bookings
                        </a>
                        <a href="{{ route('customer.settings.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('customer.settings.*') ? 'bg-blue-500/10 text-blue-700 font-bold' : 'text-slate-700 hover:bg-white/60' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Account Settings
                        </a>
                        <form method="POST" action="{{ route('customer.logout') }}" class="pt-2">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 w-full px-3.5 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50/60 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <div class="pt-3 border-t border-slate-200/50 space-y-2">
                    <a href="{{ route('customer.login') }}" class="block w-full text-center px-4 py-2.5 rounded-xl border border-white/60 bg-white/60 backdrop-blur-md font-semibold text-slate-800 text-sm shadow-xs">
                        Customer Login
                    </a>
                    <a href="{{ route('customer.register') }}" class="block w-full text-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-sm shadow-md shadow-blue-500/25">
                        Register Account
                    </a>
                    <a href="{{ route('host.login') }}" class="flex items-center justify-center gap-1.5 w-full text-center px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
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
    <nav class="fixed inset-x-0 bottom-0 z-50 glass-bottom-bar px-3 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 md:hidden" aria-label="Mobile navigation">
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
    <footer class="glass-footer mt-16 py-8">
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
