@extends('layouts.superadmin')

@section('title', 'সকল শিক্ষা প্রতিষ্ঠান')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">সকল শিক্ষা প্রতিষ্ঠান তালিকা</h1>
            <p class="text-xs text-slate-400 mt-1">সারাদেশের স্কুল, কলেজ, মাদ্রাসা ও বিশ্ববিদ্যালয় পর্যবেক্ষণ ও কনফিগারেশন</p>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 shadow-md">
        <form method="GET" action="{{ route('super-admin.institutions') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-400 uppercase font-semibold mb-1">অনুসন্ধান (নাম / EIIN / কোড)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="সার্চ করুন..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-slate-400 uppercase font-semibold mb-1">ধরন (Type)</label>
                <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    <option value="all">সকল ধরন (১৯টি প্রতিষ্ঠান)</option>
                    @if(isset($supportedTypes))
                        @foreach($supportedTypes as $typeKey => $t)
                        <option value="{{ $typeKey }}" {{ request('type') === $typeKey ? 'selected' : '' }}>{{ $t['name_bn'] }}</option>
                        @endforeach
                    @endif
                    <option value="school" {{ request('type') === 'school' ? 'selected' : '' }}>উচ্চ বিদ্যালয় (সাধারণ)</option>
                    <option value="college" {{ request('type') === 'college' ? 'selected' : '' }}>কলেজ (সাধারণ)</option>
                    <option value="madrasah" {{ request('type') === 'madrasah' ? 'selected' : '' }}>মাদ্রাসা (সাধারণ)</option>
                    <option value="university" {{ request('type') === 'university' ? 'selected' : '' }}>বিশ্ববিদ্যালয় (সাধারণ)</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-400 uppercase font-semibold mb-1">বিভাগ (Division)</label>
                <select name="division" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    <option value="all">সকল বিভাগ</option>
                    @foreach(['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Barishal', 'Sylhet', 'Rangpur', 'Mymensingh'] as $div)
                    <option value="{{ $div }}" {{ request('division') === $div ? 'selected' : '' }}>{{ $div }} বিভাগ</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-xl transition">
                    ফিল্টার করুন
                </button>
                <a href="{{ route('super-admin.institutions') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-3 py-2 rounded-xl transition">
                    রিসেট
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-slate-950 rounded-2xl border border-slate-800 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900 text-slate-400 uppercase tracking-wider text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">প্রতিষ্ঠান ও EIIN</th>
                        <th class="px-6 py-3.5">ধরন ও ক্যাম্পাস</th>
                        <th class="px-6 py-3.5">শিক্ষার্থী / শিক্ষক</th>
                        <th class="px-6 py-3.5">প্যাকেজ</th>
                        <th class="px-6 py-3.5">স্ট্যাটাস পরিবর্তন</th>
                        <th class="px-6 py-3.5 text-right">ম্যানেজমেন্ট</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($institutions as $inst)
                    <tr class="hover:bg-slate-900/60 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-sm text-white">{{ $inst->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">EIIN: {{ $inst->eiin ?? 'N/A' }} • কোড: {{ $inst->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                {{ $inst->type_name }}
                            </span>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                পূর্ণাঙ্গ
                            </span>
                            <div class="text-[11px] text-slate-400 mt-1">{{ $inst->district }}, {{ $inst->division }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-white">{{ $inst->students_count }} শিক্ষার্থী</div>
                            <div class="text-[11px] text-slate-500">{{ $inst->teachers_count }} জন শিক্ষক</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="capitalize font-semibold text-slate-300 bg-slate-900 px-2 py-0.5 rounded border border-slate-700">
                                {{ $inst->subscription_plan }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('super-admin.institutions.status', $inst->id) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-xs text-white focus:outline-none">
                                    <option value="active" {{ $inst->status === 'active' ? 'selected' : '' }}>🟢 সক্রিয়</option>
                                    <option value="pending" {{ $inst->status === 'pending' ? 'selected' : '' }}>🟡 অপেক্ষমান</option>
                                    <option value="suspended" {{ $inst->status === 'suspended' ? 'selected' : '' }}>🔴 স্থগিত</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('super-admin.institutions.impersonate', $inst->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 transition">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                <span>ক্যাম্পাস প্যানেল</span>
                            </a>
                            <a href="{{ route('super-admin.institutions.show', $inst->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                                <i class="fa-solid fa-eye"></i>
                                <span>বিস্তারিত</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">কোনো শিক্ষা প্রতিষ্ঠান পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($institutions->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $institutions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
