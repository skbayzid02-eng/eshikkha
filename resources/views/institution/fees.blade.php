@extends('layouts.institution')

@section('page_title', 'ফি আদায় ও ডিজিটাল হিসাবরক্ষণ')

@section('content')
<div class="space-y-6" x-data="{ addInvoiceModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">ফি আদায় ও ইনভয়েস ম্যানেজমেন্ট</h1>
            <p class="text-xs text-slate-500 mt-1">শিক্ষার্থীদের মাসিক বেতন, ভর্তি ফি, পরীক্ষার ফি ও bKash ট্রানজ্যাকশন রিপোর্ট</p>
        </div>

        <button @click="addInvoiceModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition">
            <i class="fa-solid fa-plus"></i>
            <span>নতুন ইনভয়েস তৈরি</span>
        </button>
    </div>

    <!-- Fee Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-400 font-semibold uppercase">মোট আদায়কৃত ফি (Collected)</div>
                <div class="text-2xl font-bold font-mono text-emerald-600 mt-1">৳ {{ number_format($totalCollected, 2) }}</div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-400 font-semibold uppercase">মোট বকেয়া ফি (Pending Dues)</div>
                <div class="text-2xl font-bold font-mono text-rose-600 mt-1">৳ {{ number_format($totalUnpaid, 2) }}</div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">ইনভয়েস নং ও শিরোনাম</th>
                        <th class="px-6 py-3.5">শিক্ষার্থী ও শ্রেণি</th>
                        <th class="px-6 py-3.5">টাকার পরিমাণ</th>
                        <th class="px-6 py-3.5">শেষ সময় (Due Date)</th>
                        <th class="px-6 py-3.5">পেমেন্ট মেথড ও TrxID</th>
                        <th class="px-6 py-3.5">স্ট্যাটাস</th>
                        <th class="px-6 py-3.5 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($fees as $fee)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-sm text-slate-900">{{ $fee->title }}</div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $fee->invoice_no }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $fee->student?->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $fee->student?->academicClass?->name }} (রোল: #{{ $fee->student?->roll_number }})</div>
                        </td>
                        <td class="px-6 py-4 font-bold font-mono text-slate-900 text-sm">৳ {{ number_format($fee->amount, 2) }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $fee->due_date->format('d M, Y') }}</td>
                        <td class="px-6 py-4">
                            @if($fee->status === 'paid')
                                <div class="font-semibold text-emerald-600">{{ $fee->payment_method ?? 'bKash' }}</div>
                                <div class="text-[10px] font-mono text-slate-400">Trx: {{ $fee->transaction_id }}</div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($fee->status === 'paid')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                    পরিশোধিত (Paid)
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                    বকেয়া (Unpaid)
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($fee->status === 'unpaid')
                            <form action="{{ route('institution.fees.pay', $fee->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="payment_method" value="bKash">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-bold text-[11px] flex items-center gap-1 shadow-xs transition">
                                    <i class="fa-solid fa-mobile-screen"></i>
                                    <span>bKash গ্রহণ</span>
                                </button>
                            </form>
                            @else
                            <button class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                                <i class="fa-solid fa-print mr-1"></i> রসিদ
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">কোনো ইনভয়েস পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Add Invoice -->
    <div x-show="addInvoiceModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative" @click.away="addInvoiceModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <span>নতুন ফি ইনভয়েস তৈরি</span>
                </h3>
                <button @click="addInvoiceModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('institution.fees.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-semibold mb-1">শিক্ষার্থী নির্বাচন করুন *</label>
                    <select name="student_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                        @foreach($students as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} (রোল: {{ $st->roll_number }} - {{ $st->admission_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-1">ফির শিরোনাম *</label>
                    <input type="text" name="title" required placeholder="যেমন: মাসিক বেতন - নভেম্বর ২০২৬" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-1">টাকার পরিমাণ (BDT) *</label>
                    <input type="number" step="0.01" name="amount" required placeholder="2500" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-1">পরিশোধের শেষ তারিখ *</label>
                    <input type="date" name="due_date" value="{{ \Carbon\Carbon::now()->addDays(10)->toDateString() }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="addInvoiceModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">বাতিল</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">ইনভয়েস তৈরি করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
