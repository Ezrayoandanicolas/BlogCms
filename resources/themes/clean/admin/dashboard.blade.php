@extends('theme::clean.admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                <span class="text-xs font-medium text-teal-600 bg-blue-50 px-2 py-0.5 rounded-full">{{ $stats['published'] }} published</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['posts'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Posts</p>
            @if($stats['drafts'] > 0)
                <div class="mt-2 text-xs text-gray-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    {{ $stats['drafts'] }} drafts
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">{{ number_format($totalViews) }} total</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $postsThisWeek }}</p>
            <p class="text-sm text-gray-500 mt-1">Posts This Week</p>
            @if($postsThisWeek > 0)
                <div class="mt-2 text-xs text-emerald-500 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Active this week
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['categories'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Categories</p>
            <div class="mt-2 text-xs text-gray-400">{{ $stats['tags'] }} tags</div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                @if($stats['pending_comments'] > 0)
                    <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">{{ $stats['pending_comments'] }} pending</span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['comments'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Comments</p>
            <a href="{{ url('/admin/comments') }}" class="mt-2 inline-block text-xs text-orange-600 hover:text-orange-700 font-medium">Review &rarr;</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Posts — Last 30 Days</h3>
            <div x-data="chart()" x-init="init()" class="relative">
                <svg viewBox="0 0 900 200" class="w-full h-48" preserveAspectRatio="none">
                    <polyline
                        :points="points"
                        fill="none"
                        stroke="#3b82f6"
                        stroke-width="2"
                        class="transition-all duration-500"
                    />
                    <polygon
                        :points="fillPoints"
                        fill="url(#grad)"
                        class="transition-all duration-500"
                    />
                    <defs>
                        <linearGradient id="grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.2"/>
                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="flex justify-between mt-2 text-[10px] text-gray-400 px-1">
                    <template x-for="(label, i) in labels" :key="i">
                        <span x-show="i % 5 === 0 || i === labels.length - 1" x-text="label" class="truncate"></span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Per-Domain Stats -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Per Domain</h3>
            <div class="space-y-4">
                @foreach($domainStats as $ds)
                    <div class="flex items-center justify-between p-3 rounded-xl {{ $loop->first ? 'bg-blue-50' : 'bg-gray-50' }}">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $ds['domain'] }}</p>
                            <p class="text-xs text-gray-500">{{ $ds['posts'] }} posts &middot; {{ number_format($ds['views']) }} views</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">{{ $ds['comments'] }} comments</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ url('/admin/posts/create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 text-teal-700 hover:bg-teal-100 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Post
                </a>
                <a href="{{ url('/admin/posts') }}" class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    View Posts
                </a>
                <a href="{{ url('/admin/comments?status=pending') }}" class="flex items-center gap-3 p-3 rounded-xl bg-orange-50 text-orange-700 hover:bg-orange-100 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Moderate Comments
                </a>
                <a href="{{ url('/admin/settings') }}" class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Content Summary</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">Published Posts</span>
                        <span class="font-semibold text-gray-900">{{ $stats['published'] }} / {{ $stats['posts'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @php $pct = $stats['posts'] > 0 ? round(($stats['published'] / $stats['posts']) * 100) : 0; @endphp
                        <div class="bg-teal-600 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">Active Comments</span>
                        <span class="font-semibold text-gray-900">{{ $counts['approved'] ?? 0 }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @php $cct = $stats['comments'] > 0 ? round((($counts['approved'] ?? 0) / max($stats['comments'], 1)) * 100) : 0; @endphp
                        <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $cct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function chart() {
        return {
            labels: @json($chartLabels),
            values: @json($chartValues),
            get maxVal() { return Math.max(...this.values, 1) },
            get points() {
                if (!this.values.length) return ''
                const w = 900, h = 200
                const step = w / (this.values.length - 1)
                return this.values.map((v, i) => `${i * step},${h - (v / this.maxVal) * h * 0.85 - 10}`).join(' ')
            },
            get fillPoints() {
                if (!this.values.length) return ''
                const pts = this.points
                const last = this.values.length - 1
                return `0,200 ${pts} ${last * (900/(this.values.length-1))},200`
            }
        }
    }
</script>
@endpush
