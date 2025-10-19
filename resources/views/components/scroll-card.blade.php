@props([
    'maxHeight' => 'h-full'
])

<div class="relative {{ $maxHeight }}">
    <div class="shadow-lg bg-[#BBCFF9] rounded-xl p-3 h-full overflow-y-auto scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-100">
        {{ $slot }}
    </div>
</div>