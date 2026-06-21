@extends('theme::clean.admin.layouts.app')

@section('title', 'Comments')

@section('content')
    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-1">Comments</h1>
    <p class="text-sm text-gray-500 mb-6">Review and moderate visitor comments</p>

    <div class="flex flex-wrap gap-2 mb-6 text-sm">
        <a href="{{ url('/admin/comments') }}" class="px-3.5 py-1.5 rounded-lg transition-colors {{ !$status ? 'bg-teal-600 text-white font-medium shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">All ({{ $counts['total'] }})</a>
        <a href="{{ url('/admin/comments?status=pending') }}" class="px-3.5 py-1.5 rounded-lg transition-colors {{ $status === 'pending' ? 'bg-orange-500 text-white font-medium shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">Pending ({{ $counts['pending'] }})</a>
        <a href="{{ url('/admin/comments?status=approved') }}" class="px-3.5 py-1.5 rounded-lg transition-colors {{ $status === 'approved' ? 'bg-emerald-600 text-white font-medium shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">Approved ({{ $counts['approved'] }})</a>
        <a href="{{ url('/admin/comments?status=rejected') }}" class="px-3.5 py-1.5 rounded-lg transition-colors {{ $status === 'rejected' ? 'bg-red-500 text-white font-medium shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">Rejected ({{ $counts['rejected'] }})</a>
        <a href="{{ url('/admin/comments?status=spam') }}" class="px-3.5 py-1.5 rounded-lg transition-colors {{ $status === 'spam' ? 'bg-red-600 text-white font-medium shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">Spam ({{ $counts['spam'] }})</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Author</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Comment</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Post</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider hidden sm:table-cell">Date</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($comments as $comment)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $comment->name }}</div>
                                <div class="text-xs text-gray-400">{{ $comment->email }}</div>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <p class="text-gray-700 truncate">{{ Str::limit($comment->content, 80) }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if($comment->post)
                                    <a href="{{ url('/blog/' . $comment->post->slug) }}" class="text-teal-600 hover:underline text-xs" target="_blank">{{ Str::limit($comment->post->title, 30) }}</a>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $colors = ['approved' => 'emerald', 'pending' => 'orange', 'rejected' => 'red', 'spam' => 'red'];
                                    $c = $colors[$comment->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-50 text-{{ $c }}-700 capitalize">
                                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $c }}-500"></span>
                                    {{ $comment->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-500 text-xs hidden sm:table-cell">{{ $comment->created_at->diffForHumans() }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($comment->status !== 'approved')
                                        <form action="{{ url('/admin/comments/' . $comment->id . '/approve') }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors" title="Approve">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($comment->status !== 'rejected')
                                        <form action="{{ url('/admin/comments/' . $comment->id . '/reject') }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Reject">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ url('/admin/comments/' . $comment->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-red-500 transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($comments->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-sm font-medium">No comments</p>
                <p class="text-xs mt-1">Comments from visitors will appear here.</p>
            </div>
        @endif
    </div>
    @if($comments->hasPages())
        <div class="mt-6">
            {{ $comments->links() }}
        </div>
    @endif
@endsection
