@extends('layouts.institution')

@section('page_title', 'নোটিশ বোর্ড ও SMS অ্যালার্ট')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">নোটিশ বোর্ড ও SMS সম্প্রচার</h1>
            <p class="text-xs text-slate-500 mt-1">শিক্ষক, শিক্ষার্থী ও অভিভাবকদের নিকট অফিসিয়াল বিজ্ঞপ্তি প্রকাশ ও এক ক্লিকে বাল্ক SMS প্রেরণ</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Publish Form -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs lg:col-span-1">
            <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-comment-sms text-emerald-600"></i>
                <span>নতুন বিজ্ঞপ্তি ও SMS পাঠান</span>
            </h3>

            <form action="{{ route('institution.notices.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-semibold mb-1">বিজ্ঞপ্তির শিরোনাম *</label>
                    <input type="text" name="title" required placeholder="যেমন: ছুটির নোটিশ / অভিভাবক সমাবেশ" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-1">প্রাপক শ্রেণি (Target Audience) *</label>
                    <select name="audience" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500">
                        <option value="all">সকলের জন্য (শিক্ষক, শিক্ষার্থী ও অভিভাবক)</option>
                        <option value="guardians">শুধুমাত্র অভিভাবকগণ (Guardian App & SMS)</option>
                        <option value="teachers">শুধুমাত্র শিক্ষকগণ (Teacher App)</option>
                        <option value="students">শুধুমাত্র শিক্ষার্থীগণ</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-1">বিজ্ঞপ্তির বিস্তারিত বিবরণ *</label>
                    <textarea name="content" rows="4" required placeholder="নোটিশের বিস্তারিত বার্তা এখানে লিখুন..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="send_sms" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-0">
                        <span class="font-bold text-emerald-900 text-xs">অভিভাবকদের মোবাইলে তাৎক্ষণিক SMS প্রেরণ করুন</span>
                    </label>
                    <div class="text-[11px] text-emerald-700 mt-1 pl-6">SMS Gateway: Teletalk/Greenweb API সক্রিয়</div>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition shadow-md shadow-emerald-600/20">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> প্রকাশ ও SMS প্রেরণ
                </button>
            </form>
        </div>

        <!-- Notices List -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs lg:col-span-2">
            <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-bell text-emerald-600"></i>
                <span>সাম্প্রতিক নোটিশসমূহ</span>
            </h3>

            <div class="space-y-4">
                @forelse($notices as $notice)
                <div class="p-4 rounded-xl border {{ $notice->institution_id === null ? 'bg-amber-50/50 border-amber-200' : 'bg-slate-50 border-slate-200' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            @if($notice->institution_id === null)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500 text-white uppercase">
                                সুপার অ্যাডমিন জাতীয় বার্তা
                            </span>
                            @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 uppercase">
                                ক্যাম্পাস নোটিশ
                            </span>
                            @endif
                            <span class="text-[11px] text-slate-500">প্রাপক: {{ $notice->audience }}</span>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">{{ $notice->published_at->format('d M, Y') }}</span>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm mb-1.5">{{ $notice->title }}</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $notice->content }}</p>
                </div>
                @empty
                <div class="text-center py-10 text-xs text-slate-400">কোনো নোটিশ পাওয়া যায়নি।</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
