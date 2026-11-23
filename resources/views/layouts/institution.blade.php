<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Institution Management Panel') | ই-শিক্ষা</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Hind Siliguri"', '"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans">

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed bottom-5 right-5 z-50 bg-emerald-600 text-white px-5 py-3.5 rounded-xl shadow-xl flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-xl"></i>
        <div class="font-medium text-sm">{{ session('success') }}</div>
        <button @click="show = false" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @endif

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-68 bg-white border-r border-slate-200 flex flex-col flex-shrink-0 shadow-sm">
            <!-- Active Institution Card -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/60">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-lg shadow-sm flex-shrink-0">
                        @if($institution->type === 'madrasah')
                            <i class="fa-solid fa-mosque"></i>
                        @elseif($institution->type === 'college')
                            <i class="fa-solid fa-building-columns"></i>
                        @elseif($institution->type === 'university')
                            <i class="fa-solid fa-graduation-cap"></i>
                        @else
                            <i class="fa-solid fa-school"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-slate-900 text-sm leading-tight truncate" title="{{ $institution->name }}">{{ $institution->name }}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 uppercase tracking-wide">
                                {{ $institution->type === 'madrasah' ? 'মাদ্রাসা' : ($institution->type === 'college' ? 'কলেজ' : ($institution->type === 'university' ? 'বিশ্ববিদ্যালয়' : 'স্কুল')) }}
                            </span>
                            <span class="text-xs text-slate-500 font-mono">EIIN: {{ $institution->eiin ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Tenant Switcher Dropdown -->
                <div class="mt-3" x-data="{ open: false }">
                    <div class="relative">
                        <button @click="open = !open" class="w-full text-left text-xs bg-white hover:bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 flex items-center justify-between text-slate-700 font-medium transition">
                            <span class="truncate"><i class="fa-solid fa-repeat text-emerald-600 mr-1.5"></i> প্রতিষ্ঠান পরিবর্তন করুন</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-xl py-1 z-30 max-h-56 overflow-y-auto" style="display: none;">
                            @foreach($allInstitutions as $inst)
                            <a href="{{ route('institution.switch', $inst->id) }}" class="block px-3 py-2 text-xs hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 {{ $inst->id === $institution->id ? 'bg-emerald-50 font-bold text-emerald-700' : '' }}">
                                <div class="truncate">{{ $inst->name }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $inst->type }} • {{ $inst->district }}</div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">
                <a href="{{ route('institution.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.dashboard') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-chart-line w-5"></i>
                    <span>ড্যাশবোর্ড</span>
                </a>

                <a href="{{ route('institution.academic-years') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.academic-years*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-check w-5"></i>
                    <span>শিক্ষাবর্ষ ও এনরোলমেন্ট</span>
                </a>

                <a href="{{ route('institution.classes') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.classes*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-shapes w-5"></i>
                    <span>শ্রেণি ও শাখা ব্যবস্থাপনা</span>
                    <span class="ml-auto text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('institution.classes*') ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $institution->academicClasses()->count() }}
                    </span>
                </a>

                <a href="{{ route('institution.students') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.students*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-user-graduate w-5"></i>
                    <span>শিক্ষার্থী তালিকা ও ভর্তি</span>
                    <span class="ml-auto text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('institution.students*') ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $institution->students()->count() }}
                    </span>
                </a>

                <a href="{{ route('institution.teachers') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.teachers*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-chalkboard-user w-5"></i>
                    <span>শিক্ষক ও স্টাফ</span>
                    <span class="ml-auto text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('institution.teachers*') ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $institution->teachers()->count() }}
                    </span>
                </a>

                <a href="{{ route('institution.attendance') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.attendance*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-clipboard-check w-5"></i>
                    <span>দৈনিক হাজিরা ও RFID</span>
                </a>

                <a href="{{ route('institution.fees') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.fees*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-receipt w-5"></i>
                    <span>ফি আদায় ও হিসাবরক্ষণ</span>
                </a>

                <a href="{{ route('institution.exams') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.exams*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-file-lines w-5"></i>
                    <span>পরীক্ষা ও মার্কশিট</span>
                </a>

                <a href="{{ route('institution.routines') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.routines*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-days w-5"></i>
                    <span>ক্লাস রুটিন ও শিডিউল</span>
                </a>

                <a href="{{ route('institution.notices') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('institution.notices*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fa-solid fa-bell w-5"></i>
                    <span>নোটিশ ও SMS অ্যালার্ট</span>
                </a>

                <div class="pt-4 border-t border-slate-200 mt-4">
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-3 mb-2">অন্যান্য লিঙ্ক</div>

                    <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        <i class="fa-solid fa-shield-halved text-emerald-600 w-5"></i>
                        <span>সুপার অ্যাডমিন প্যানেল</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-auto text-slate-400"></i>
                    </a>

                    <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-slate-600 hover:bg-slate-100 transition">
                        <i class="fa-solid fa-globe w-5"></i>
                        <span>মূল ওয়েবসাইট (Homepage)</span>
                    </a>
                </div>
            </nav>

            <!-- Bottom Info -->
            <div class="p-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>সক্রিয় ক্যাম্পাস</span>
                </div>
                <a href="{{ route('logout') }}" title="লগআউট" class="text-rose-600 hover:text-rose-700 font-medium">লগআউট</a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between z-10 shadow-xs">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-bold text-slate-800">
                        @yield('page_title', 'প্রতিষ্ঠান ম্যানেজমেন্ট প্যানেল')
                    </h1>
                    <span class="text-xs text-slate-500 hidden sm:inline">
                        <i class="fa-regular fa-clock mr-1"></i> {{ \Carbon\Carbon::now()->format('d M, Y') }} (আজ)
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('institution.students') }}" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>নতুন ভর্তি</span>
                    </a>
                    <a href="{{ route('institution.notices') }}" class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm transition">
                        <i class="fa-solid fa-comment-sms"></i>
                        <span>SMS পাঠান</span>
                    </a>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
