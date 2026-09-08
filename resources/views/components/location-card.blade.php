@props([
    'name',
    'address',
    'image',
    'productUrl' => null,
])

@php
    $imageUrl = \Illuminate\Support\Str::startsWith($image, ['http://', 'https://']) 
        ? $image 
        : asset($image);
    $targetUrl = $productUrl ?? (Route::has('categories.index') ? route('categories.index') : route('products.index'));
@endphp

<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden hover:shadow-md hover:border-gray-300 transition-all duration-200 flex flex-col h-full">
    <!-- Location Photo (16:9 ratio) -->
    <div class="aspect-video w-full overflow-hidden bg-gray-100">
        <img src="{{ $imageUrl }}" 
             alt="Cabang {{ $name }}" 
             class="w-full h-full object-cover" 
             loading="lazy">
    </div>

    <!-- Content -->
    <div class="p-6 flex flex-col flex-grow justify-between">
        <div class="space-y-2 mb-6">
            <!-- Branch Name -->
            <h3 class="text-2xl font-bold text-[#111111]">
                {{ $name }}
            </h3>

            <!-- Complete Address -->
            <p class="text-gray-600 text-sm leading-relaxed">
                {{ $address }}
            </p>
        </div>

        <!-- CTA Button -->
        <a href="{{ $targetUrl }}" 
           class="w-full py-3 px-4 bg-[#F97316] hover:bg-orange-600 text-white rounded-xl font-semibold text-center transition duration-200 block shadow-sm">
            Belanja Sekarang
        </a>
    </div>
</div>
