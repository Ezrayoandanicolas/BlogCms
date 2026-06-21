@extends('theme::compact.layouts.app')

@section('title', 'About - ' . config('app.name'))

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-3">About {{ config('app.name') }}</h1>
        <div class="prose prose-lg max-w-none">
            <p>Welcome to {{ config('app.name') }}! We are dedicated to providing high-quality content about {{ config('app.site_topic', 'various topics') }}.</p>
            <p>Our mission is to deliver informative, engaging, and valuable articles that help our readers stay informed and inspired.</p>
            <h2>Our Story</h2>
            <p>Founded with a passion for sharing knowledge, {{ config('app.name') }} has grown into a trusted source of information for our readers.</p>
            <h2>Contact Us</h2>
            <p>Have questions or suggestions? Feel free to <a href="{{ url('/contact') }}">contact us</a>.</p>
        </div>
    </div>
@endsection
