@extends('layouts.institution')

@section('page_title', 'শিক্ষাবর্ষ ও এনরোলমেন্ট ব্যবস্থাপনা')

@section('content')
<div class="space-y-6" x-data="{ yearModal: false, enrollModal: false, activeYearId: '{{ $currentYear?->id }}', activeYearName: '{{ $currentYear?->name }}' }">
    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
                <i class="fa-solid fa-calendar-days text-indigo-600"></i>
                <span>শিক্ষাবর্ষ ও এনরোলমেন্ট ব্যবস্থাপনা</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">পুরোনো শিক্ষাবর্ষের তথ্য অক্ষুণ্ণ রেখে নতুন শিক্ষাবর্ষ চালু ও নতুন এনরোলমেন্ট পরিচালনা করুন</p>
        </div>

        <div class="flex items-center gap-2.5">
            <button @click="yearModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition">
                <i class="fa-solid fa-calendar-plus"></i>
                <span>+ নতুন শিক্ষাবর্ষ চালু করুন</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Academic Years Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($years as $yr)
        <div class="bg-white p-5 rounded-2xl border {{ $yr->is_current ? 'border-indigo-500 shadow-md ring-2 ring-indigo-500/10' : 'border-slate-200 shadow-xs' }} relative flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl {{ $yr->is_current ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center font-bold text-base">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                                <span>শিক্ষাবর্ষ {{ $yr->name }}</span>
                                @if($yr->is_current)
                                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold font-mono">বর্তমান (Active)</span>
                                @endif
                            </h3>
                            <p class="text-[11px] text-slate-500">{{ date('d M Y', strtotime($yr->start_date)) }} - {{ date('d M Y', strtotime($yr->end_date)) }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 py-3 my-2 bg-slate-50 rounded-xl text-center">
                    <div>
                        <div class="text-[10px] text-slate-500 font-semibold">মোট এনরোলমেন্ট</div>
                        <div class="text-base font-extrabold text-slate-900 font-mono mt-0.5">{{ $yr->enrollments_count }} জন</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 font-semibold">স্ট্যাটাস</div>
                        <div class="text-xs font-bold font-mono mt-1 uppercase {{ $yr->status === 'active' ? 'text-emerald-600' : 'text-slate-500' }}">
                            {{ $yr->status }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                @if(!$yr->is_current)
                <form action="{{ route('institution.academic-years.activate', $yr->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                        বর্তমান সক্রিয় করুন
                    </button>
                </form>
                @else
                <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> চলমান বছর
                </span>
                @endif

                <button @click="activeYearId = '{{ $yr->id }}'; activeYearName = '{{ $yr->name }}'; enrollModal = true" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    + এনরোল করুন
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-3 p-8 bg-white rounded-2xl border border-slate-200 text-center text-slate-500 text-xs">
            এখনো কোনো শিক্ষাবর্ষ তৈরি করা হয়নি। নতুন শিক্ষাবর্ষ যুক্ত করুন।
        </div>
        @endforelse
    </div>

    <!-- Current Year Enrollments List -->
    @if($currentYear)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-user-graduate text-indigo-600"></i>
                <span>শিক্ষাবর্ষ {{ $currentYear->name }} এর শিক্ষার্থী তালিকা (Enrollments):</span>
            </h2>
            <span class="text-xs text-slate-500">মোট {{ $currentYear->enrollments_count }} জন শিক্ষার্থী</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="p-3">রোল</th>
                        <th class="p-3">শিক্ষার্থীর নাম</th>
                        <th class="p-3">ভর্তি নম্বর</th>
                        <th class="p-3">শ্রেণি</th>
                        <th class="p-3">শাখা</th>
                        <th class="p-3">এনরোলমেন্ট তারিখ</th>
                        <th class="p-3">স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($enrollments as $enr)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 font-mono font-bold text-slate-900">#{{ $enr->roll_number ?? $enr->student?->roll_number }}</td>
                        <td class="p-3 font-semibold text-slate-900">{{ $enr->student?->name }}</td>
                        <td class="p-3 font-mono text-slate-600">{{ $enr->student?->admission_number }}</td>
                        <td class="p-3 font-semibold text-indigo-600">{{ $enr->academicClass?->name }}</td>
                        <td class="p-3 text-slate-600">{{ $enr->section?->name ?? 'সাধারণ শাখা' }}</td>
                        <td class="p-3 text-slate-500">{{ $enr->enrollment_date ? date('d M Y', strtotime($enr->enrollment_date)) : '—' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800">
                                {{ $enr->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-400">এই শিক্ষাবর্ষে কোনো শিক্ষার্থী এনরোল করা হয়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- MODAL 1: Create Academic Year -->
    <div x-show="yearModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3" style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative my-auto" @click.away="yearModal = false">
            <h3 class="text-base font-bold text-slate-900 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-indigo-600"></i>
                <span>নতুন শিক্ষাবর্ষ চালু করুন</span>
            </h3>
            <p class="text-xs text-slate-500 mb-4">পূর্ববর্তী বছরের ডেটা মুছে যাবে না, সম্পূর্ণ ইতিহাস সংরক্ষিত থাকবে।</p>

            <form action="{{ route('institution.academic-years.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-bold mb-1">শিক্ষাবর্ষের নাম (Year Name):</label>
                    <input type="text" name="name" required placeholder="যেমন: 2027 অথবা 2026-2027" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">শুরুর তারিখ:</label>
                        <input type="date" name="start_date" required value="{{ date('Y') }}-01-01" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">শেষের তারিখ:</label>
                        <input type="date" name="end_date" required value="{{ date('Y') }}-12-31" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_current" name="is_current" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="is_current" class="text-slate-700 font-semibold text-xs">এই বছরটিকে বর্তমান সক্রিয় শিক্ষাবর্ষ হিসেবে নির্ধারণ করুন</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                    <button type="button" @click="yearModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">বাতিল</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
