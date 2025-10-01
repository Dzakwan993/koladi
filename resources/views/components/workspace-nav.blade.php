<div class="h-12 bg-white shadow-sm flex items-center px-6 space-x-6 text-sm border-b border-gray-200">
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Ruang Kerja</span>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <a href="#" class="text-gray-700 hover:text-blue-600 font-medium transition">Div. Marketing</a>
    </div>

    <div class="flex-1"></div>

    <div class="flex items-center gap-4">
        @foreach (['insight','tugas','chat','dokumen','jadwal','pengumuman'] as $item)
            <a href="#" class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <img src="/images/icons/{{ $item }}.png" alt="{{ ucfirst($item) }}" class="w-4 h-4">
                <span>{{ ucfirst($item) }}</span>
            </a>
        @endforeach
    </div>
</div>
