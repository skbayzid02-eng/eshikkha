@extends('layouts.institution')

@section('page_title', 'ক্যাম্পাস অ্যাডমিন ড্যাশবোর্ড')

@section('content')
<div class="space-y-6">
    <!-- Top Welcome Card -->
    <div class="bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur text-xs font-semibold mb-3 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>সক্রিয় ই-শিক্ষা স্মার্ট ক্যাম্পাস</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold">{{ $institution->name }}</h1>
                <p class="text-emerald-100/80 text-xs sm:text-sm mt-1 max-w-2xl">
                    কোড: <span class="font-mono font-bold">{{ $institution->code }}</span> • 
                    EIIN: <span class="font-mono font-bold">{{ $institution->eiin ?? 'N/A' }}</span> • 
                    ক্যাম্পাস লোকেশন: {{ $institution->district }}, {{ $institution->division }} বিভাগ
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('institution.classes') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur border border-white/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-shapes"></i>
                    <span>শ্রেণি ও শাখা</span>
                </a>
                <a href="{{ route('institution.students') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-emerald-800 font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>নতুন শিক্ষার্থী ভর্তি</span>
                </a>
                <a href="{{ route('institution.attendance') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>হাজিরা গ্রহণ</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Metric 1: Total Students -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">মোট শিক্ষার্থী</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-graduate"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 font-mono">{{ $totalStudents }}</div>
            <div class="text-xs text-slate-500 mt-2 flex items-center gap-1.5">
                <i class="fa-solid fa-layer-group text-slate-400"></i>
                <span>{{ $totalClasses }} টি সক্রিয় শ্রেণি</span>
            </div>
        </div>

        <!-- Metric 2: Today's Attendance -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">আজকের উপস্থিতি হার</span>
                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 font-mono">{{ $attendanceRate }}%</div>
            <div class="text-xs mt-2 flex items-center gap-2">
                <span class="text-emerald-600 font-semibold">{{ $presentToday }} উপস্থিত</span>
                <span class="text-slate-300">•</span>
                <span class="text-rose-600 font-semibold">{{ $absentToday }} অনুপস্থিত</span>
            </div>
        </div>

        <!-- Metric 3: Fees Collected -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">আদায়কৃত ফি</span>
                <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 font-mono">৳ {{ number_format($totalFeeCollected) }}</div>
            <div class="text-xs text-slate-500 mt-2">
                <span>bKash ও ব্যাংক কালেকশন</span>
            </div>
        </div>

        <!-- Metric 4: Faculty & Staff -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">শিক্ষক ও স্টাফ</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 font-mono">{{ $totalTeachers }} জন</div>
            <div class="text-xs text-emerald-600 font-semibold mt-2 flex items-center gap-1">
                <i class="fa-solid fa-mobile-screen"></i>
                <span>শিক্ষক অ্যাপ অ্যাক্টিভ</span>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Action Grid -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs lg:col-span-1">
            <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-bolt text-emerald-600"></i>
                <span>দ্রুত সেবা (Quick Actions)</span>
            </h3>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <a href="{{ route('institution.attendance') }}" class="p-3.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-100 flex flex-col items-center justify-center text-center transition">
                    <i class="fa-solid fa-clipboard-check text-xl mb-1.5"></i>
                    <span class="font-bold">হাজিরা গ্রহণ</span>
                </a>

                <a href="{{ route('institution.students') }}" class="p-3.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-100 flex flex-col items-center justify-center text-center transition">
                    <i class="fa-solid fa-user-plus text-xl mb-1.5"></i>
                    <span class="font-bold">ভর্তি কার্যক্রম</span>
                </a>

                <a href="{{ route('institution.fees') }}" class="p-3.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-100 flex flex-col items-center justify-center text-center transition">
                    <i class="fa-solid fa-receipt text-xl mb-1.5"></i>
                    <span class="font-bold">ফি রসিদ ও ইনভয়েস</span>
                </a>

                <a href="{{ route('institution.notices') }}" class="p-3.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-100 flex flex-col items-center justify-center text-center transition">
                    <i class="fa-solid fa-comment-sms text-xl mb-1.5"></i>
                    <span class="font-bold">SMS অ্যালার্ট</span>
                </a>
            </div>

            <!-- Campus Support Info -->
            <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                <div class="font-semibold text-slate-900 mb-1">সুপার অ্যাডমিন হেল্পডেস্ক</div>
                <div>ই-শিক্ষা সাপোর্ট হটলাইন: ০৯৬৩৮-৭৭৭০০০</div>
                <div class="text-[11px] text-slate-400 mt-1">SaaS বিলিং চক্র: মাসিক স্বয়ংক্রিয় bKash ডেবিট</div>
            </div>
        </div>

        <!-- Recent Students -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users text-emerald-600"></i>
                    <span>ভর্তি হওয়া সর্বশেষ শিক্ষার্থী</span>
                </h3>
                <a href="{{ route('institution.students') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">সকল শিক্ষার্থী <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentStudents as $student)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center">
                            {{ $student->roll_number }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $student->name }}</div>
                            <div class="text-slate-500">
                                {{ $student->academicClass?->name }} • {{ $student->section?->name }}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-mono text-slate-600">{{ $student->admission_number }}</div>
                        <div class="text-[11px] text-slate-400">অভিভাবক: {{ $student->guardian_phone }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">কোনো শিক্ষার্থী পাওয়া যায়নি।</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
