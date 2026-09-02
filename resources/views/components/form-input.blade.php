@props([
    'name',
    'type' => 'text',
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'id' => null,
    'autocomplete' => null,
    'icon' => null,        // SVG path string or null
    'showToggle' => false, // True for password fields needing show/hide
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
    $inputValue = old($name, $value);
    $hasPadLeft  = $icon !== null;
    $hasPadRight = $showToggle;
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-[#374151] mb-1.5">
            {{ $label }}
            @if ($required) <span class="text-[#F97316]">*</span> @endif
        </label>
    @endif

    <div
        @if($showToggle) x-data="{ show: false }" @endif
        class="relative"
    >
        {{-- Left Icon --}}
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-400">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </span>
        @endif

        {{-- Input --}}
        <input
            :type="@if($showToggle) show ? 'text' : '{{ $type }}' @else '{{ $type }}' @endif"
            @if(!$showToggle) type="{{ $type }}" @endif
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $inputValue }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            class="w-full {{ $hasPadLeft ? 'pl-10' : 'pl-4' }} {{ $hasPadRight ? 'pr-10' : 'pr-4' }} py-3 bg-white border {{ $hasError ? 'border-red-500 focus:ring-red-200' : 'border-[#D1D5DB] hover:border-neutral-400 focus:border-[#F97316] focus:ring-orange-100' }} rounded-lg text-[#111111] placeholder:text-neutral-400 text-sm focus:outline-none focus:ring-4 transition-all duration-200"
        />

        {{-- Toggle Eye (password) --}}
        @if ($showToggle)
            <button
                type="button"
                @click="show = !show"
                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-neutral-400 hover:text-[#111111] transition-colors focus:outline-none"
            >
                <svg x-show="!show" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <svg x-show="show" class="w-4.5 h-4.5" style="display:none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908A9.97 9.97 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411M3 3l18 18"/>
                </svg>
            </button>
        @endif
    </div>

    {{-- Error Message --}}
    @error($name)
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
