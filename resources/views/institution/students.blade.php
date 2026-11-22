@extends('layouts.institution')

@section('page_title', 'শিক্ষার্থী ব্যবস্থাপনা ও ভর্তি')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">শিক্ষার্থী তালিকা</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $institution->name }} এর সকল শ্রেণির শিক্ষার্থী বিবরণ</p>
        </div>

        <button @click="addModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition">
            <i class="fa-solid fa-user-plus"></i>
            <span>নতুন শিক্ষার্থী ভর্তি</span>
        </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('institution.students') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 uppercase font-semibold mb-1">নাম / রোল / মোবাইল</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="অনুসন্ধান করুন..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-slate-500 uppercase font-semibold mb-1">শ্রেণি অনুযায়ী ফিল্টার</label>
                <select name="class_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="all">সকল শ্রেণি</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl transition">
                    ফিল্টার
                </button>
                <a href="{{ route('institution.students') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-2 rounded-xl transition">
                    রিসেট
                </a>
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">রোল</th>
                        <th class="px-6 py-3.5">শিক্ষার্থীর নাম ও ভর্তি নম্বর</th>
                        <th class="px-6 py-3.5">শ্রেণি ও শাখা</th>
                        <th class="px-6 py-3.5">অভিভাবক ও মোবাইল</th>
                        <th class="px-6 py-3.5">রক্তের গ্রুপ</th>
                        <th class="px-6 py-3.5 text-right">স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $st)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900 font-mono text-sm">#{{ $st->roll_number }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-sm text-slate-900">{{ $st->name }}</div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $st->admission_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-slate-800">{{ $st->academicClass?->name }}</span>
                            <div class="text-[11px] text-emerald-600 font-medium">{{ $st->section?->name }} ({{ $st->section?->shift }})</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-800">{{ $st->guardian_name }}</div>
                            <div class="text-[11px] font-mono text-slate-500">{{ $st->guardian_phone }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[11px] font-mono">
                                {{ $st->blood_group ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800">
                                সক্রিয় (Active)
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">কোনো শিক্ষার্থী পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $students->links() }}
        </div>
        @endif
    </div>

    <!-- Modal: Add Student -->
    <div x-show="addModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl relative"
             @click.away="addModal = false"
             x-data="{
                sameAddress: false,
                presentVillage: '',
                presentPostOffice: '',
                presentUpazila: '',
                presentDistrict: '',
                permVillage: '',
                permPostOffice: '',
                permUpazila: '',
                permDistrict: '',
                syncAddress() {
                    if (this.sameAddress) {
                        this.permVillage = this.presentVillage;
                        this.permPostOffice = this.presentPostOffice;
                        this.permUpazila = this.presentUpazila;
                        this.permDistrict = this.presentDistrict;
                    }
                }
             }">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 sm:p-6 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">নতুন শিক্ষার্থী ভর্তি ফরম</h3>
                        <p class="text-xs text-slate-500">শিক্ষার্থীর যাবতীয় তথ্য পূরণ করে ভর্তি প্রক্রিয়া সম্পন্ন করুন</p>
                    </div>
                </div>
                <button @click="addModal = false" class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Form Content (Scrollable) -->
            <form action="{{ route('institution.students.store') }}" method="POST" class="overflow-y-auto p-5 sm:p-7 space-y-6 text-xs">
                @csrf

                <!-- Section 1: শিক্ষার্থীর মৌলিক তথ্য -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 text-slate-800 font-bold text-sm">
                        <i class="fa-solid fa-id-card text-emerald-600"></i>
                        <span>১. শিক্ষার্থীর মৌলিক তথ্য</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-1">
                            <label class="block text-slate-700 font-semibold mb-1">শিক্ষার্থীর পূর্ণ নাম *</label>
                            <input type="text" name="name" required placeholder="যেমন: তানভীর আহমেদ" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-slate-700 font-semibold">শ্রেণি (Class) *</label>
                                <span class="text-[10px] text-emerald-600 font-medium">টাইপ বা নির্বাচন</span>
                            </div>
                            <input type="text" name="class_name" required list="classes-list" placeholder="যেমন: ষষ্ঠ শ্রেণি, Class 9 ইত্যাদি" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                            <datalist id="classes-list">
                                @foreach($classes as $c)
                                <option value="{{ $c->name }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-slate-700 font-semibold">বিভাগ বা শাখা (Section)</label>
                                <span class="text-[10px] text-slate-400">ঐচ্ছিক</span>
                            </div>
                            <input type="text" name="section_name" list="sections-list" placeholder="যেমন: শাখা - ক, Section A, দিবা ইত্যাদি" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                            <datalist id="sections-list">
                                @foreach($classes as $c)
                                    @foreach($c->sections as $sec)
                                    <option value="{{ $sec->name }}"></option>
                                    @endforeach
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">ক্লাস রোল নম্বর *</label>
                            <input type="number" name="roll_number" required placeholder="যেমন: ১, ১২" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">লিঙ্গ (Gender) *</label>
                            <select name="gender" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                                <option value="">-- লিঙ্গ নির্বাচন করুন --</option>
                                <option value="male">পুরুষ (Male)</option>
                                <option value="female">মহিলা (Female)</option>
                                <option value="other">তৃতীয় লিঙ্গ / অন্যান্য (Third Gender / Other)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">রক্তের গ্রুপ (Blood Group)</label>
                            <select name="blood_group" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                                <option value="">-- রক্তের গ্রুপ নির্বাচন করুন --</option>
                                <option value="A+">A+ (A Positive)</option>
                                <option value="A-">A- (A Negative)</option>
                                <option value="B+">B+ (B Positive)</option>
                                <option value="B-">B- (B Negative)</option>
                                <option value="O+">O+ (O Positive)</option>
                                <option value="O-">O- (O Negative)</option>
                                <option value="AB+">AB+ (AB Positive)</option>
                                <option value="AB-">AB- (AB Negative)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">জন্ম তারিখ (Date of Birth)</label>
                            <input type="date" name="dob" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: পিতা-মাতা ও যোগাযোগের তথ্য -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100 text-slate-800 font-bold text-sm">
                        <i class="fa-solid fa-users text-emerald-600"></i>
                        <span>২. পিতা-মাতা ও যোগাযোগের তথ্য</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">পিতার নাম (Father's Name)</label>
                            <input type="text" name="father_name" placeholder="পিতার পূর্ণ নাম" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">মাতার নাম (Mother's Name)</label>
                            <input type="text" name="mother_name" placeholder="মাতার পূর্ণ নাম" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">অভিভাবকের মোবাইল নম্বর *</label>
                            <input type="text" name="guardian_phone" required placeholder="017XXXXXXXX" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                            <span class="inline-flex items-center gap-1 text-[10px] text-amber-700 font-medium bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200 mt-1">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>শুধুমাত্র অ্যাপের ভেতর মেসেজিংয়ের জন্য, কোনো সাধারণ এসএমএস নয়</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: বর্তমান ঠিকানা -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                            <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                            <span>৩. বর্তমান ঠিকানা (Present Address)</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">বাসার ঠিকানা / হোল্ডিং নং / গ্রাম</label>
                            <input type="text" name="present_village" x-model="presentVillage" @input="syncAddress()" placeholder="যেমন: বাসা নং ১২, রোড ৩, গ্রাম" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">ডাকঘর / পোস্ট অফিস</label>
                            <input type="text" name="present_post_office" x-model="presentPostOffice" @input="syncAddress()" placeholder="যেমন: ধানমন্ডি - ১২০৫" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">উপজেলা বা থানা</label>
                            <input type="text" name="present_upazila" x-model="presentUpazila" @input="syncAddress()" placeholder="যেমন: ধানমন্ডি থানা" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">জেলা</label>
                            <input type="text" name="present_district" x-model="presentDistrict" @input="syncAddress()" placeholder="যেমন: ঢাকা" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>
                    </div>
                </div>

                <!-- Section 4: স্থায়ী ঠিকানা -->
                <div class="space-y-3 pt-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                            <i class="fa-solid fa-house-chimney text-emerald-600"></i>
                            <span>৪. স্থায়ী ঠিকানা (Permanent Address)</span>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 hover:bg-emerald-100 transition">
                            <input type="checkbox" x-model="sameAddress" @change="syncAddress()" class="rounded border-emerald-400 text-emerald-600 focus:ring-emerald-500">
                            <span>স্থায়ী ঠিকানা বর্তমান ঠিকানার অনুরূপ</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">বাসার ঠিকানা / হোল্ডিং নং / গ্রাম</label>
                            <input type="text" name="permanent_village" x-model="permVillage" placeholder="গ্রাম / হোল্ডিং নম্বর" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">ডাকঘর / পোস্ট অফিস</label>
                            <input type="text" name="permanent_post_office" x-model="permPostOffice" placeholder="পোস্ট অফিসের নাম" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">উপজেলা বা থানা</label>
                            <input type="text" name="permanent_upazila" x-model="permUpazila" placeholder="উপজেলা / থানা" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">জেলা</label>
                            <input type="text" name="permanent_district" x-model="permDistrict" placeholder="জেলার নাম" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 focus:bg-white text-slate-800 transition">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="addModal = false" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        বাতিল
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm flex items-center gap-2 transition">
                        <i class="fa-solid fa-check"></i>
                        <span>ভর্তি নিশ্চিত করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
