@extends('theme::default.layouts.app')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
    <div class="text-center py-20">
        <h1 class="text-6xl font-bold text-gray-300 mb-4">404</h1>
        <h2 class="text-2xl font-semibold mb-4">Halaman Tidak Ditemukan</h2>
        <p class="text-gray-500 mb-8">Halaman yang Anda cari mungkin telah dipindahkan atau dihapus.</p>
        <a href="{{ url('/') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            Kembali ke Beranda
        </a>
    </div>
@endsection
