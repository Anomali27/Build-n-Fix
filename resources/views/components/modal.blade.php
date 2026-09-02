@props([
    'name' => 'modal',
    'show' => false,
    'title' => null,
    'maxWidth' => 'md',
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div
    x-data="{ open: @js($show) }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Overlay Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-[#111111]/60 backdrop-blur-xs transition-opacity"
    ></div>

    <!-- Modal Dialog Positioner -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full {{ $maxWidthClass }} transform overflow-hidden rounded-2xl bg-[#FFFFFF] p-6 text-left align-middle shadow-2xl transition-all border border-neutral-200"
        >
            <!-- Header -->
            @if($title || isset($header))
                <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-4">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <h3 class="text-lg font-bold text-[#111111] tracking-tight">
                            {{ $title }}
                        </h3>
                    @endif
                    <button 
                        type="button" 
                        @click="open = false"
                        class="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-[#111111] transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Content Body -->
            <div class="text-sm text-neutral-600 space-y-3">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if(isset($footer))
                <div class="mt-6 flex justify-end gap-3 border-t border-neutral-100 pt-4">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
