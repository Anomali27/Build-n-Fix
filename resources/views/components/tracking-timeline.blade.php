@props([
    'timeline' => [],
    'fulfillmentMethod' => 'pickup',
    'branchName' => 'Serdam',
    'deliveryAddress' => null,
])

<div class="space-y-5">
    <div class="relative pl-6 border-l-2 border-gray-200 space-y-5">
        @foreach($timeline as $step)
            @php
                $isCompleted = $step['completed'] ?? false;
                $isCurrent = $step['current'] ?? false;
            @endphp
            <div class="relative group">
                <!-- Dot icon on line -->
                <div class="absolute -left-[31px] top-0.5 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all {{ $isCurrent ? 'bg-[#F97316] text-white ring-4 ring-orange-100 shadow-md' : ($isCompleted ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-400') }}">
                    @if($isCompleted && !$isCurrent)
                        ✓
                    @else
                        {{ $loop->iteration }}
                    @endif
                </div>

                <div class="bg-gray-50/80 border border-gray-100 rounded-xl p-3.5 transition-all {{ $isCurrent ? 'bg-orange-50/40 border-orange-200 ring-1 ring-orange-200/50' : '' }}">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs font-extrabold {{ $isCurrent ? 'text-[#F97316]' : ($isCompleted ? 'text-gray-900' : 'text-gray-400') }}">
                            {{ $step['label'] }}
                        </h4>
                        @if($isCurrent)
                            <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-[#F97316] text-white shrink-0">Status Saat Ini</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                        {{ $step['description'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Fulfillment info notice box -->
    @if(strtolower($fulfillmentMethod) === 'pickup')
        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 font-bold text-sm">
                🏪
            </div>
            <div>
                <h5 class="text-xs font-extrabold text-amber-900">Lokasi Pengambilan (Cabang {{ $branchName }})</h5>
                <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                    Pesanan dapat diambil langsung di toko Build n Fix cabang <strong>{{ $branchName }}</strong> setelah status berubah menjadi <span class="font-bold text-[#F97316]">Ready to Pick Up</span>.
                </p>
            </div>
        </div>
    @else
        <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200/80 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 font-bold text-sm">
                🚚
            </div>
            <div>
                <h5 class="text-xs font-extrabold text-blue-900">Alamat Pengiriman</h5>
                <p class="text-[11px] text-blue-700 mt-0.5 leading-relaxed">
                    {{ $deliveryAddress ?? 'Jl. Ahmad Yani No. 123, Pontianak' }}
                </p>
            </div>
        </div>
    @endif
</div>
