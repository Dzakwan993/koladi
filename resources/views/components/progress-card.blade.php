@props([
    'title' => 'Div. Marketing',
    'description' => 'Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan',
    'percentage' => 80,
    'members' => [],
    'additionalCount' => 0
])

<div class="bg-white rounded-lg p-3 shadow-md hover:shadow-lg transition-shadow duration-300">
    <div class="flex items-start justify-between gap-4">
        {{-- Left Side: Content --}}
        <div class="flex-1">
            {{-- Title --}}
            <p class="text-xl font-bold text-gray-900 mb-1 leading-tight">{{ $title }}</p>
            
            {{-- Description --}}
            <p class="text-gray-500 text-sm leading-relaxed mb-1">{{ $description }}</p>
            
            {{-- Members Section --}}
            <div class="flex items-center">
                {{-- Avatar Stack --}}
                <div class="flex -space-x-2">
                    @foreach($members as $index => $member)
                        @if($index < 3)
                            <div class="relative">
                                <img 
                                    src="{{ $member['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=random&size=32' }}" 
                                    alt="{{ $member['name'] }}"
                                    class="w-6 h-6 rounded-full border-2 border-white object-cover  ring-gray-100"
                                />
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Member Names --}}
                <div class="ml-4">
                    <span class="text-gray-800 text-sm">
                        <span class="font-semibold">{{ $members[0]['name'] ?? 'Sahroni' }}</span>
                        @if($additionalCount > 0)
                            <span class="text-gray-500"> dan {{ $additionalCount }} lainnya</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Right Side: Circular Progress --}}
<div class="flex flex-col items-center">
    {{-- Label --}}
    <div class="text-center mb-2">
        <span class="text-sm font-medium text-gray-500 tracking-wide">Persentase</span>
    </div>

    {{-- Circular Progress Bar --}}
<div class="relative w-14 h-14 flex items-center justify-center">
    <svg class="absolute inset-0 w-full h-full transform -rotate-90">
        <circle
            cx="50%"
            cy="50%"
            r="22"
            stroke="#E0E7FF"
            stroke-width="5"
            fill="none"
        />
        <circle
            cx="50%"
            cy="50%"
            r="22"  
            stroke="#2563EB"
            stroke-width="5" 
            fill="none"
            stroke-linecap="round"
            stroke-dasharray="{{ 2 * 3.14159 * 22 }}" 
            stroke-dashoffset="{{ 2 * 3.14159 * 22 * (1 - $percentage / 100) }}"  
            style="transition: stroke-dashoffset 1s cubic-bezier(0.4, 0, 0.2, 1)"
        />
    </svg>

    {{-- Percentage Text --}}
    <span class="text-sm font-bold text-gray-900 absolute">{{ $percentage }}%</span>
</div>



        </div>
    </div>
</div>

<style>
    /* Optional: Add animation on load */
    @keyframes progressAnimation {
        from {
            stroke-dashoffset: {{ 2 * 3.14159 * 54 }};
        }
        to {
            stroke-dashoffset: {{ 2 * 3.14159 * 54 * (1 - $percentage / 100) }};
        }
    }
</style>