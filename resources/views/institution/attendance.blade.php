@extends('layouts.institution')

@section('page_title', 'দৈনিক শিক্ষার্থী হাজিরা ও RFID')

@section('content')
<div class="space-y-6" x-data="{
    searchStudent: '',
    selectedClass: '{{ $selectedClassId ?? '' }}',
    classesData: @js($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])])),

    getAvailableSections() {
        if (!this.selectedClass) return [];
        const found = this.classesData.find(c => c.id == this.selectedClass);
        return found ? found.sections : [];
    },

    markAll(status) {
        document.querySelectorAll('input[type=radio][value=' + status + ']').forEach(el => {
            el.checked = true;
            el.dispatchEvent(new Event('change'));
        });
    }
}">
    <!-- Header Title -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white flex items-center justify-center text-lg shadow-md shadow-emerald-500/20">
                        <i class="fa-solid fa-clipboard-user"></i>
                    </span>
                    <span>দৈনিক শিক্ষার্থী হাজিরা গ্রহণ ও ফিল্টারিং</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 pl-13">
                    শ্রেণি ও শাখা অনুযায়ী ডাটাবেজ থেকে শিক্ষার্থী ও তাদের হাজিরা স্বয়ংক্রিয়ভাবে ফিল্টার করুন এবং উপস্থিতি সংরক্ষণ করুন।
                </p>
            </div>

            <!-- Quick Date Badge -->
            <div class="flex items-center gap-2">
                <span class="px-4 py-2 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-2xs">
                    <i class="fa-solid fa-calendar-day text-emerald-600"></i>
                    <span>তারিখ: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F, Y') }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Form (Class & Section & Date) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-filter text-emerald-600"></i>
                <span>ফিল্টার অপশন (শ্রেণি ও শাখা নির্বাচন করুন):</span>
            </h3>
        </div>

        <form method="GET" action="{{ route('institution.attendance') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <!-- Date Filter -->
            <div>
                <label class="block text-slate-600 font-bold mb-1.5 flex items-center gap-1.5">
                    <i class="fa-regular fa-calendar text-emerald-600"></i>
                    <span>হাজিরার তারিখ:</span>
                </label>
                <input type="date" name="date" value="{{ $selectedDate }}" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3.5 py-2.5 text-slate-800 font-semibold focus:outline-none focus:border-emerald-500 focus:bg-white transition">
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-slate-600 font-bold mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-graduation-cap text-emerald-600"></i>
                    <span>শ্রেণি (Class):</span>
                </label>
                <select name="class_id" x-model="selectedClass" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3.5 py-2.5 text-slate-800 font-semibold focus:outline-none focus:border-emerald-500 focus:bg-white transition">
                    @forelse($classes as $c)
                    <option value="{{ $c->id }}" {{ ($selectedClassId ?? null) == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                    @empty
                    <option value="">কোনো শ্রেণি পাওয়া যায়নি</option>
                    @endforelse
                </select>
            </div>

            <!-- Section Filter -->
            <div>
                <label class="block text-slate-600 font-bold mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-users-rectangle text-emerald-600"></i>
                    <span>শাখা (Section):</span>
                </label>
                <select name="section_id" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3.5 py-2.5 text-slate-800 font-semibold focus:outline-none focus:border-emerald-500 focus:bg-white transition">
                    <option value="">সকল শাখা (All Sections)</option>
                    
                    @foreach($classes as $c)
                        @if($c->sections && $c->sections->isNotEmpty())
                            @foreach($c->sections as $sec)
                            <option value="{{ $sec->id }}" 
                                    x-show="!selectedClass || selectedClass == {{ $c->id }}"
                                    {{ ($selectedSectionId ?? null) == $sec->id ? 'selected' : '' }}>
                                {{ $c->name }} - {{ $sec->name }}
                            </option>
                            @endforeach
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-2xl transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span>হাজিরা শিট আনুন</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Form -->
    <form action="{{ route('institution.attendance.store') }}" method="POST">
        @csrf
        <input type="hidden" name="date" value="{{ $selectedDate }}">

        <!-- Live Stats & Quick Actions Bar -->
        @php
            $totalCount = $students->count();
            $presentCount = collect($attendanceRecords)->filter(fn($st) => $st === 'present')->count();
            $absentCount = collect($attendanceRecords)->filter(fn($st) => $st === 'absent')->count();
            $lateCount = collect($attendanceRecords)->filter(fn($st) => $st === 'late')->count();
            $rate = $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0;
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">মোট শিক্ষার্থী</div>
                    <div class="text-lg font-extrabold text-slate-900 leading-tight">{{ $totalCount }} জন</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">উপস্থিত (Present)</div>
                    <div class="text-lg font-extrabold text-emerald-700 leading-tight">{{ $presentCount }} জন</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">অনুপস্থিত (Absent)</div>
                    <div class="text-lg font-extrabold text-rose-700 leading-tight">{{ $absentCount }} জন</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">উপস্থিতির হার</div>
                    <div class="text-lg font-extrabold text-amber-800 leading-tight">{{ $rate }}%</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <!-- Top Controls in Sheet: SMS Notice & Quick Mark All -->
            <div class="p-4 bg-emerald-50/70 border-b border-emerald-100 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-emerald-950 font-semibold">
                    <i class="fa-solid fa-bell text-emerald-600"></i>
                    <span>অনুপস্থিত শিক্ষার্থীদের ক্ষেত্রে স্বয়ংক্রিয়ভাবে অভিভাবকের ফোনে SMS নোটিফিকেশন প্রেরিত হবে।</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-slate-500 font-semibold">এক ক্লিকে মার্ক করুন:</span>
                    <button type="button" @click="markAll('present')" class="px-3 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 text-emerald-800 font-bold rounded-xl transition shadow-2xs">
                        সবাই উপস্থিত
                    </button>
                    <button type="button" @click="markAll('absent')" class="px-3 py-1 bg-white hover:bg-rose-100 border border-rose-300 text-rose-800 font-bold rounded-xl transition shadow-2xs">
                        সবাই অনুপস্থিত
                    </button>
                    <button type="button" @click="markAll('late')" class="px-3 py-1 bg-white hover:bg-amber-100 border border-amber-300 text-amber-800 font-bold rounded-xl transition shadow-2xs">
                        সবাই দেরি
                    </button>
                </div>
            </div>

            <!-- Student Search Bar in Attendance Sheet -->
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" x-model="searchStudent" 
                           placeholder="এই শিটের শিক্ষার্থী খুঁজুন (রোল অথবা নাম দিয়ে)..." 
                           class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5 font-bold">রোল</th>
                            <th class="px-6 py-3.5 font-bold">শিক্ষার্থীর নাম</th>
                            <th class="px-6 py-3.5 font-bold">শ্রেণি ও শাখা</th>
                            <th class="px-6 py-3.5 font-bold">ভর্তি আইডি</th>
                            <th class="px-6 py-3.5 font-bold">অভিভাবকের মোবাইল</th>
                            <th class="px-6 py-3.5 text-center font-bold">উপস্থিতি স্থিতি (Attendance Status)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($students as $st)
                        @php
                            $currentStatus = $attendanceRecords[$st->id] ?? 'present';
                            $studentName = $st->name ?? 'অজ্ঞাত শিক্ষার্থী';
                            $roll = $st->roll_number ?? '—';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition" 
                            x-show="!searchStudent || '{{ strtolower($studentName) }}'.includes(searchStudent.toLowerCase()) || '{{ $roll }}'.includes(searchStudent)">
                            <td class="px-6 py-4 font-bold text-slate-900 font-mono text-sm">#{{ $roll }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $studentName }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">আইডি: #{{ $st->id }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-700">
                                <div>{{ $st->academicClass?->name ?? 'শ্রেণি নির্ধারিত নয়' }}</div>
                                <div class="text-[11px] text-slate-500 font-normal">{{ $st->section?->name ?? 'সাধারণ শাখা' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-500">{{ $st->admission_number ?? '—' }}</td>
                            <td class="px-6 py-4 font-mono text-slate-600">{{ $st->guardian_phone ?? 'প্রযোজ্য নয়' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 sm:gap-3">
                                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition select-none {{ $currentStatus === 'present' ? 'bg-emerald-50 border-emerald-400 text-emerald-800 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="attendances[{{ $st->id }}]" value="present" {{ $currentStatus === 'present' ? 'checked' : '' }} class="text-emerald-600 focus:ring-0">
                                        <span>উপস্থিত</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition select-none {{ $currentStatus === 'absent' ? 'bg-rose-50 border-rose-400 text-rose-800 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="attendances[{{ $st->id }}]" value="absent" {{ $currentStatus === 'absent' ? 'checked' : '' }} class="text-rose-600 focus:ring-0">
                                        <span>অনুপস্থিত</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition select-none {{ $currentStatus === 'late' ? 'bg-amber-50 border-amber-400 text-amber-800 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="attendances[{{ $st->id }}]" value="late" {{ $currentStatus === 'late' ? 'checked' : '' }} class="text-amber-600 focus:ring-0">
                                        <span>দেরি</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">
                                    <i class="fa-regular fa-folder-open"></i>
                                </div>
                                <div class="font-bold text-slate-700">এই শ্রেণি বা শাখায় কোনো শিক্ষার্থী পাওয়া যায়নি।</div>
                                <div class="text-xs text-slate-500 mt-1">অন্য কোনো শ্রেণি বা শাখা নির্বাচন করে আবার চেষ্টা করুন।</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->isNotEmpty())
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>হাজিরা সংরক্ষণ ও SMS প্রেরণ নিশ্চিত করুন</span>
                </button>
            </div>
            @endif
        </div>
    </form>
</div>
@endsection
