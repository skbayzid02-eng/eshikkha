@extends('layouts.superadmin')

@section('title', 'সাবস্ক্রিপশন ও রাজস্ব')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">প্যাকেজ ও রাজস্ব ওভারভিউ</h1>
        <p class="text-xs text-slate-400 mt-1">ই-শিক্ষা প্ল্যাটফর্মের SaaS সাবস্ক্রিপশন ও প্রতিষ্ঠান ভিত্তিক বিলিং বিবরণ</p>
    </div>

    <!-- Pricing Tiers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase">Starter Plan</div>
            <div class="text-2xl font-extrabold text-white font-mono mt-1">৳ ২,৫০০ <span class="text-xs text-slate-500 font-normal">/মাস</span></div>
            <p class="text-xs text-slate-400 mt-2">ছোট স্কুল বা মাদ্রাসার জন্য (সর্বোচ্চ ৫০০ শিক্ষার্থী)</p>
        </div>

        <div class="bg-slate-950 p-5 rounded-2xl border border-emerald-500/50 shadow-lg shadow-emerald-500/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-400 uppercase">Standard Plan</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-semibold">জনপ্রিয়</span>
            </div>
            <div class="text-2xl font-extrabold text-white font-mono mt-1">৳ ৫,০০০ <span class="text-xs text-slate-500 font-normal">/মাস</span></div>
            <p class="text-xs text-slate-400 mt-2">মাঝারি স্কুল ও কলেজের জন্য (সর্বোচ্চ ২০০০ শিক্ষার্থী)</p>
        </div>

        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase">Premium Plan</div>
            <div class="text-2xl font-extrabold text-white font-mono mt-1">৳ ১০,০০০ <span class="text-xs text-slate-500 font-normal">/মাস</span></div>
            <p class="text-xs text-slate-400 mt-2">বড় কামিল মাদ্রাসা ও নামকরা কলেজ (সর্বোচ্চ ৪৫০০ শিক্ষার্থী)</p>
        </div>

        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase">Enterprise Plan</div>
            <div class="text-2xl font-extrabold text-white font-mono mt-1">৳ ২৫,০০০ <span class="text-xs text-slate-500 font-normal">/মাস</span></div>
            <p class="text-xs text-slate-400 mt-2">বিশ্ববিদ্যালয় বা মাল্টি-ক্যাম্পাস চেইন (আনলিমিটেড শিক্ষার্থী)</p>
        </div>
    </div>

    <!-- Active Subscriptions Table -->
    <div class="bg-slate-950 rounded-2xl border border-slate-800 shadow-lg overflow-hidden">
        <div class="p-6 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">প্রতিষ্ঠানভিত্তিক সাবস্ক্রিপশন তালিকা</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900 text-slate-400 uppercase tracking-wider text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">প্রতিষ্ঠান</th>
                        <th class="px-6 py-3.5">প্যাকেজ</th>
                        <th class="px-6 py-3.5">শিক্ষার্থী সংখ্যা</th>
                        <th class="px-6 py-3.5">মাসিক চার্জ</th>
                        <th class="px-6 py-3.5">পেমেন্ট গেটওয়ে</th>
                        <th class="px-6 py-3.5 text-right">স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($institutions as $inst)
                    <tr class="hover:bg-slate-900/60 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $inst->name }}</td>
                        <td class="px-6 py-4 uppercase font-semibold text-emerald-400">{{ $inst->subscription_plan }}</td>
                        <td class="px-6 py-4">{{ $inst->students_count }} জন</td>
                        <td class="px-6 py-4 font-mono font-bold text-white">
                            @if($inst->subscription_plan === 'enterprise') ৳ ২৫,০০০
                            @elseif($inst->subscription_plan === 'premium') ৳ ১০,০০০
                            @elseif($inst->subscription_plan === 'standard') ৳ ৫,০০০
                            @else ৳ ২,৫০০
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-400">bKash Merchant Auto-Debit</td>
                        <td class="px-6 py-4 text-right">
                            <span class="px-2.5 py-1 rounded-full text-[11px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold">
                                পেইড (Paid)
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
