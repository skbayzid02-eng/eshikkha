<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin Panel') | ই-শিক্ষা বাংলাদেশ</title>

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
<body class="bg-slate-900 text-slate-100 antialiased font-sans">

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed bottom-5 right-5 z-50 bg-emerald-600 text-white px-5 py-3.5 rounded-xl shadow-xl flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-xl"></i>
        <div class="font-medium text-sm">{{ session('success') }}</div>
        <button @click="show = false" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @endif

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-72 bg-slate-950 border-r border-slate-800 flex flex-col flex-shrink-0">
            <!-- Brand -->
            <div class="p-5 border-b border-slate-800/80">
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-shield-halved text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-lg text-white tracking-tight flex items-center gap-1.5">
                            eShikkha <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Super Admin</span>
                        </div>
                        <div class="text-xs text-slate-400">বাংলাদেশ শিক্ষা কমান্ড সেন্টার</div>
                    </div>
                </a>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-1.5 text-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 px-3 mb-2">সারাদেশ ওভারভিউ</div>

                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('super-admin.dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-chart-pie w-5"></i>
                    <span>ন্যাশনাল ড্যাশবোর্ড</span>
                </a>

                <a href="{{ route('super-admin.institutions') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('super-admin.institutions*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-building-columns w-5"></i>
                    <span>শিক্ষা প্রতিষ্ঠানসমূহ</span>
                    <span class="ml-auto text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-300">{{ \App\Models\Institution::count() }}</span>
                </a>

                <a href="{{ route('super-admin.subscriptions') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('super-admin.subscriptions') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-credit-card w-5"></i>
                    <span>প্যাকেজ ও রাজস্ব</span>
                </a>

                <a href="{{ route('super-admin.notices') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('super-admin.notices') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-bullhorn w-5"></i>
                    <span>জাতীয় নোটিশ ও ব্রডকাস্ট</span>
                </a>

                <div class="pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-500 px-3 mb-2">সুইচ ও পোর্টাল</div>

                <a href="{{ route('institution.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-emerald-400 bg-emerald-950/40 border border-emerald-800/50 hover:bg-emerald-900/40 transition">
                    <i class="fa-solid fa-school w-5"></i>
                    <span>প্রতিষ্ঠান ম্যানেজমেন্ট প্যানেল</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-auto"></i>
                </a>

                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition">
                    <i class="fa-solid fa-globe w-5"></i>
                    <span>মূল ওয়েবসাইট (Homepage)</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-auto"></i>
                </a>
            </nav>

            <!-- Bottom User Profile -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 border border-emerald-500 flex items-center justify-center font-bold text-emerald-400">
                        SA
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-white truncate">Super Admin</div>
                        <div class="text-xs text-slate-400 truncate">superadmin@eshikkha.com</div>
                    </div>
                    <a href="{{ route('logout') }}" title="লগআউট" class="text-slate-400 hover:text-rose-400 p-1.5 transition">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden bg-slate-900">
            <!-- Topbar -->
            <header class="h-16 bg-slate-950/80 backdrop-blur border-b border-slate-800 px-6 flex items-center justify-between z-10">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        সারাদেশে সিস্টেম সম্পূর্ণ স্বাভাবিক ও লাইভ
                    </span>
                    <span class="text-xs text-slate-400 hidden md:inline">
                        <i class="fa-solid fa-calendar-day mr-1"></i> {{ \Carbon\Carbon::now()->format('l, d F Y') }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('quick-login', ['role' => 'institution_admin']) }}" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3.5 py-1.5 rounded-lg flex items-center gap-2 transition">
                        <i class="fa-solid fa-arrow-right-arrow-left text-emerald-400"></i>
                        <span>প্রতিষ্ঠান প্যানেলে যান</span>
                    </a>
                    <a href="{{ route('super-admin.institutions') }}" class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white font-medium px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-md shadow-emerald-600/30 transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>নতুন প্রতিষ্ঠান যোগ</span>
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
