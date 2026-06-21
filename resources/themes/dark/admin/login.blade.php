@extends('theme::dark.admin.layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="bg-slate-800 rounded-2xl shadow-none-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-100">{{ config('app.name') }}</h1>
            <p class="text-sm text-slate-400 mt-1">Sign in to your admin account</p>
        </div>

        <form action="{{ url('/admin/login') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-200 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-shadow-none"
                       placeholder="admin@example.com">
                @error('email')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-200 mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-shadow-none"
                       placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
                @error('password')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember"
                       class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                <label for="remember" class="text-sm text-slate-300">Remember me</label>
            </div>

            <button type="submit"
                    class="w-full bg-purple-600 text-white py-2.5 rounded-xl hover:bg-purple-700 text-sm font-medium transition-colors shadow-none-sm">
                Sign In
            </button>
        </form>
    </div>
@endsection
