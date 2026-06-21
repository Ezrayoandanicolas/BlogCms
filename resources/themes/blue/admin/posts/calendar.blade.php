@extends('theme::blue.admin.layouts.app')

@section('title', 'Calendar - Posts')

@section('content')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Post Calendar</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->setDate($year, $month, 1)->format('F Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/admin/posts/calendar?month=' . ($month == 1 ? 12 : $month - 1) . '&year=' . ($month == 1 ? $year - 1 : $year)) }}" class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Previous
            </a>
            <a href="{{ url('/admin/posts/calendar') }}" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Today</a>
            <a href="{{ url('/admin/posts/calendar?month=' . ($month == 12 ? 1 : $month + 1) . '&year=' . ($month == 12 ? $year + 1 : $year)) }}" class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                Next
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="grid grid-cols-7 border-b border-gray-100">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center bg-gray-50/50">{{ $day }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @php
                $today = now()->format('Y-m-d');
            @endphp

            {{-- Empty cells before first day --}}
            @for($i = 0; $i < $firstDayOfWeek; $i++)
                <div class="min-h-[100px] p-2 border-b border-r border-gray-50 bg-gray-50/30"></div>
            @endfor

            {{-- Day cells --}}
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr = now()->setDate($year, $month, $day)->format('Y-m-d');
                    $dayPosts = $posts[$dateStr] ?? collect();
                    $isToday = $dateStr === $today;
                    $isPast = now()->setDate($year, $month, $day)->isPast();
                    $isFuture = now()->setDate($year, $month, $day)->isFuture();
                @endphp
                <div class="min-h-[100px] p-2 border-b border-r border-gray-50 {{ $isToday ? 'bg-blue-50' : ($isPast ? 'bg-white' : 'bg-amber-50/30') }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold {{ $isToday ? 'text-cyan-600' : 'text-gray-700' }}">{{ $day }}</span>
                        @if($dayPosts->count() > 0)
                            <span class="text-[10px] font-medium text-white {{ $isFuture ? 'bg-amber-500' : 'bg-cyan-500' }} px-1.5 py-0.5 rounded-full">{{ $dayPosts->count() }}</span>
                        @endif
                    </div>
                    <div class="space-y-0.5 max-h-20 overflow-y-auto">
                        @foreach($dayPosts as $post)
                            <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}"
                               class="block text-[10px] truncate rounded px-1 py-0.5 {{ $post->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} hover:bg-opacity-80">
                                {{ $post->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endfor

            {{-- Empty cells after last day --}}
            @php
                $totalCells = $firstDayOfWeek + $daysInMonth;
                $remainder = 7 - ($totalCells % 7);
                if ($remainder < 7) {
                    for($i = 0; $i < $remainder; $i++) {
                        echo '<div class="min-h-[100px] p-2 border-b border-r border-gray-50 bg-gray-50/30"></div>';
                    }
                }
            @endphp
        </div>
    </div>
@endsection
