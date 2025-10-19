@props([
    'title' => 'Div. Marketing',
    'description' => 'Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan',
    'percentage' => 80,
    'members' => [],
    'additionalCount' => 0
])

<div class="bg-white rounded-lg p-2.5 sm:p-3 shadow-md hover:shadow-lg transition-shadow duration-300 font-[Inter,sans-serif]">
    <div class="flex flex-col sm:flex-row items-start justify-between gap-3 sm:gap-4">
        {{-- Left Side: Content --}}
        <div class="flex-1 w-full sm:w-auto min-w-0">
            {{-- Title --}}
            <p class="text-base sm:text-lg md:text-xl font-bold text-[#1E1E1E] mb-1 sm:mb-1.5 leading-tight">{{ $title }}</p>
            
            {{-- Description --}}
            <p class="text-[#6B7280] text-xs sm:text-sm font-medium leading-relaxed mb-1 sm:mb-1.5">{{ $description }}</p>
            
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
                                    class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white object-cover ring-gray-100"
                                />
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Member Names --}}
                <div class="ml-2">
                    <span class="text-[#1E1E1E] text-xs sm:text-sm">
                        <span class="font-bold">{{ $members[0]['name'] ?? 'Sahroni' }}</span>
                        @if($additionalCount > 0)
                            <span class="text-[#6B7280] font-medium"> dan {{ $additionalCount }} lainnya</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Right Side: Circular Progress --}}
        <div class="flex flex-col items-center w-full sm:w-auto">
            {{-- Label --}}
            <div class="text-center mb-1.5 sm:mb-2">
                <span class="text-xs sm:text-sm font-bold text-[#6B7280] tracking-wide">Persentase</span>
            </div>

            {{-- Circular Progress Bar --}}
            <div class="relative w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center">
                <svg class="absolute inset-0 w-full h-full transform -rotate-90">
                    <!-- Definisi Linear Gradient -->
                    <defs>
                        <linearGradient id="progressGradient-{{ $percentage }}" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:#2563EB;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#143681;stop-opacity:1" />
                        </linearGradient>
                    </defs>

                    <!-- Background Circle (Abu-abu) -->
                    <circle
                        cx="50%"
                        cy="50%"
                        r="20"
                        stroke="#E0E7FF"
                        stroke-width="4"
                        fill="none"
                        class="sm:stroke-[5]"
                        style="r: 20px;"
                    />
                    
                    <!-- Progress Circle (Dengan Gradient) -->
                    <circle
                        cx="50%"
                        cy="50%"
                        r="20"  
                        stroke="url(#progressGradient-{{ $percentage }})"
                        stroke-width="4" 
                        fill="none"
                        stroke-linecap="round"
                        stroke-dasharray="{{ 2 * 3.14159 * 20 }}" 
                        stroke-dashoffset="{{ 2 * 3.14159 * 20 * (1 - $percentage / 100) }}"  
                        style="transition: stroke-dashoffset 1s cubic-bezier(0.4, 0, 0.2, 1); r: 20px;"
                        class="sm:stroke-[5]"
                    />
                </svg>

                {{-- Percentage Text --}}
                <span class="text-xs sm:text-sm font-bold text-[#1E1E1E] absolute">{{ $percentage }}%</span>
            </div>
        </div>
    </div>
</div>