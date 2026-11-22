@extends('layouts.superadmin')

@section('title', 'জাতীয় নোটিশ ও জরুরি ব্রডকাস্ট')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">জাতীয় নোটিশ ও ব্রডকাস্ট সেন্টার</h1>
            <p class="text-xs text-slate-400 mt-1">সারাদেশের সকল স্কুল, কলেজ, মাদ্রাসা ও বিশ্ববিদ্যালয়ের শিক্ষক-শিক্ষার্থীদের অ্যাপে তাৎক্ষণিক বার্তা পাঠান</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Publish Notice Form -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 lg:col-span-1 shadow-lg">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-emerald-400"></i>
                <span>নতুন জাতীয় নোটিশ প্রচার</span>
            </h3>

            <form action="{{ route('super-admin.notices.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 font-semibold mb-1">নোটিশের শিরোনাম *</label>
                    <input type="text" name="title" required placeholder="জরুরি জাতীয় বার্তা..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-slate-400 font-semibold mb-1">কারা দেখতে পাবে (Target Audience) *</label>
                    <select name="audience" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                        <option value="all">সকল ব্যবহারকারী (প্রতিষ্ঠান, শিক্ষক, অভিভাবক)</option>
                        <option value="institutions">শুধুমাত্র প্রতিষ্ঠান প্রশাসন</option>
                        <option value="teachers">শুধুমাত্র শিক্ষকগণ (শিক্ষক অ্যাপ)</option>
                        <option value="students">শুধুমাত্র শিক্ষার্থী ও অভিভাবকগণ (স্টুডেন্ট অ্যাপ)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 font-semibold mb-1">বিস্তারিত বিবরণ *</label>
                    <textarea name="content" rows="4" required placeholder="নোটিশের বিস্তারিত লিখুন..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_pinned" id="is_pinned" value="1" class="rounded bg-slate-900 border-slate-700 text-emerald-600">
                    <label for="is_pinned" class="text-slate-300">গুরুত্বপূর্ণ নোটিশ হিসেবে পিন করে রাখুন</label>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-600/30">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> সারাদেশে প্রচার করুন
                </button>
            </form>
        </div>

        <!-- Published Notices List -->
        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 lg:col-span-2 shadow-lg">
            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-list-ul text-emerald-400"></i>
                <span>পূর্বে প্রচারিত জাতীয় নোটিশসমূহ</span>
            </h3>

            <div class="space-y-4">
                @forelse($notices as $notice)
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase font-semibold">
                            {{ $notice->audience }}
                        </span>
                        <span class="text-xs text-slate-500">{{ $notice->published_at->format('d M, Y') }}</span>
                    </div>
                    <h4 class="font-bold text-white text-sm mb-1">{{ $notice->title }}</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $notice->content }}</p>
                </div>
                @empty
                <div class="text-center py-10 text-xs text-slate-500">কোনো নোটিশ নেই।</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
