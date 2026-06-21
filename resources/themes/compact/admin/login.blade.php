@extends('theme::compact.admin.layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-4">
            <h1 class="text-lg font-bold text-gray-800">{{ config('app.name') }}</h1>
            <p class="text-sm text-gray-500 mt-1">Sign in to your admin account</p>
        </div>

        <form action="{{ url('/admin/login') }}" method="POST">
            @csrf

            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-shadow"
                       placeholder="admin@example.com">
                @error('email')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-shadow"
                       placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
                @error('password')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div class="mb-3 flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember"
                       class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                <label for="remember" class="text-sm text-gray-600">Remember me</label>
            </div>

            <button type="submit"
                    class="w-full bg-sky-600 text-white py-2.5 rounded-xl hover:bg-sky-700 text-sm font-medium transition-colors shadow-sm">
                Sign In
            </button>
        </form>
    </div>
@endsection
