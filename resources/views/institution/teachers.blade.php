@extends('layouts.institution')

@section('page_title', 'শিক্ষক ও স্টাফ ব্যবস্থাপনা')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">শিক্ষক ও স্টাফ তালিকা</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $institution->name }} এর অনুষদ সদস্য ও দায়িত্বপ্রাপ্ত শিক্ষকগণ</p>
        </div>

        <button @click="addModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition">
            <i class="fa-solid fa-user-plus"></i>
            <span>নতুন শিক্ষক যুক্ত করুন</span>
        </button>
    </div>

    <!-- Teachers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">শিক্ষকের নাম ও আইডি</th>
                        <th class="px-6 py-3.5">পদবী (Designation)</th>
                        <th class="px-6 py-3.5">বিভাগ (Department)</th>
                        <th class="px-6 py-3.5">মোবাইল ও ইমেইল</th>
                        <th class="px-6 py-3.5">শিক্ষাগত যোগ্যতা</th>
                        <th class="px-6 py-3.5 text-right">স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $t)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-sm text-slate-900">{{ $t->name }}</div>
                            <div class="text-[11px] font-mono text-emerald-600 font-semibold mt-0.5">{{ $t->employee_id }}</div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">{{ $t->designation }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium text-[11px]">
                                {{ $t->department }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-slate-800">{{ $t->phone }}</div>
                            <div class="text-[11px] text-slate-400">{{ $t->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $t->qualification ?? 'উল্লেখ নেই' }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800">
                                সক্রিয় (Active)
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">কোনো শিক্ষক পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Add Teacher -->
    <div x-show="addModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative" @click.away="addModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-chalkboard-user text-emerald-600"></i>
                    <span>নতুন শিক্ষক নিবন্ধন ফরম</span>
                </h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('institution.teachers.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-slate-700 font-semibold mb-1">শিক্ষকের পূর্ণ নাম *</label>
                        <input type="text" name="name" required placeholder="যেমন: ড. মুহাম্মদ শফিকুর রহমান" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">পদবী (Designation) *</label>
                        <input type="text" name="designation" required placeholder="যেমন: সিনিয়র সহকারী শিক্ষক / মুহাদ্দিস" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">বিভাগ (Department) *</label>
                        <input type="text" name="department" required placeholder="যেমন: গণিত / আরবি ও কুরআন / ইংরেজি" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">মোবাইল নম্বর *</label>
                        <input type="text" name="phone" required placeholder="017XXXXXXXX" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">ইমেইল *</label>
                        <input type="email" name="email" required placeholder="teacher@institution.edu.bd" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-slate-700 font-semibold mb-1">শিক্ষাগত যোগ্যতা</label>
                        <input type="text" name="qualification" placeholder="যেমন: M.Sc in Physics (DU), B.Ed" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="addModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">বাতিল</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
