@extends('theme::dark.layouts.app')

@section('title', 'Contact - ' . config('app.name'))

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Contact Us</h1>
        <p class="text-slate-400 mb-8">Have a question or suggestion? Send us a message.</p>

        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ url('/contact') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-200 mb-1">Name</label>
                <input type="text" name="name" required class="w-full border border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-200 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-200 mb-1">Message</label>
                <textarea name="message" rows="5" required class="w-full border border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 outline-none resize-none"></textarea>
            </div>
            <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-purple-700 transition">Send Message</button>
        </form>
    </div>
@endsection
