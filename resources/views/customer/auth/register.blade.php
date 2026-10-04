@extends('layouts.app')

@section('title', 'Guest Registration - DirectStay')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 shadow-xl shadow-slate-200/50 dark:shadow-none transition-colors"
         x-data="{
            name: '{{ old('name', '') }}',
            email: '{{ old('email', '') }}',
            phone: '{{ old('phone', '') }}',
            password: '',
            password_confirmation: '',
            get emailValid() {
                if (!this.email) return null;
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email.trim());
            },
            get passwordStrength() {
                if (!this.password) return 0;
                let score = 0;
                if (this.password.length >= 6) score += 1;
                if (this.password.length >= 8) score += 1;
                if (/[A-Z]/.test(this.password) && /[a-z]/.test(this.password)) score += 1;
                if (/[0-9]/.test(this.password) || /[^A-Za-z0-9]/.test(this.password)) score += 1;
                return score;
            },
            get passwordStrengthLabel() {
                if (this.passwordStrength <= 1) return { text: 'Weak', color: 'bg-rose-500', width: 'w-1/4' };
                if (this.passwordStrength <= 2) return { text: 'Fair', color: 'bg-amber-500', width: 'w-2/4' };
                if (this.passwordStrength <= 3) return { text: 'Good', color: 'bg-blue-500', width: 'w-3/4' };
                return { text: 'Strong', color: 'bg-emerald-500', width: 'w-full' };
            },
            get passwordsMatch() {
                if (!this.password_confirmation) return null;
                return this.password === this.password_confirmation;
            },
            get phoneValid() {
                if (!this.phone) return null;
                return /^(09|\+639)\d{9}$/.test(this.phone.replace(/[-\s]/g, ''));
            }
         }">

        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-3">
                Direct Guest Account
            </span>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Create Guest Account</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Book directly, receive official Deca Gate Passes, and manage staycations</p>
        </div>

        <form action="{{ route('customer.register.post') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Legal Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Full Legal Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" x-model="name"
                       placeholder="As shown on Government ID" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                @error('name')
                    <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address with Real-Time Validation -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Email Address <span class="text-rose-500">*</span>
                    </label>
                    <span x-show="emailValid === true" x-cloak class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Valid email format</span>
                    </span>
                    <span x-show="emailValid === false" x-cloak class="text-[11px] font-bold text-rose-600 dark:text-rose-400">
                        Enter a valid email
                    </span>
                </div>
                <input type="email" name="email" x-model="email"
                       placeholder="e.g. guest@gmail.com" required
                       :class="{
                           'border-emerald-500 ring-1 ring-emerald-500/20': emailValid === true,
                           'border-rose-400 ring-1 ring-rose-400/20': emailValid === false
                       }"
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
                    A 6-digit verification code will be dispatched to this inbox.
                </p>
                @error('email')
                    <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile Phone with Format Helper -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Mobile Phone <span class="text-rose-500">*</span>
                    </label>
                    <span x-show="phoneValid === true" x-cloak class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                        ✓ Valid mobile number
                    </span>
                </div>
                <input type="text" name="phone" x-model="phone"
                       placeholder="e.g. 09171234567" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('phone') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono transition-colors">
                @error('phone')
                    <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password & Live Strength Meter -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" x-model="password" required
                           placeholder="Min 6 characters"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    
                    <!-- Real-Time Password Strength Meter -->
                    <div x-show="password.length > 0" x-cloak class="mt-1.5 space-y-1">
                        <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300 rounded-full"
                                 :class="[passwordStrengthLabel.color, passwordStrengthLabel.width]"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Strength:</span>
                            <span class="font-bold capitalize" x-text="passwordStrengthLabel.text"></span>
                        </div>
                    </div>

                    @error('password')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Confirm Password <span class="text-rose-500">*</span>
                        </label>
                        <span x-show="passwordsMatch === true" x-cloak class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                            ✓ Match
                        </span>
                        <span x-show="passwordsMatch === false" x-cloak class="text-[10px] font-bold text-rose-500">
                            ✕ No match
                        </span>
                    </div>
                    <input type="password" name="password_confirmation" x-model="password_confirmation" required
                           placeholder="Repeat password"
                           :class="{
                               'border-emerald-500': passwordsMatch === true,
                               'border-rose-400': passwordsMatch === false
                           }"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-3.5 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all mt-2 cursor-pointer">
                Continue &amp; Receive Verification Code &rarr;
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
            Already have an account?
            <a href="{{ route('customer.login') }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Sign In
            </a>
        </div>
    </div>
</div>
@endsection
