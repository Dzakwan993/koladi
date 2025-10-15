@props([
    'maxHeight' => 'h-full'
])

<div class="shadow-lg bg-gradient-to-b from-blue-100 to-blue-100 rounded-xl p-3 {{ $maxHeight }} overflow-y-auto scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-100">
    {{ $slot }}
</div>