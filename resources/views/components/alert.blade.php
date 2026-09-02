@props([
    'type' => 'error',
    'title' => null,
    'message' => null,
    'dismissible' => true,
])

@php
    $styles = match($type) {
        'success' => [
            'wrap'  => 'bg-emerald-50 border-emerald-200 text-emerald-900',
            'icon'  => 'text-emerald-600',
            'svg'   => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
        ],
        'warning' => [
            'wrap'  => 'bg-amber-50 border-amber-200 text-amber-900',
            'icon'  => 'text-amber-600',
            'svg'   => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
        ],
        'info' => [
            'wrap'  => 'bg-blue-50 border-blue-200 text-blue-900',
            'icon'  => 'text-blue-600',
            'svg'   => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>',
        ],
        default => [
            'wrap'  => 'bg-red-50 border-red-200 text-red-900',
            'icon'  => 'text-red-600',
            'svg'   => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
        ],
    };
@endphp

<div
    x-data="{ visible: true }"
    x-show="visible"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    {{ $attributes->merge(['class' => "flex items-start gap-3 p-3.5 rounded-lg border text-sm {$styles['wrap']}"]) }}
>
    {{-- Icon --}}
    <svg class="w-4.5 h-4.5 mt-0.5 shrink-0 fill-current {{ $styles['icon'] }}" viewBox="0 0 20 20">
        {!! $styles['svg'] !!}
    </svg>

    {{-- Body --}}
    <div class="flex-1 leading-relaxed">
        @if ($title)
            <p class="font-semibold mb-0.5">{{ $title }}</p>
        @endif
        @if ($message)
            <p>{{ $message }}</p>
        @else
            {{ $slot }}
        @endif
    </div>

    {{-- Dismiss --}}
    @if ($dismissible)
        <button type="button" @click="visible = false"
            class="shrink-0 p-0.5 rounded hover:bg-black/10 transition-colors opacity-60 hover:opacity-100 focus:outline-none">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif
</div>
