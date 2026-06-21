@extends('theme::compact.admin.layouts.app')

@section('title', 'Categories')

@section('content')
    <h1 class="text-base md:text-lg font-bold text-gray-800 mb-1">Categories</h1>
    <p class="text-sm text-gray-500 mb-3">Organize your content with categories</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3">
            <h3 class="font-semibold text-gray-800 mb-2">New Category</h3>
            <form action="{{ url('/admin/categories') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Name *</label>
                    <input type="text" name="name" placeholder="e.g. Technology" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-shadow">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Slug</label>
                    <input type="text" name="slug" placeholder="Auto-generated" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-shadow">
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Optional description" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-shadow resize-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-sky-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-sky-700 transition-colors shadow-sm">Create Category</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Slug</th>
                            <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Posts</th>
                            <th class="text-right px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-4 font-medium text-gray-800">{{ $cat->name }}</td>
                                <td class="px-5 py-4 text-gray-500 text-xs font-mono">{{ $cat->slug }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">{{ $cat->posts_count }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <form action="{{ url('/admin/categories/' . $cat->id) }}" method="POST" class="inline-flex gap-1.5 items-center flex-wrap justify-end">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $cat->name }}" class="border border-gray-200 rounded-lg px-2 py-1 text-xs w-20 focus:ring-2 focus:ring-sky-500 outline-none">
                                        <input type="text" name="slug" value="{{ $cat->slug }}" class="border border-gray-200 rounded-lg px-2 py-1 text-xs w-20 focus:ring-2 focus:ring-sky-500 outline-none" placeholder="slug">
                                        <button type="submit" class="text-sky-600 hover:text-sky-700 text-xs font-medium">Update</button>
                                    </form>
                                    <form action="{{ url('/admin/categories/' . $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-600 text-xs font-medium ml-2">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($categories->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <p class="text-sm font-medium">No categories</p>
                </div>
            @endif
        </div>
    </div>
    @if($categories->hasPages())
        <div class="mt-3">{{ $categories->links() }}</div>
    @endif
@endsection
