@extends('layouts.superadmin')

@section('title', $institution->name . ' - বিস্তারিত')

@section('content')
<div class="space-y-6">
    <!-- Header with Back & Impersonate -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('super-admin.institutions') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> সকল প্রতিষ্ঠানে ফিরে যান
            </a>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <span>{{ $institution->name }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full uppercase font-mono font-bold
                    @if($institution->status === 'active') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                    @else bg-amber-500/20 text-amber-400 border border-amber-500/30
                    @endif">
                    {{ $institution->status }}
                </span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">কোড: {{ $institution->code }} • EIIN: {{ $institution->eiin ?? 'N/A' }} • ধরন: {{ strtoupper($institution->type) }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.institutions.impersonate', $institution->id) }}" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                <span>এই প্রতিষ্ঠানের প্যানেলে প্রবেশ করুন</span>
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800">
            <div class="text-xs text-slate-400 uppercase font-semibold">মোট শিক্ষার্থী</div>
            <div class="text-2xl font-bold font-mono text-white mt-1">{{ $institution->students->count() }} / {{ $institution->student_capacity }}</div>
            <div class="text-[11px] text-slate-500 mt-1">ধারণক্ষমতা ব্যবহার: {{ round(($institution->students->count() / max($institution->student_capacity, 1)) * 100) }}%</div>
        </div>

        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800">
            <div class="text-xs text-slate-400 uppercase font-semibold">শিক্ষক ও স্টাফ</div>
            <div class="text-2xl font-bold font-mono text-teal-400 mt-1">{{ $institution->teachers->count() }} জন</div>
            <div class="text-[11px] text-slate-500 mt-1">নিবন্ধিত শিক্ষক</div>
        </div>

        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800">
            <div class="text-xs text-slate-400 uppercase font-semibold">শ্রেণি ও বিভাগ</div>
            <div class="text-2xl font-bold font-mono text-cyan-400 mt-1">{{ $institution->academicClasses->count() }} টি শ্রেণি</div>
            <div class="text-[11px] text-slate-500 mt-1">সক্রিয় একাডেমিক প্রোগ্রাম</div>
        </div>

        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800">
            <div class="text-xs text-slate-400 uppercase font-semibold">প্যাকেজ স্তর</div>
            <div class="text-2xl font-bold font-mono text-indigo-400 mt-1 uppercase">{{ $institution->subscription_plan }}</div>
            <div class="text-[11px] text-slate-500 mt-1">SaaS বিলিং সক্রিয়</div>
        </div>
    </div>

    <!-- Management Forms Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Institution Config Form -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 lg:col-span-1">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-emerald-400"></i>
                <span>সাবস্ক্রিপশন ও সীমা পরিবর্তন</span>
            </h3>

            <form action="{{ route('super-admin.institutions.plan', $institution->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 font-semibold mb-1">প্যাকেজ নির্বাচন</label>
                    <select name="subscription_plan" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none">
                        <option value="starter" {{ $institution->subscription_plan === 'starter' ? 'selected' : '' }}>Starter Plan (২,৫০০ ৳/মাস)</option>
                        <option value="standard" {{ $institution->subscription_plan === 'standard' ? 'selected' : '' }}>Standard Plan (৫,০০০ ৳/মাস)</option>
                        <option value="premium" {{ $institution->subscription_plan === 'premium' ? 'selected' : '' }}>Premium Plan (১০,০০০ ৳/মাস)</option>
                        <option value="enterprise" {{ $institution->subscription_plan === 'enterprise' ? 'selected' : '' }}>Enterprise Plan (২৫,০০০ ৳/মাস)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 font-semibold mb-1">সর্বোচ্চ শিক্ষার্থী ধারণক্ষমতা</label>
                    <input type="number" name="student_capacity" value="{{ $institution->student_capacity }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none">
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition">
                    আপডেট সংরক্ষণ করুন
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800 text-xs text-slate-400 space-y-2">
                <div><strong>মোবাইল:</strong> {{ $institution->phone }}</div>
                <div><strong>ইমেইল:</strong> {{ $institution->email }}</div>
                <div><strong>ঠিকানা:</strong> {{ $institution->address ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Academic Classes & Sections -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 lg:col-span-2">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-graduation-cap text-emerald-400"></i>
                <span>একাডেমিক শ্রেণি ও শাখাসমূহ</span>
            </h3>

            <div class="space-y-3">
                @forelse($institution->academicClasses as $cls)
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm text-white">{{ $cls->name }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            শাখা: 
                            @foreach($cls->sections as $sec)
                                <span class="inline-block bg-slate-800 px-2 py-0.5 rounded text-[11px] text-slate-300 mr-1">{{ $sec->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <span class="text-xs font-mono text-emerald-400 font-semibold">{{ $cls->students->count() }} শিক্ষার্থী</span>
                </div>
                @empty
                <div class="text-center py-8 text-xs text-slate-500">কোনো শ্রেণি তৈরি করা হয়নি।</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
