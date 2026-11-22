@extends('layouts.app')

@section('title', 'লগইন | ই-শিক্ষা প্ল্যাটফর্ম')

@section('content')
<div class="min-h-screen bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-emerald-600/10 blur-3xl"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-teal-600/10 blur-3xl"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center z-10">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-graduation-cap text-2xl"></i>
            </div>
            <span class="font-extrabold text-2xl text-white">ই-শিক্ষা</span>
        </a>
        <h2 class="text-2xl font-bold text-white tracking-tight">আপনার অ্যাকাউন্টে প্রবেশ করুন</h2>
        <p class="mt-1 text-xs text-slate-400">সুপার অ্যাডমিন, প্রতিষ্ঠান প্রশাসন ও শিক্ষক পোর্টাল</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg z-10 px-4 sm:px-0">
        <!-- Quick Demo Switcher Cards -->
        <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-4 mb-6 shadow-xl backdrop-blur">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-bolt"></i> এক ক্লিকে ডেমো টেস্ট লগইন:
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <a href="{{ route('quick-login', 'super_admin') }}" class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/60 text-left transition flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-shield-halved"></i></span>
                    <div class="truncate">
                        <div class="font-bold text-white">Super Admin</div>
                        <div class="text-[10px] text-slate-400">সকল প্রতিষ্ঠান নিয়ন্ত্রণ</div>
                    </div>
                </a>
                <a href="{{ route('quick-login', ['role' => 'institution_admin']) }}" class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700/60 text-left transition flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-school"></i></span>
                    <div class="truncate">
                        <div class="font-bold text-white">প্রতিষ্ঠান অ্যাডমিন</div>
                        <div class="text-[10px] text-slate-400">স্কুল/মাদ্রাসা প্যানেল</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="bg-slate-950 border border-slate-800 py-8 px-6 shadow-2xl rounded-3xl sm:px-10">
            @if($errors->any())
            <div class="mb-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs p-3 rounded-xl">
                {{ $errors->first() }}
            </div>
            @endif

            <form class="space-y-5" action="{{ route('login.post') }}" method="POST">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        ইমেইল বা ইউজার আইডি
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email', 'superadmin@eshikkha.com') }}" required autocomplete="email" class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder-slate-500">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        পাসওয়ার্ড
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" value="password" required autocomplete="current-password" class="w-full bg-slate-900 border border-slate-700 text-white text-sm rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 placeholder-slate-500">
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1.5">ডিফল্ট পাসওয়ার্ড: <span class="font-mono text-emerald-400">password</span></div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-emerald-600 focus:ring-0">
                        <span>মনে রাখুন</span>
                    </label>
                    <a href="#" class="text-emerald-400 hover:text-emerald-300">পাসওয়ার্ড ভুলে গেছেন?</a>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-600/30 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition focus:outline-none">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>লগইন করুন</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800 text-center">
                <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> মূল ওয়েবসাইটে ফিরে যান
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
