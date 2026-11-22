@extends('layouts.app')

@section('title', 'ই-শিক্ষা (eShikkha) | বাংলাদেশের স্মার্ট শিক্ষা ব্যবস্থাপনা প্ল্যাটফর্ম')

@section('content')
<!-- Top Notification Ticker -->
<div class="bg-gradient-to-r from-emerald-800 to-teal-900 text-white text-xs py-2 px-4">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <span class="bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">জরুরি বিজ্ঞপ্তি</span>
            <span class="truncate">📢 সমগ্র বাংলাদেশে ই-শিক্ষা প্ল্যাটফর্মে জাতীয় শিক্ষা সপ্তাহ ২০২৬ নিবন্ধন উন্মুক্ত করা হয়েছে।</span>
        </div>
        <div class="flex items-center gap-4 text-emerald-200 text-[11px]">
            <span><i class="fa-solid fa-headset mr-1"></i> হেল্পলাইন: ০৯৬৩৮-৭৭৭০০০</span>
            <span class="hidden md:inline">|</span>
            <span class="hidden md:inline"><i class="fa-solid fa-envelope mr-1"></i> support@eshikkha.com</span>
        </div>
    </div>
</div>

<!-- Main Navigation -->
<header class="bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-emerald-600/30">
                <i class="fa-solid fa-graduation-cap text-2xl"></i>
            </div>
            <div>
                <div class="font-extrabold text-2xl text-slate-900 tracking-tight flex items-center gap-1.5">
                    ই-শিক্ষা <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">eShikkha</span>
                </div>
                <div class="text-xs text-slate-500 font-medium">স্মার্ট বাংলাদেশ ডিজিটাল শিক্ষা ইকোসিস্টেম</div>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-700">
            <a href="#panels" class="hover:text-emerald-600 transition">প্রধান ৪টি অংশ</a>
            <a href="#features" class="hover:text-emerald-600 transition">সুবিধাসমূহ</a>
            <a href="#institutions" class="hover:text-emerald-600 transition">প্রতিষ্ঠানসমূহ</a>
            <a href="#apps" class="hover:text-emerald-600 transition">মোবাইল অ্যাপস</a>
            <a href="#register" class="hover:text-emerald-600 transition">নতুন আবেদন</a>
        </nav>

        <!-- Action / Portal Access Buttons -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Super Admin Direct Link -->
            <a href="{{ route('super-admin.dashboard') }}" class="text-xs sm:text-sm font-semibold bg-slate-900 hover:bg-slate-800 text-white px-3 sm:px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition">
                <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                <span class="hidden sm:inline">সুপার অ্যাডমিন</span>
            </a>

            <!-- Institution Management Direct Link -->
            <a href="{{ route('institution.dashboard') }}" class="text-xs sm:text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-3 sm:px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-md shadow-emerald-600/20 transition">
                <i class="fa-solid fa-school"></i>
                <span>প্রতিষ্ঠান প্যানেল</span>
            </a>

            <!-- General Login -->
            <a href="{{ route('login') }}" class="text-xs sm:text-sm font-medium border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-3 sm:px-3.5 py-2.5 rounded-xl transition">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </a>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28 bg-gradient-to-b from-slate-50 via-emerald-50/20 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs sm:text-sm font-semibold mb-6 border border-emerald-200">
                <i class="fa-solid fa-sparkles text-emerald-600"></i>
                <span>স্কুল • কলেজ • বিশ্ববিদ্যালয় • মাদ্রাসা সমন্বিত সিস্টেম</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.2]">
                সমগ্র বাংলাদেশের শিক্ষা ব্যবস্থাপনায় <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600">এক প্ল্যাটফর্মে ৪টি শক্তিশালী সমাধান</span>
            </h1>

            <p class="mt-6 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                কেন্দ্রীয় সুপার অ্যাডমিন থেকে প্রতিটি স্কুল-মাদ্রাসার নিজস্ব প্যানেল, সাথে শিক্ষকদের জন্য আলাদা অ্যাপ এবং শিক্ষার্থী ও অভিভাবকদের জন্য স্মার্ট মোবাইল অ্যাপ।
            </p>

            <!-- Quick Action Buttons -->
            <div class="mt-8 sm:mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('super-admin.dashboard') }}" class="px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm sm:text-base flex items-center gap-2.5 shadow-lg shadow-slate-900/20 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                    <span>সুপার অ্যাডমিন প্যানেল প্রবেশ</span>
                </a>
                <a href="{{ route('institution.dashboard') }}" class="px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm sm:text-base flex items-center gap-2.5 shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>প্রতিষ্ঠান ম্যানেজমেন্ট প্যানেল</span>
                </a>
                <a href="#apps" class="px-6 py-3.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold text-sm sm:text-base flex items-center gap-2.5 shadow-xs transition">
                    <i class="fa-solid fa-mobile-screen-button text-emerald-600"></i>
                    <span>মোবাইল অ্যাপস দেখুন</span>
                </a>
            </div>
        </div>

        <!-- 4 Core Parts Cards -->
        <div id="panels" class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- 1. Super Admin Panel -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-950 text-white rounded-2xl p-6 shadow-xl border border-slate-800 relative group hover:border-emerald-500/50 transition">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl mb-4 border border-emerald-500/30">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-1">অংশ ১ • ওয়েব প্যানেল</div>
                <h3 class="text-xl font-bold text-white mb-2">Super Admin Panel</h3>
                <p class="text-sm text-slate-300 mb-5 leading-relaxed">
                    নিজের নিয়ন্ত্রণে পুরো বাংলাদেশের সকল স্কুল, কলেজ, বিশ্ববিদ্যালয় ও মাদ্রাসার অনুমোদন, রাজস্ব, প্যাকেজ ও জাতীয় বিজ্ঞপ্তি মনিটরিং।
                </p>
                <a href="{{ route('super-admin.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-400 group-hover:text-emerald-300">
                    <span>প্যানেল ওপেন করুন</span>
                    <i class="fa-solid fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition"></i>
                </a>
            </div>

            <!-- 2. Institution Management Panel -->
            <div class="bg-white rounded-2xl p-6 shadow-xl border border-slate-200 relative group hover:border-emerald-500 transition">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-school"></i>
                </div>
                <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider mb-1">অংশ ২ • ওয়েব প্যানেল</div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Institution Panel</h3>
                <p class="text-sm text-slate-600 mb-5 leading-relaxed">
                    প্রতিটি প্রতিষ্ঠানের নিজস্ব শিক্ষার্থী ভর্তি, শিক্ষক ও স্টাফ, দৈনিক বায়োমেট্রিক হাজিরা, bKash ফি গ্রহণ, মার্কশিট ও রুটিন।
                </p>
                <a href="{{ route('institution.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                    <span>ক্যাম্পাস ড্যাশবোর্ড</span>
                    <i class="fa-solid fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition"></i>
                </a>
            </div>

            <!-- 3. Teacher App -->
            <div class="bg-white rounded-2xl p-6 shadow-xl border border-slate-200 relative group hover:border-teal-500 transition">
                <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div class="text-xs font-semibold text-teal-700 uppercase tracking-wider mb-1">অংশ ৩ • Flutter মোবাইল অ্যাপ</div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Teacher App</h3>
                <p class="text-sm text-slate-600 mb-5 leading-relaxed">
                    শিক্ষকদের জন্য ক্লাস হাজিরা এক ক্লিকে নেওয়া, পরীক্ষার নম্বর এন্ট্রি, বাড়ির কাজ আপলোড এবং শিক্ষার্থীদের উপস্থিতি নজরদারি।
                </p>
                <div class="text-xs font-mono text-slate-500 bg-slate-50 px-2 py-1 rounded truncate mb-3">
                    /eshikkhateacher
                </div>
                <a href="#apps" class="inline-flex items-center gap-2 text-xs font-bold text-teal-700 group-hover:text-teal-800">
                    <span>অ্যাপ বিবরণ দেখুন</span>
                    <i class="fa-solid fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition"></i>
                </a>
            </div>

            <!-- 4. Student & Guardian App -->
            <div class="bg-white rounded-2xl p-6 shadow-xl border border-slate-200 relative group hover:border-cyan-500 transition">
                <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
                <div class="text-xs font-semibold text-cyan-700 uppercase tracking-wider mb-1">অংশ ৪ • Flutter মোবাইল অ্যাপ</div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Student & Guardian</h3>
                <p class="text-sm text-slate-600 mb-5 leading-relaxed">
                    অভিভাবকদের জন্য সন্তানের উপস্থিতি লাইভ SMS নোটিফিকেশন, ডিজিটাল আইডি, bKash-এ সরাসরি বেতন পরিশোধ ও ফলাফল।
                </p>
                <div class="text-xs font-mono text-slate-500 bg-slate-50 px-2 py-1 rounded truncate mb-3">
                    /eshikkhastudent
                </div>
                <a href="#apps" class="inline-flex items-center gap-2 text-xs font-bold text-cyan-700 group-hover:text-cyan-800">
                    <span>অ্যাপ বিবরণ দেখুন</span>
                    <i class="fa-solid fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Nationwide Statistics -->
