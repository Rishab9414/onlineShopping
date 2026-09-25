<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }} Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/admin.css'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="antialiased bg-amber-50/40" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-brand-dark transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="flex flex-col h-full">
                {{-- Logo --}}
                <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
                    <x-store-logo class="rounded-full bg-white" height="40px" maxWidth="40px" />
                    <div>
                        <p class="text-white font-bold text-sm leading-tight">{{ $store->name() }}</p>
                        <p class="text-rose-100/50 text-xs">Boutique Admin</p>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto" x-data="{ mastersOpen: {{ request()->routeIs('admin.masters.*') ? 'true' : 'false' }}, usersOpen: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}, marketingOpen: {{ request()->routeIs('admin.banners.*') || request()->routeIs('admin.home-themes.*') || request()->routeIs('admin.promo-popups.*') || request()->routeIs('admin.home-reels.*') || request()->routeIs('admin.coupons.*') || request()->routeIs('admin.announcements.*') || request()->routeIs('admin.blog.*') ? 'true' : 'false' }} }">
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2">Main</p>

                    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>

                    @if(auth()->user()->hasPermissionGroup('products'))
                    <a href="{{ route('admin.products.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Products
                    </a>
                    @endif

                    @if(auth()->user()->hasPermissionGroup('customers'))
                    <a href="{{ route('admin.customers.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Customers
                    </a>
                    @endif

                    @if(auth()->user()->hasPermissionGroup('masters'))
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2 mt-5">Phase 1 — Masters</p>

                    <button @click="mastersOpen = !mastersOpen" class="admin-sidebar-link w-full justify-between {{ request()->routeIs('admin.masters.*') ? 'text-white' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Master Data
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="mastersOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="mastersOpen" x-cloak class="ml-4 space-y-0.5 border-l border-slate-800 pl-3">
                        @foreach([
                            ['route' => 'admin.masters.categories.index', 'label' => 'Categories'],
                            ['route' => 'admin.masters.brands.index', 'label' => 'Brands'],
                            ['route' => 'admin.masters.manufacturers.index', 'label' => 'Manufacturers'],
                            ['route' => 'admin.masters.suppliers.index', 'label' => 'Suppliers'],
                            ['route' => 'admin.masters.taxes.index', 'label' => 'Tax / GST'],
                            ['route' => 'admin.masters.units.index', 'label' => 'Units'],
                            ['route' => 'admin.masters.sizes.index', 'label' => 'Sizes'],
                            ['route' => 'admin.masters.colors.index', 'label' => 'Colors'],
                            ['route' => 'admin.masters.materials.index', 'label' => 'Materials'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['route']) ? 'bg-white/15 text-white' : 'text-rose-100/60 hover:text-white hover:bg-white/10' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                    @endif

                    @if(auth()->user()->hasPermissionGroup('users'))
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2 mt-5">User Management</p>

                    <button @click="usersOpen = !usersOpen" class="admin-sidebar-link w-full justify-between {{ request()->routeIs('admin.users.*') ? 'text-white' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Users & Roles
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="usersOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="usersOpen" x-cloak class="ml-4 space-y-0.5 border-l border-slate-800 pl-3">
                        @foreach([
                            ['route' => 'admin.users.admin-users.index', 'label' => 'Admin Users'],
                            ['route' => 'admin.users.roles.index', 'label' => 'Roles'],
                            ['route' => 'admin.users.permissions.index', 'label' => 'Permissions'],
                            ['route' => 'admin.users.login-history.index', 'label' => 'Login History'],
                            ['route' => 'admin.users.activity-logs.index', 'label' => 'Activity Logs'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['route']) ? 'bg-white/15 text-white' : 'text-rose-100/60 hover:text-white hover:bg-white/10' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                    @endif

                    @if(auth()->user()->hasPermissionGroup('marketing'))
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2 mt-5">Marketing</p>
                    <button @click="marketingOpen = !marketingOpen" class="admin-sidebar-link w-full justify-between {{ request()->routeIs('admin.banners.*') || request()->routeIs('admin.home-themes.*') || request()->routeIs('admin.promo-popups.*') || request()->routeIs('admin.home-reels.*') || request()->routeIs('admin.coupons.*') || request()->routeIs('admin.announcements.*') || request()->routeIs('admin.blog.*') ? 'text-white' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            Marketing
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="marketingOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="marketingOpen" x-cloak class="ml-4 space-y-0.5 border-l border-slate-800 pl-3">
                        @foreach([
                            ['route' => 'admin.banners.index', 'match' => 'admin.banners.*', 'label' => 'Banners'],
                            ['route' => 'admin.home-themes.index', 'match' => 'admin.home-themes.*', 'label' => 'Home Themes'],
                            ['route' => 'admin.promo-popups.index', 'match' => 'admin.promo-popups.*', 'label' => 'Offer Popup'],
                            ['route' => 'admin.home-reels.index', 'match' => 'admin.home-reels.*', 'label' => 'Home Reels'],
                            ['route' => 'admin.coupons.index', 'match' => 'admin.coupons.*', 'label' => 'Coupons'],
                            ['route' => 'admin.announcements.index', 'match' => 'admin.announcements.*', 'label' => 'Announcements'],
                            ['route' => 'admin.blog.index', 'match' => 'admin.blog.*', 'label' => 'Blog'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['match']) ? 'bg-white/15 text-white' : 'text-rose-100/60 hover:text-white hover:bg-white/10' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                    @endif

                    @if(auth()->user()->hasPermissionGroup('orders'))
                    <a href="{{ route('admin.orders.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Orders
                    </a>
                    <a href="{{ route('admin.orders.sync-payments') }}" class="admin-sidebar-link ml-4 {{ request()->routeIs('admin.orders.sync-payments') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sync Payments
                    </a>
                    @endif

                    @if(auth()->user()->hasPermissionGroup('reports'))
                    <a href="{{ route('admin.reports.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Reports
                    </a>
                    @endif

                    @if(auth()->user()->hasPermissionGroup('settings'))
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2 mt-5">System</p>

                    <a href="{{ route('admin.settings.homepage') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.homepage') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        Homepage Settings
                    </a>

                    <a href="{{ route('admin.settings.store') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.store') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Store Profile
                    </a>

                    <a href="{{ route('admin.settings.payments') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.payments') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Payment Settings
                    </a>

                    <a href="{{ route('admin.settings.tax') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.tax') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Tax / GST Settings
                    </a>

                    @if(Route::has('admin.settings.maintenance'))
                    <a href="{{ route('admin.settings.maintenance') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.maintenance') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8"/></svg>
                        Maintenance
                    </a>
                    @endif
                    @endif

                    <a href="{{ route('home') }}" target="_blank" class="admin-sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        View Store
                    </a>
                </nav>

                {{-- User --}}
                <div class="p-4 border-t border-slate-800">
                    <div class="flex items-center gap-3 px-2 py-2">
                        <div class="w-9 h-9 bg-amber-200 text-brand-dark rounded-full flex items-center justify-center font-semibold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-xl transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-cloak></div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar --}}
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-slate-500 hidden sm:block">@yield('page-subtitle', 'Overview of your store performance')</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                            Store Online
                        </span>
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-slate-700">{{ now()->format('l, M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-6 overflow-auto">
                @if(session('success'))
                    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @vite(['resources/js/app.js', 'resources/js/admin-master.js'])
    @stack('vite')
    @stack('scripts')
</body>
</html>
