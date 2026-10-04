@extends('layouts.app')

@section('title', 'Verify Email Code - DirectStay')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 shadow-xl shadow-slate-200/50 dark:shadow-none transition-colors"
         x-data="{
            digits: ['', '', '', '', '', ''],
            cooldown: 60,
            timer: null,
            submitting: false,
            init() {
                this.$nextTick(() => {
                    this.$refs.digit0?.focus();
                });
                this.startTimer();
            },
            startTimer() {
                this.cooldown = 60;
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => {
                    if (this.cooldown > 0) {
                        this.cooldown--;
                    } else {
                        clearInterval(this.timer);
                    }
                }, 1000);
            },
            handleInput(index, event) {
                if (this.submitting) return;
                const val = event.target.value.replace(/[^0-9]/g, '');
                this.digits[index] = val ? val[val.length - 1] : '';
                event.target.value = this.digits[index];

                if (this.digits[index] && index < 5) {
                    this.$refs['digit' + (index + 1)]?.focus();
                }
                this.syncCode();
            },
            handleKeydown(index, event) {
                if (this.submitting) return;
                if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                    this.$refs['digit' + (index - 1)]?.focus();
                }
            },
            handlePaste(event) {
                if (this.submitting) return;
                event.preventDefault();
                const paste = (event.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                if (paste) {
                    for (let i = 0; i < 6; i++) {
                        this.digits[i] = paste[i] || '';
                        if (this.$refs['digit' + i]) {
                            this.$refs['digit' + i].value = this.digits[i];
                        }
                    }
                    const nextIdx = Math.min(paste.length, 5);
                    this.$refs['digit' + nextIdx]?.focus();
                    this.syncCode();
                }
            },
            submitForm() {
                if (this.submitting) return;
                const full = this.digits.join('');
                if (full.length !== 6) return;
                this.submitting = true;
                document.getElementById('hiddenCodeInput').value = full;
                this.$nextTick(() => {
                    document.getElementById('verifyForm').submit();
                });
            },
            syncCode() {
                const full = this.digits.join('');
                document.getElementById('hiddenCodeInput').value = full;
                if (full.length === 6 && !this.submitting) {
                    this.submitForm();
                }
            }
         }">

        <!-- Gmail-style Envelope Graphic -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-blue-700 text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/25">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-2">
                ✉️ Check Your Gmail Inbox
            </span>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Enter Verification Code</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">
                We sent a 6-digit security code to<br>
                <strong class="text-slate-800 dark:text-slate-200 font-mono text-xs">{{ $email ?? 'your email' }}</strong>
            </p>
        </div>

        @if(session('info'))
            <div class="mb-5 p-3 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-200 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-5 p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-200 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @error('code')
            <div class="mb-5 p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-200 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <!-- 6 Segmented OTP Input Boxes -->
        <form action="{{ route('customer.verify.post') }}" method="POST" id="verifyForm" class="space-y-6" @submit.prevent="submitForm()">
            @csrf
            <input type="hidden" name="code" id="hiddenCodeInput">

            <div>
                <label class="block text-center text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                    6-Digit Verification Code
                </label>
                <div class="flex items-center justify-center gap-2 sm:gap-2.5" @paste="handlePaste($event)">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text"
                               inputmode="numeric"
                               maxlength="1"
                               x-ref="digit{{ $i }}"
                               :disabled="submitting"
                               @input="handleInput({{ $i }}, $event)"
                               @keydown="handleKeydown({{ $i }}, $event)"
                               class="w-11 h-14 sm:w-12 sm:h-16 text-center text-xl sm:text-2xl font-black font-mono rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 text-slate-900 dark:text-white focus:border-blue-600 focus:bg-white dark:focus:bg-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all shadow-inner tabular-nums disabled:opacity-60 disabled:cursor-not-allowed">
                    @endfor
                </div>
                <p class="text-[11px] text-center text-slate-400 dark:text-slate-500 mt-2">
                    Tip: You can paste the full 6 digits directly
                </p>
            </div>

            <button type="submit"
                    :disabled="submitting || digits.join('').length < 6"
                    class="w-full py-3.5 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                <span x-show="!submitting">Verify &amp; Activate Account</span>
                <span x-show="submitting" class="inline-flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Activating Account...</span>
                </span>
            </button>
        </form>

        <!-- Resend Code Bar -->
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 space-y-3">
            <div>
                Didn't receive the email code?
                <form action="{{ route('customer.verify.resend') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            :disabled="cooldown > 0"
                            class="font-bold text-blue-600 dark:text-blue-400 hover:underline disabled:opacity-50 disabled:no-underline cursor-pointer ml-1">
                        <span x-show="cooldown > 0" x-text="'Resend in ' + cooldown + 's'"></span>
                        <span x-show="cooldown === 0">Resend Code</span>
                    </button>
                </form>
            </div>

            <div>
                <a href="{{ route('customer.register') }}" class="text-[11px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 underline">
                    Wrong email address? Register again
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
