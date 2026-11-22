@extends('layouts.superadmin')

@section('title', 'সুপার অ্যাডমিন ন্যাশনাল কমান্ড সেন্টার')

@section('content')
<div class="space-y-8" x-data="{ addModal: false }">
    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-emerald-950/60 p-6 md:p-8 rounded-3xl border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-radial from-emerald-500/10 to-transparent pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-semibold mb-3 border border-emerald-500/20">
                    <i class="fa-solid fa-crown text-emerald-400"></i>
                    <span>সার্বভৌম প্ল্যাটফর্ম অ্যাডমিন প্যানেল</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white">বাংলাদেশ শিক্ষা প্ল্যাটফর্ম ড্যাশবোর্ড</h1>
                <p class="text-slate-400 text-sm mt-1 max-w-2xl">
                    সারাদেশের সকল স্কুল, কলেজ, বিশ্ববিদ্যালয় ও মাদ্রাসার সার্বিক তথ্য, অনুমোদন, সাবস্ক্রিপশন এবং কেন্দ্রীয় নিয়ন্ত্রণ।
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="addModal = true" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>নতুন প্রতিষ্ঠান যোগ করুন</span>
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Stat 1: Total Institutions -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">মোট শিক্ষা প্রতিষ্ঠান</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-building-columns"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono">{{ $totalInstitutions }}</div>
            <div class="flex items-center gap-2 mt-2 text-xs">
                <span class="text-emerald-400 font-semibold">{{ $activeInstitutions }} সক্রিয়</span>
                <span class="text-slate-600">•</span>
                <span class="text-amber-400 font-semibold">{{ $pendingInstitutions }} আবেদন অপেক্ষমান</span>
            </div>
        </div>

        <!-- Stat 2: Total Students Nationwide -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">সারাদেশে শিক্ষার্থী</span>
                <span class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-graduate"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono">{{ number_format($totalStudents) }}</div>
            <div class="flex items-center gap-2 mt-2 text-xs text-slate-400">
                <span>৬৪ জেলার নিবন্ধিত শিক্ষার্থী</span>
            </div>
        </div>

        <!-- Stat 3: Total Teachers -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">শিক্ষক ও স্টাফ</span>
                <span class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono">{{ number_format($totalTeachers) }}</div>
            <div class="flex items-center gap-2 mt-2 text-xs text-emerald-400">
                <i class="fa-solid fa-circle-check text-[10px]"></i>
                <span>শিক্ষক অ্যাপ ব্যবহারকারী</span>
            </div>
        </div>

        <!-- Stat 4: Platform Monthly Revenue -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">মাসিক প্ল্যাটফর্ম রাজস্ব</span>
                <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono">৳ {{ number_format($monthlyRevenue) }}</div>
            <div class="flex items-center gap-2 mt-2 text-xs text-indigo-400">
                <span>Enterprise & Standard SaaS</span>
            </div>
        </div>
    </div>

    <!-- Category Breakdown & Division Coverage -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Institution Types Breakdown -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-lg">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-emerald-400"></i>
                <span>প্রতিষ্ঠানের ধরন অনুযায়ী বিভাজন</span>
            </h3>

            <div class="space-y-3.5">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center"><i class="fa-solid fa-school"></i></span>
                        <div>
                            <div class="font-bold text-sm text-white">স্কুল (School)</div>
                            <div class="text-xs text-slate-500">মাধ্যমিক ও নিম্ন মাধ্যমিক</div>
                        </div>
                    </div>
                    <span class="text-base font-mono font-bold text-white">{{ $byType['school'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center"><i class="fa-solid fa-building-columns"></i></span>
                        <div>
                            <div class="font-bold text-sm text-white">কলেজ (College)</div>
                            <div class="text-xs text-slate-500">উচ্চ মাধ্যমিক ও ডিগ্রি</div>
                        </div>
                    </div>
                    <span class="text-base font-mono font-bold text-white">{{ $byType['college'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center"><i class="fa-solid fa-mosque"></i></span>
                        <div>
                            <div class="font-bold text-sm text-white">মাদ্রাসা (Madrasah)</div>
                            <div class="text-xs text-slate-500">দাখিল, আলিম, কামিল ও হিফজ</div>
                        </div>
                    </div>
                    <span class="text-base font-mono font-bold text-white">{{ $byType['madrasah'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center"><i class="fa-solid fa-graduation-cap"></i></span>
                        <div>
                            <div class="font-bold text-sm text-white">বিশ্ববিদ্যালয় (University)</div>
                            <div class="text-xs text-slate-500">পাবলিক ও প্রাইভেট ইনস্টিটিউট</div>
                        </div>
                    </div>
                    <span class="text-base font-mono font-bold text-white">{{ $byType['university'] }}</span>
                </div>
            </div>
        </div>

        <!-- Division Coverage -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-lg lg:col-span-2">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-emerald-400"></i>
                <span>বিভাগভিত্তিক সম্প্রসারণ (Division Coverage)</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($divisions as $division => $count)
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-center hover:border-emerald-500/50 transition">
                    <div class="text-xs text-slate-400 font-medium">{{ $division }}</div>
                    <div class="text-2xl font-bold font-mono text-emerald-400 mt-1">{{ $count }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">ক্যাম্পাস</div>
                </div>
                @endforeach
            </div>

            <!-- Quick Broadcast Widget -->
            <div class="mt-6 p-4 rounded-xl bg-emerald-950/30 border border-emerald-800/50">
                <form action="{{ route('super-admin.notices.store') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="hidden" name="audience" value="all">
                    <input type="text" name="title" required placeholder="সারাদেশের সকল ক্যাম্পাসে তাৎক্ষণিক জরুরি স্ক্রল নোটিশ প্রচার করুন..." class="flex-1 bg-slate-900 border border-slate-700 text-xs px-3.5 py-2.5 rounded-lg text-white focus:outline-none focus:border-emerald-500">
                    <input type="hidden" name="content" value="জরুরি জাতীয় নির্দেশনা - সুপার অ্যাডমিন কন্ট্রোল প্যানেল থেকে প্রেরিত।">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2.5 rounded-lg flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/30 transition">
                        <i class="fa-solid fa-bullhorn"></i>
                        <span>জাতীয় প্রচার</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Institutions Table -->
    <div class="bg-slate-950 rounded-2xl border border-slate-800 shadow-lg overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-emerald-400"></i>
                    <span>শিক্ষা প্রতিষ্ঠানসমূহ ও লাইভ স্ট্যাটাস</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">যেকোনো প্রতিষ্ঠানের অভ্যন্তরীণ প্যানেলে সরাসরি প্রবেশ বা নিয়ন্ত্রণ করুন</p>
            </div>

            <a href="{{ route('super-admin.institutions') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1.5">
                <span>সবগুলো প্রতিষ্ঠান দেখুন</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900 text-slate-400 uppercase tracking-wider text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">প্রতিষ্ঠানের নাম ও কোড</th>
                        <th class="px-6 py-3.5">ধরন ও EIIN</th>
                        <th class="px-6 py-3.5">ঠিকানা ও জেলা</th>
                        <th class="px-6 py-3.5">প্যাকেজ</th>
                        <th class="px-6 py-3.5">স্ট্যাটাস</th>
                        <th class="px-6 py-3.5 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($recentInstitutions as $inst)
                    <tr class="hover:bg-slate-900/60 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-sm text-white">{{ $inst->name }}</div>
                            <div class="text-[11px] font-mono text-emerald-400 mt-0.5">{{ $inst->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                @if($inst->type === 'madrasah') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                @elseif($inst->type === 'college') bg-blue-500/20 text-blue-400 border border-blue-500/30
                                @elseif($inst->type === 'university') bg-purple-500/20 text-purple-400 border border-purple-500/30
                                @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                @endif">
                                {{ $inst->type }}
                            </span>
                            <div class="text-[11px] text-slate-400 mt-1 font-mono">EIIN: {{ $inst->eiin ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-200">{{ $inst->district }}</div>
                            <div class="text-[11px] text-slate-500">{{ $inst->division }} বিভাগ</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="capitalize font-semibold text-slate-300 bg-slate-900 px-2 py-0.5 rounded border border-slate-700">
                                {{ $inst->subscription_plan }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($inst->status === 'active')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> সক্রিয়
                                </span>
                            @elseif($inst->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> অপেক্ষমান
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> স্থগিত
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <!-- Direct Impersonate / Enter Tenant -->
                            <a href="{{ route('super-admin.institutions.impersonate', $inst->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 transition" title="প্রতিষ্ঠান প্যানেলে প্রবেশ করুন">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                <span>প্যানেল প্রবেশ</span>
                            </a>

                            <!-- View details -->
                            <a href="{{ route('super-admin.institutions.show', $inst->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                                <i class="fa-solid fa-gear"></i>
                                <span>ম্যানেজ</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Add New Institution -->
    <div x-show="addModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-slate-950 border border-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl relative" @click.away="addModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-emerald-400"></i>
                    <span>নতুন শিক্ষা প্রতিষ্ঠান অনুমোদন ও তৈরি</span>
                </h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('super-admin.institutions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">প্রতিষ্ঠানের পূর্ণ নাম *</label>
                        <input type="text" name="name" required placeholder="যেমন: নটর ডেম কলেজ" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">ধরন *</label>
                        <select name="type" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                            <option value="school">স্কুল (School)</option>
                            <option value="college">কলেজ (College)</option>
                            <option value="madrasah">মাদ্রাসা (Madrasah)</option>
                            <option value="university">বিশ্ববিদ্যালয় (University)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">বিভাগ *</label>
                        <select name="division" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                            <option value="Dhaka">ঢাকা বিভাগ</option>
                            <option value="Chittagong">চট্টগ্রাম বিভাগ</option>
                            <option value="Rajshahi">রাজশাহী বিভাগ</option>
                            <option value="Khulna">খুলনা বিভাগ</option>
                            <option value="Barishal">বরিশাল বিভাগ</option>
                            <option value="Sylhet">সিলেট বিভাগ</option>
                            <option value="Rangpur">রংপুর বিভাগ</option>
                            <option value="Mymensingh">ময়মনসিংহ বিভাগ</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">জেলা *</label>
                        <input type="text" name="district" required placeholder="ঢাকা / চট্টগ্রাম ইত্যাদি" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">EIIN নম্বর</label>
                        <input type="text" name="eiin" placeholder="108252" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">সাবস্ক্রিপশন প্যাকেজ *</label>
                        <select name="subscription_plan" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                            <option value="standard">Standard (৫,০০০ ৳/মাস)</option>
                            <option value="premium">Premium (১০,০০০ ৳/মাস)</option>
                            <option value="enterprise">Enterprise (২৫,০০০ ৳/মাস)</option>
                            <option value="starter">Starter (২,৫০০ ৳/মাস)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">ফোন নম্বর *</label>
                        <input type="text" name="phone" required placeholder="01711XXXXXX" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold uppercase mb-1">ইমেইল *</label>
                        <input type="email" name="email" required placeholder="info@institution.edu.bd" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-slate-300 font-semibold uppercase mb-1">সর্বোচ্চ ধারণক্ষমতা (শিক্ষার্থী সংখ্যা) *</label>
                        <input type="number" name="student_capacity" value="2500" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" @click="addModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800">বাতিল</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