<section class="bg-slate-900 text-white py-12 border-y border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center divide-y lg:divide-y-0 lg:divide-x divide-slate-800">
            <div class="pt-4 lg:pt-0">
                <div class="text-3xl sm:text-4xl font-extrabold text-emerald-400 font-mono">{{ $stats['total_districts'] }}</div>
                <div class="text-xs sm:text-sm text-slate-400 mt-1">সমগ্র ৬৪ জেলা কভারেজ</div>
            </div>
            <div class="pt-4 lg:pt-0">
                <div class="text-3xl sm:text-4xl font-extrabold text-teal-400 font-mono">{{ $stats['total_institutions'] }}+</div>
                <div class="text-xs sm:text-sm text-slate-400 mt-1">নিবন্ধিত শিক্ষা প্রতিষ্ঠান</div>
            </div>
            <div class="pt-4 lg:pt-0">
                <div class="text-3xl sm:text-4xl font-extrabold text-cyan-400 font-mono">{{ number_format($stats['total_students']) }}+</div>
                <div class="text-xs sm:text-sm text-slate-400 mt-1">শিক্ষার্থী ডাটাবেজ</div>
            </div>
            <div class="pt-4 lg:pt-0">
                <div class="text-3xl sm:text-4xl font-extrabold text-amber-400 font-mono">৯৯.৮%</div>
                <div class="text-xs sm:text-sm text-slate-400 mt-1">ডিজিটাল হাজিরা ও SMS সতর্কতা</div>
            </div>
        </div>
    </div>
