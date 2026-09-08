@extends('layouts.app')

@section('content')
<div class="bg-[#F8F8F6] min-h-screen py-8 md:py-12">
    <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
        
        <!-- Page Header -->
        <div class="mb-8 md:mb-12">
            <!-- Back Button -->
            <a href="{{ route('home') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white text-[#111111] border border-gray-200 rounded-xl font-medium text-sm hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 shadow-sm mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>

            <!-- Header Typography -->
            <h1 class="text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight mb-3">
                Lokasi Build n Fix
            </h1>
            <p class="text-gray-600 text-base md:text-lg max-w-2xl font-normal leading-relaxed">
                Temukan cabang Build n Fix terdekat untuk memenuhi kebutuhan material bangunan Anda.
            </p>
        </div>

        <!-- Location Cards Grid (Mobile: 1, Tablet: 2, Desktop: 3) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @foreach($locations as $location)
                <x-location-card
                    :name="$location['name']"
                    :address="$location['address']"
                    :image="$location['image']"
                    :product-url="Route::has('categories.index') ? route('categories.index', ['branches' => [$location['name']]]) : route('products.index', ['branches' => [$location['name']]])"
                />
            @endforeach
        </div>

    </div>
</div>
@endsection
