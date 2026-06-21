<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <aside class="lg:w-64 bg-gradient-to-b from-slate-800 to-slate-900 text-white shrink-0">
            <div class="p-5 border-b border-slate-700 flex items-center justify-between">
                <a href="{{ url('/admin') }}" class="text-lg font-bold tracking-tight">{{ config('app.name') }}</a>
                <button id="admin-menu-btn" class="lg:hidden p-1.5 rounded hover:bg-slate-700 text-slate-300" aria-label="Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <div class="p-4 border-b border-slate-700">
                <label class="text-xs text-slate-400 uppercase tracking-wider font-medium block mb-1.5">Website</label>
                <form action="{{ url('/admin/switch-domain/' . ($currentDomainId ?? '')) }}" method="POST" id="domain-switch-form">
                    @csrf
                    <select name="domain_id" onchange="this.form.action=this.form.action.replace(/\/\d+$/, '/' + this.value); this.form.submit();"
                            class="w-full bg-slate-700 text-white border border-slate-600 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        @foreach($adminDomains ?? [] as $d)
                            <option value="{{ $d->id }}" {{ ($currentDomainId ?? '') == $d->id ? 'selected' : '' }}>{{ $d->domain }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <nav id="admin-nav" class="hidden lg:block p-3 space-y-0.5">
                <a href="{{ url('/admin') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ url('/admin/posts') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/posts') && !request()->is('admin/posts/calendar*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    Posts
                </a>
                <a href="{{ url('/admin/posts/calendar') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/posts/calendar*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Calendar
                </a>
                <a href="{{ url('/admin/comments') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/comments*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Comments
                </a>
                <a href="{{ url('/admin/media') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/media*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Media
                </a>
                <a href="{{ url('/admin/categories') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/categories*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Categories
                </a>
                <a href="{{ url('/admin/tags') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/tags*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Tags
                </a>
                <a href="{{ url('/admin/settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/settings*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
                <a href="{{ url('/admin/api-docs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->is('admin/api-docs*') ? 'bg-slate-700 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    API Docs
                </a>

                <hr class="my-3 border-slate-700">

                @php
                    $viewSiteUrl = url('/');
                    if ($currentDomainId) {
                        $currentDomain = $adminDomains->firstWhere('id', $currentDomainId);
                        if ($currentDomain) {
                            $viewSiteUrl = 'http://' . $currentDomain->domain;
                        }
                    }
                @endphp
                <a href="{{ $viewSiteUrl }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:bg-slate-700/50 hover:text-white transition-colors" target="_blank">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View Site
                </a>
                <form action="{{ url('/admin/logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full text-left px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:bg-red-600/20 hover:text-red-400 transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </nav>
        </aside>

        <main class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b px-4 md:px-8 py-3 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                <div class="flex items-center gap-3 text-sm text-gray-500">
                    <span class="hidden sm:inline">{{ auth()->user()?->name ?? 'Guest' }}</span>
                    <span class="w-7 h-7 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr(auth()->user()?->name ?? 'G', 0, 1)) }}</span>
                </div>
            </header>

            <div class="flex-1 p-4 md:p-8 overflow-x-auto">
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.getElementById('admin-menu-btn')?.addEventListener('click', function() {
            document.getElementById('admin-nav').classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