</section>

<!-- Interactive Institutions Showcase -->
<section id="institutions" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest">ক্যাম্পাসসমূহ</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-1">প্ল্যাটফর্মে সংযুক্ত শীর্ষ প্রতিষ্ঠানসমূহ</h2>
                <p class="text-slate-600 text-sm mt-2">স্কুল, কলেজ, মাদ্রাসা ও বিশ্ববিদ্যালয় ভিত্তিক আলাদা আলাদা কনফিগারেশন</p>
            </div>
            <a href="{{ route('institution.dashboard') }}" class="mt-4 md:mt-0 text-sm font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5">
                <span>প্রতিষ্ঠান প্যানেলে ডেমো টেস্ট করুন</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($institutions as $inst)
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider
                            @if($inst->type === 'madrasah') bg-amber-100 text-amber-800
                            @elseif($inst->type === 'college') bg-blue-100 text-blue-800
                            @elseif($inst->type === 'university') bg-purple-100 text-purple-800
                            @else bg-emerald-100 text-emerald-800
                            @endif">
                            {{ $inst->type === 'madrasah' ? 'মাদ্রাসা' : ($inst->type === 'college' ? 'কলেজ' : ($inst->type === 'university' ? 'বিশ্ববিদ্যালয়' : 'স্কুল')) }}
                        </span>
                        <span class="text-xs font-mono text-slate-500 font-bold">{{ $inst->code }}</span>
                    </div>

                    <h4 class="font-bold text-slate-900 text-lg mb-2">{{ $inst->name }}</h4>
                    <p class="text-xs text-slate-500 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-slate-400"></i>
                        <span>{{ $inst->district }}, {{ $inst->division }} বিভাগ</span>
                    </p>

                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 bg-white p-3 rounded-xl border border-slate-200 mb-4">
                        <div>
                            <span class="text-slate-400 block text-[10px]">EIIN কোড</span>
                            <span class="font-semibold">{{ $inst->eiin ?? 'প্রযোজ্য নয়' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">প্যাকেজ</span>
                            <span class="font-semibold capitalize text-emerald-600">{{ $inst->subscription_plan }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                    <a href="{{ route('institution.switch', $inst->id) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>এই প্রতিষ্ঠানের প্যানেলে যান</span>
                    </a>
                    <span class="text-[10px] text-slate-400 font-medium">সক্রিয়</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Mobile Apps Showcase (Teacher & Student) -->
<section id="apps" class="py-20 bg-slate-900 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Flutter Mobile Applications</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-2">২টি সম্পূর্ণ ডেডিকেটেড মোবাইল অ্যাপ</h2>
            <p class="text-slate-400 text-sm sm:text-base mt-3">
                শিক্ষকদের জন্য আলাদা ও অভিভাবক-শিক্ষার্থীদের জন্য আলাদা দুটি হাই-পারফরম্যান্স Flutter অ্যাপ।
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Teacher App Box -->
            <div class="bg-slate-950 rounded-3xl p-8 border border-slate-800 relative shadow-2xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center text-2xl border border-teal-500/30">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">eShikkha Teacher App</h3>
                        <div class="text-xs text-teal-400 font-mono">D:\laragon\www\eshikkha\APPLICATION_FLUTTER\eshikkhateacher</div>
                    </div>
                </div>

                <ul class="space-y-3.5 text-sm text-slate-300 mb-8">
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-teal-400 mt-1"></i>
                        <span><strong>দ্রুত হাজিরা গ্রহণ:</strong> ক্লাসে দাঁড়িয়ে এক ক্লিকে রোল অনুযায়ী Present/Absent নির্ধারণ।</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-teal-400 mt-1"></i>
                        <span><strong>পরীক্ষার নম্বর এন্ট্রি:</strong> সাময়িক বা চূড়ান্ত পরীক্ষার সাবজেক্ট অনুযায়ী নম্বর দিলে স্বয়ংক্রিয় GPA হিসাব।</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-teal-400 mt-1"></i>
                        <span><strong>ক্লাস রুটিন ও শিডিউল:</strong> শিক্ষক নিজের প্রতিদিনের ক্লাস টাইমিং ও রুম নম্বর সরাসরি অ্যাপে দেখতে পাবেন।</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-teal-400 mt-1"></i>
                        <span><strong>নোটিশ ও যোগাযোগ:</strong> প্রতিষ্ঠান কর্তৃপক্ষের জরুরি নোটিশ সরাসরি নোটিফিকেশনে পাওয়া।</span>
                    </li>
                </ul>

                <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">REST API Integration: <code class="text-teal-400">/api/teacher/*</code></span>
                    <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-check mr-1"></i> Ready & Connected</span>
                </div>
            </div>

            <!-- Student App Box -->
            <div class="bg-slate-950 rounded-3xl p-8 border border-slate-800 relative shadow-2xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-2xl border border-cyan-500/30">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">eShikkha Student & Guardian App</h3>
                        <div class="text-xs text-cyan-400 font-mono">D:\laragon\www\eshikkha\APPLICATION_FLUTTER\eshikkhastudent</div>
                    </div>
                </div>

                <ul class="space-y-3.5 text-sm text-slate-300 mb-8">
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-cyan-400 mt-1"></i>
                        <span><strong>অভিভাবকের লাইভ উপস্থিতি অ্যালার্ট:</strong> শিক্ষার্থী ক্যাম্পাসে প্রবেশ বা অনুপস্থিত হলে অভিভাবকের ফোনে তাৎক্ষণিক নোটিফিকেশন।</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-cyan-400 mt-1"></i>
                        <span><strong>bKash / Nagad ফি পেমেন্ট:</strong> বকেয়া মাসিক বেতন, পরীক্ষার ফি অ্যাপ থেকেই ১-ক্লিকে পরিশোধ ও ডিজিটাল রসিদ ডাউনলোড।</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-cyan-400 mt-1"></i>
                        <span><strong>মার্কশিট ও গ্রেড ট্র্যাকিং:</strong> বোর্ড মানদণ্ডে (A+, A, GPA 5.00) বিষয়ভিত্তিক পরীক্ষার ফলাফল বিশ্লেষণ।</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-cyan-400 mt-1"></i>
                        <span><strong>ডিজিটাল স্টুডেন্ট আইডি:</strong> QR কোডযুক্ত ডিজিটাল পরিচয়পত্র ও ক্লাস রুটিন।</span>
                    </li>
                </ul>

                <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">REST API Integration: <code class="text-cyan-400">/api/student/*</code></span>
                    <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-check mr-1"></i> Ready & Connected</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 19 Institution Types & Management Showcase -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold mb-3 border border-emerald-200">
                <i class="fa-solid fa-layer-group text-emerald-600"></i>
                <span>১৯টি শিক্ষা প্রতিষ্ঠানের সমন্বিত ইকোসিস্টেম</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                সকল ধরনের শিক্ষা প্রতিষ্ঠানে <span class="text-emerald-600">পূর্ণাঙ্গ ব্যবস্থাপনা</span>
            </h2>
            <p class="mt-3 text-slate-600 text-sm sm:text-base">
                প্রাথমিক বিদ্যালয় থেকে শুরু করে বিশ্ববিদ্যালয় ও ধর্মীয় প্রতিষ্ঠান—প্রত্যেকটির জন্য রয়েছে আলাদা অ্যাকাডেমিক মানদণ্ড ও ডিজিটাল সমাধান।
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($supportedTypes as $typeKey => $t)
            <div class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-500/40 bg-slate-50 hover:bg-emerald-50/20 transition group flex flex-col justify-between">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-xs border border-slate-200 text-emerald-600 flex items-center justify-center text-lg flex-shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="{{ $t['icon'] }}"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $t['name_bn'] }}</h4>
                        <div class="text-[11px] text-slate-500 font-medium">{{ $t['name_en'] }}</div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs">
                    <span class="text-slate-500 text-[11px]">ব্যবস্থাপনা:</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] border border-emerald-200">
                        <i class="fa-solid fa-circle-check text-[9px] text-emerald-600"></i> {{ $t['management'] }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Institution Onboarding / Application Form -->
<section id="register" class="py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-8 sm:p-12 shadow-xl border border-slate-200">
            <div class="text-center max-w-xl mx-auto mb-10">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest">নতুন প্রতিষ্ঠানের আবেদন</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-2">আপনার শিক্ষা প্রতিষ্ঠানকে ই-শিক্ষায় যুক্ত করুন</h2>
                <p class="text-slate-600 text-sm mt-2">আবেদন করার পর সুপার অ্যাডমিন যাচাই করে আপনার ক্যাম্পাসের জন্য প্যানেল প্রস্তুত করে দেবে।</p>
            </div>

            <form action="{{ route('register-institution') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">প্রতিষ্ঠানের নাম *</label>
                        <input type="text" name="name" required placeholder="যেমন: ধানমন্ডি সরকারি উচ্চ বিদ্যালয়" class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">প্রতিষ্ঠানের ধরন *</label>
                        <select name="type" required class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none bg-white">
                            @foreach($supportedTypes as $typeKey => $t)
                                <option value="{{ $typeKey }}">{{ $t['name_bn'] }} (ব্যবস্থাপনা: {{ $t['management'] }})</option>
                            @endforeach
                            <option value="school">অন্যান্য সাধারণ স্কুল</option>
                            <option value="college">অন্যান্য সাধারণ কলেজ</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">বিভাগ *</label>
                        <select name="division" required class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none bg-white">
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
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">জেলা *</label>
                        <input type="text" name="district" required placeholder="যেমন: ঢাকা" class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">মোবাইল নম্বর (প্রধান শিক্ষক / অধ্যক্ষ) *</label>
                        <input type="text" name="phone" required placeholder="017XXXXXXXX" class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">অফিসিয়াল ইমেইল *</label>
                        <input type="email" name="email" required placeholder="principal@school.edu.bd" class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">EIIN নম্বর (যদি থাকে)</label>
                        <input type="text" name="eiin" placeholder="যেমন: 108252" class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">ঠিকানা</label>
                        <input type="text" name="address" placeholder="রোড, থানা, পোস্ট কোড" class="w-full text-sm px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none">
                    </div>
                </div>

                <div class="text-center pt-4">
                    <button type="submit" class="w-full sm:w-auto px-10 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base rounded-xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-paper-plane mr-2"></i> আবেদনের অনুরোধ জমা দিন
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-slate-950 text-slate-400 py-12 border-t border-slate-800 text-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center text-white">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <div class="text-white font-bold">ই-শিক্ষা (eShikkha) Bangladesh</div>
                <div class="text-xs text-slate-500">ডিজিটাল ও স্মার্ট শিক্ষা ব্যবস্থাপনা ইকোসিস্টেম</div>
            </div>
        </div>

        <div class="flex items-center gap-6 text-xs">
            <a href="{{ route('super-admin.dashboard') }}" class="hover:text-emerald-400">সুপার অ্যাডমিন</a>
            <a href="{{ route('institution.dashboard') }}" class="hover:text-emerald-400">প্রতিষ্ঠান প্যানেল</a>
            <a href="#apps" class="hover:text-emerald-400">শিক্ষক অ্যাপ</a>
            <a href="#apps" class="hover:text-emerald-400">অভিভাবক অ্যাপ</a>
        </div>

        <div class="text-xs text-slate-600">
            © ২০২৬ ই-শিক্ষা বাংলাদেশ। সর্বস্বত্ব সংরক্ষিত।
        </div>
    </div>
</footer>
@endsection
