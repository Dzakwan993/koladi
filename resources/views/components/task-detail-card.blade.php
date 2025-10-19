@props([
    'time' => 'Kamis, 18 Feb 2025 11:12 AM - Kamis, 18 Feb 2025 01:00 PM',
    'title' => 'Div. Marketing',
    'description' => 'Rapat pengadaan alat yang di butuhkan dan revisi fonder agritment sebersar 50% dan juga',
    'members' => [],
    'additionalCount' => 0
])

<div class="bg-white rounded-lg p-4 shadow-sm">
    {{-- Meeting Header --}}
    <div class="bg-[#6D96F2] text-white px-3 py-2 rounded-lg mb-1.5 text-xs items-center gap-2 inline-flex">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ $time }}</span>
    </div>

    {{-- Title --}}
    <h3 class="text-lg font-bold text-[#1E1E1E] mb-1">{{ $title }}</h3>

    {{-- Description --}}
    <p class="text-sm font-medium text-[#6B7280] mb-4 leading-relaxed">
        {{ $description }}
    </p>

    {{-- Members and Button --}}
    <div class="flex items-center justify-between mb-1">
        <div class="flex items-center gap-3">
            {{-- Avatar Stack --}}
            <div class="flex -space-x-2">
                @foreach($members as $index => $member)
                    @if($index < 3)
                        <img 
                            src="{{ $member['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=random&size=32' }}" 
                            alt="{{ $member['name'] }}"
                            class="w-7 h-7 rounded-full border-2 border-white object-cover" 
                        />
                    @endif
                @endforeach
            </div>
            
            {{-- Names --}}
            <span class="text-sm text-gray-700">
                <span class="font-semibold">{{ $members[0]['name'] ?? 'Sahroni' }}</span>
                @if($additionalCount > 0)
                    <span class="text-gray-500"> dan {{ $additionalCount }} lainnya</span>
                @endif
            </span>
        </div>

        {{-- Join Button --}}
        <button class="bg-[#225AD6] hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1">
            <img src="{{ asset('images/icons/Zoom-icon.svg') }}" alt="Zoom" class="w-6 h-6" />
            Gabung rapat
        </button>
    </div>
</div>