@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<div class="bg-[#f3f6fc]">
    {{-- Workspace Nav (menempel ke topbar) --}}
    
        <div class="flex-1"></div>
        
        
    </div>

    {{-- Grid Workspace --}}
    <div class="p-8 grid grid-cols-3 gap-6 max-w-6xl mx-auto">
        {{-- Card Tugas --}}
        <a href="#" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group">
            <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="text-gray-700 font-extrabold text-2xl">INI BAKAL JADI TAMPILAN DASHBOARD</span>
        </a>

      

       
    </div>
</div>
@endsection
