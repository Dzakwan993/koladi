@extends('layouts.app')

@section('title', 'Video Tutorial')

@section('content')
<div class="bg-[#f3f6fc] min-h-screen">

    {{-- ================================================ --}}
    {{-- HEADER SECTION --}}
    {{-- ================================================ --}}
    <div class="bg-white border-b border-gray-100 px-8 py-12">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#e9effd]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                        stroke="#225ad6" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
                    </svg>
                </span>
                <span class="text-sm font-medium text-[#225ad6]">Video Tutorial</span>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Belajar Pakai Koladi Lebih Cepat 🚀
            </h1>
            <p class="text-gray-600 text-sm leading-relaxed mb-1">
                Mulai dari setup awal sampai koordinasi tim, semua bisa kamu pelajari lewat video singkat di bawah ini.
            </p>
            <p class="text-gray-500 text-sm leading-relaxed mb-1">
                Ikuti urutannya supaya kamu bisa langsung menggunakan Koladi dengan lancar bersama timmu — tanpa bingung, tanpa ribet.
            </p>
            <p class="text-gray-500 text-sm leading-relaxed">
                Tonton satu per satu, dan dalam beberapa menit kamu sudah siap kerja pakai Koladi.
            </p>
        </div>
    </div>

    {{-- ================================================ --}}
    {{-- VIDEO GRID --}}
    {{-- ================================================ --}}
    <div class="px-8 py-10 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($videos as $index => $video)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col
                            hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">

                    {{-- Video Preview with Custom Thumbnail Overlay --}}
                    <div class="relative w-full group cursor-pointer" style="aspect-ratio: 16/9;"
                         x-data="{ isPlaying: false }">

                        <!-- {{-- Step badge --}}
                        <div class="absolute top-3 left-3 z-20 bg-[#225ad6] text-white text-[11px] font-bold
                                    px-2.5 py-0.5 rounded-full leading-5 shadow-sm">
                            {{ $index + 1 }}
                        </div> -->

                        {{-- Static Preview State --}}
                        <template x-if="!isPlaying">
                            <div class="absolute inset-0 z-10 overflow-hidden" @click="isPlaying = true">
                                {{-- Thumbnail --}}
                                <img src="https://img.youtube.com/vi/{{ $video['youtube_id'] }}/hqdefault.jpg"
                                     alt="{{ $video['title'] }}"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">

                                {{-- Dark Overlay --}}
                                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors duration-300"></div>

                                {{-- Custom Play Button --}}
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center shadow-xl
                                                group-hover:bg-[#225ad6] group-hover:scale-110 transition-all duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                             class="w-8 h-8 text-[#225ad6] group-hover:text-white transition-colors ml-1">
                                            <path fill-rule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Actual Video State (Iframe) --}}
                        <template x-if="isPlaying">
                            <iframe
                                class="w-full h-full absolute inset-0 z-10"
                                :src="`https://www.youtube.com/embed/{{ $video['youtube_id'] }}?autoplay=1&modestbranding=1&rel=0&iv_load_policy=3`"
                                title="{{ $video['title'] }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen>
                            </iframe>
                        </template>
                    </div>

                    {{-- Card Content --}}
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-base font-semibold text-gray-900 mb-2 leading-snug">
                            {{ $video['title'] }}
                        </h3>
                        <p class="text-sm text-gray-500 leading-relaxed flex-1">
                            {{ $video['desc'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
