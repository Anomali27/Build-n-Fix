@props(['title', 'resetUrl' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4']) }}>
    @if(isset($title) && $title)
        <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100 flex items-center justify-between">
            <span>{{ $title }}</span>
            @if($resetUrl)
                <a href="{{ $resetUrl }}" class="text-[11px] text-[#F97316] font-semibold hover:underline">Reset</a>
            @endif
        </h3>
    @endif
    
    <div>
        {{ $slot }}
    </div>
</div>
