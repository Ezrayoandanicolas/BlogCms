@extends('theme::compact.admin.layouts.app')

@section('title', 'Posts')

@section('content')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-3">
        <div>
            <h1 class="text-base md:text-lg font-bold text-gray-800">Posts</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your blog posts</p>
        </div>
        <a href="{{ url('/admin/posts/create') }}" class="inline-flex items-center gap-2 bg-sky-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-sky-700 transition-colors shadow-sm whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Post
        </a>
    </div>

    <form id="bulkForm" method="POST" action="{{ url('/admin/posts/bulk') }}">
        @csrf
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                <select name="action" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    <option value="">Bulk Action</option>
                    <option value="publish">Publish</option>
                    <option value="draft">Draft</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="text-sm bg-sky-600 text-white px-3 py-1.5 rounded-lg hover:bg-sky-700 transition disabled:opacity-50" id="applyBtn" disabled>Apply</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[600px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="w-10 px-3 py-3.5"></th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Title</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider hidden sm:table-cell">Author</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider hidden md:table-cell">Category</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider hidden sm:table-cell">Date</th>
                            <th class="text-right px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($posts as $post)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-3 py-4 text-center">
                                    <input type="checkbox" name="ids[]" value="{{ $post->id }}" class="post-checkbox rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ url('/blog/' . $post->slug) }}" class="text-sky-600 hover:text-sky-700 font-medium" target="_blank">{{ $post->title }}</a>
                                </td>
                                <td class="px-5 py-4 text-gray-500 hidden sm:table-cell">{{ $post->user?->name }}</td>
                                <td class="px-5 py-4 hidden md:table-cell">
                                    @if($post->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $post->category->name }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($post->status === 'published')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs hidden sm:table-cell">
                                    @if($post->published_at)
                                        @if($post->published_at->isFuture())
                                            <span class="text-amber-600 font-medium">{{ $post->published_at->format('M d, Y H:i') }}</span>
                                            <span class="text-amber-500 text-[10px] ml-1">(Scheduled)</span>
                                        @else
                                            <span class="text-gray-500">{{ $post->published_at->format('M d, Y H:i') }}</span>
                                        @endif
                                    @elseif($post->status === 'draft')
                                        <span class="text-gray-300">—</span>
                                    @else
                                        <span class="text-gray-500">{{ $post->created_at->format('M d, Y') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="inline-flex items-center gap-1 text-sky-600 hover:text-sky-700 text-xs font-medium mr-3">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>
                                    <form action="{{ url('/admin/posts/' . $post->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this post?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 text-red-500 hover:text-red-600 text-xs font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($posts->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <p class="text-sm font-medium">No posts yet</p>
                    <p class="text-xs mt-1">Create your first post to get started.</p>
                </div>
            @endif
        </div>
    </form>

    @if($posts->hasPages())
        <div class="mt-3">{{ $posts->links() }}</div>
    @endif
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.post-checkbox').forEach(cb => cb.checked = this.checked);
        toggleApply();
    });
    document.querySelectorAll('.post-checkbox').forEach(cb => {
        cb.addEventListener('change', toggleApply);
    });
    function toggleApply() {
        const checked = document.querySelectorAll('.post-checkbox:checked').length;
        const action = document.querySelector('[name="action"]').value;
        document.getElementById('applyBtn').disabled = !(checked && action);
    }
    document.querySelector('[name="action"]')?.addEventListener('change', toggleApply);
</script>
@endpush
