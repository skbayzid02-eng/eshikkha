@extends('layouts.institution')

@section('page_title', 'শ্রেণি ও শাখা ব্যবস্থাপনা')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, sectionModal: false, activeClassId: null, activeClassName: '' }">
    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2.5">
                <i class="fa-solid fa-shapes text-emerald-600"></i>
                <span>শ্রেণি ও শাখা ব্যবস্থাপনা</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">{{ $institution->name }} এর নিজস্ব কাস্টম শ্রেণি ও শাখা পরিচালনা করুন</p>
        </div>

        <button @click="createModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition">
            <i class="fa-solid fa-plus"></i>
            <span>নতুন শ্রেণি তৈরি করুন</span>
        </button>
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

    <!-- Metrics Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">মোট শ্রেণি</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-shapes"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ $classes->count() }}</div>
            <div class="text-[11px] text-slate-400 mt-1">ক্যাম্পাসে সক্রিয় শ্রেণি</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">মোট শাখা (Sections)</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ $classes->sum('sections_count') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">সকল শ্রেণির শাখা সমষ্টি</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">ভর্তি শিক্ষার্থী</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-user-graduate"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ $classes->sum('students_count') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">শ্রেণিগুলোতে মোট শিক্ষার্থী</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">সিঙ্ক স্ট্যাটাস</span>
                <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </span>
            </div>
            <div class="text-sm font-bold text-emerald-600 flex items-center gap-1.5 mt-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>অ্যাপের সাথে লাইভ সিঙ্ক</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Flutter ও Web সরাসরি যুক্ত</div>
        </div>
    </div>

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($classes as $c)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 hover:border-emerald-300 transition flex flex-col justify-between">
            <div>
                <!-- Header: Class Title and Badges -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900">{{ $c->name }}</h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-mono">
                                Lvl: {{ $c->numeric_level }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 capitalize">
                                {{ $c->medium == 'bangla' ? 'বাংলা মাধ্যম' : 'English Version' }}
                            </span>
                            @if($c->group && $c->group != 'general')
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 capitalize">
                                {{ $c->group == 'science' ? 'বিজ্ঞান' : ($c->group == 'humanities' ? 'মানবিক' : ($c->group == 'business' ? 'ব্যবসায় শিক্ষা' : $c->group)) }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('institution.classes.destroy', $c->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই শ্রেণিটি মুছে ফেলতে চান?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-300 hover:text-rose-600 transition p-1" title="শ্রেণি ডিলিট করুন">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>

                <!-- Students count banner -->
                <div class="mb-4">
                    <a href="{{ route('institution.students', ['class_id' => $c->id]) }}" class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-emerald-700 font-medium">
                        <i class="fa-solid fa-user-graduate text-emerald-600"></i>
                        <span class="font-bold font-mono text-slate-800">{{ $c->students_count }}</span> জন শিক্ষার্থী ভর্তি আছেন
                        <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
                    </a>
                </div>

                <!-- Sections List -->
                <div class="space-y-2 border-t border-slate-100 pt-3">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                        <span>শাখা তালিকা ({{ $c->sections->count() }}টি)</span>
                        <button @click="activeClassId = {{ $c->id }}; activeClassName = '{{ addslashes($c->name) }}'; sectionModal = true" class="text-emerald-600 hover:text-emerald-700 text-xs font-bold flex items-center gap-1">
                            <i class="fa-solid fa-plus text-[10px]"></i> নতুন শাখা
                        </button>
                    </div>

                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                        @forelse($c->sections as $sec)
                        <div class="flex items-center justify-between bg-slate-50 rounded-xl px-3 py-2 text-xs border border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span class="font-semibold text-slate-800">{{ $sec->name }}</span>
                                <span class="text-[10px] text-slate-400">({{ $sec->shift == 'morning' ? 'প্রভাতী' : 'দিবা' }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($sec->room_number)
                                <span class="text-[10px] bg-white px-2 py-0.5 rounded border border-slate-200 font-mono text-slate-600">
                                    {{ $sec->room_number }}
                                </span>
                                @endif
                                <form action="{{ route('institution.sections.destroy', $sec->id) }}" method="POST" onsubmit="return confirm('শাখাটি ডিলিট করতে চান?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-300 hover:text-rose-500 p-0.5" title="শাখা মুছুন">
                                        <i class="fa-solid fa-xmark text-[11px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-2 text-[11px] text-slate-400 bg-slate-50 rounded-xl">
                            কোনো শাখা তৈরি হয়নি।
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('institution.students', ['class_id' => $c->id]) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                    <span>শিক্ষার্থী দেখুন</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <button @click="activeClassId = {{ $c->id }}; activeClassName = '{{ addslashes($c->name) }}'; sectionModal = true" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg font-medium transition">
                    + শাখা যোগ
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-4">
                <i class="fa-solid fa-shapes"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">এখনো কোনো শ্রেণি তৈরি করা হয়নি</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-6">
                আপনার প্রতিষ্ঠানের শিক্ষার্থীদের ভর্তি ও ম্যানেজ করতে প্রথমেই আপনার প্রয়োজনীয় শ্রেণিগুলো (যেমন: ষষ্ঠ শ্রেণি, নবম শ্রেণি, প্লে গ্রুপ ইত্যাদি) তৈরি করে নিন।
            </p>
            <button @click="createModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl inline-flex items-center gap-2 shadow-sm transition">
                <i class="fa-solid fa-plus"></i>
                <span>প্রথম শ্রেণি তৈরি করুন</span>
            </button>
        </div>
        @endforelse
    </div>

    <!-- Modal: Add New Class -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative" @click.away="createModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-shapes text-emerald-600"></i>
                    <span>নতুন কাস্টম শ্রেণি তৈরি</span>
                </h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('institution.classes.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-semibold mb-1">শ্রেণির নাম *</label>
                    <input type="text" name="name" required placeholder="যেমন: ষষ্ঠ শ্রেণি, নবম শ্রেণি, প্লে গ্রুপ, একাদশ শ্রেণি" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">শ্রেণি লেভেল (ঐচ্ছিক)</label>
                        <select name="numeric_level" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-emerald-500">
                            <option value="">স্বয়ংক্রিয় শনাক্তকরণ (Auto-Detect)</option>
                            <option value="-2">Play Group (লেভেল -২)</option>
                            <option value="-1">Nursery (লেভেল -১)</option>
                            <option value="0">KG (লেভেল ০)</option>
                            @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}">Class {{ $i }} (লেভেল {{ $i }})</option>
                            @endfor
                            <option value="13">Diploma (লেভেল ১৩)</option>
                            <option value="14">Honours (লেভেল ১৪)</option>
                            <option value="15">Degree (লেভেল ১৫)</option>
                            <option value="16">Masters (লেভেল ১৬)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">বিভাগ / গ্রুপ *</label>
                        <select name="group" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-emerald-500">
                            <option value="general">সাধারণ (General)</option>
                            <option value="science">বিজ্ঞান (Science)</option>
                            <option value="humanities">মানবিক (Humanities)</option>
                            <option value="business">ব্যবসায় শিক্ষা (Business)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-1">শিক্ষার মাধ্যম *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="medium" value="bangla" checked class="text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-slate-800">বাংলা মাধ্যম</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="medium" value="english" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-slate-800">ইংলিশ ভার্সন</span>
                        </label>
                    </div>
                </div>

                <!-- Initial Section -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div class="text-[11px] font-bold text-slate-700 uppercase">প্রাথমিক শাখা তৈরি</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="sm:col-span-2">
                            <label class="block text-slate-500 text-[10px] font-semibold mb-1">শাখার নাম</label>
                            <input type="text" name="initial_section_name" value="শাখা - ক (Section A)" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-slate-500 text-[10px] font-semibold mb-1">শিফট</label>
                            <select name="shift" class="w-full bg-white border border-slate-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-emerald-500">
                                <option value="morning">প্রভাতী</option>
                                <option value="day">দিবা</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold transition">বাতিল</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-sm transition">
                        শ্রেণি সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Add Section to Existing Class -->
    <div x-show="sectionModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl relative" @click.away="sectionModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-emerald-600"></i>
                        <span>নতুন শাখা যোগ করুন</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">শ্রেণি: <span class="font-bold text-slate-800" x-text="activeClassName"></span></p>
                </div>
                <button @click="sectionModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form :action="'/institution/classes/' + activeClassId + '/sections'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-semibold mb-1">শাখার নাম *</label>
                    <input type="text" name="name" required placeholder="যেমন: শাখা - খ, মেঘনা শাখা, Section B" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">শিফট *</label>
                        <select name="shift" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-500">
                            <option value="morning">প্রভাতী (Morning)</option>
                            <option value="day">দিবা (Day)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">রুম নম্বর</label>
                        <input type="text" name="room_number" placeholder="যেমন: Room 202" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-900 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="sectionModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold transition">বাতিল</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-sm transition">
                        শাখা সংরক্ষণ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
