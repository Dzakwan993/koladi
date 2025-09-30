@extends('layouts.app')

@section('title','kanban-tugas')

@section('content')

{{-- Workspace Nav --}}
@include('components.workspace-nav')

<div class="p-6 bg-gray-50 min-h-screen" x-data>
    <div class="grid grid-cols-4 gap-4" id="kanban-board">
        <!-- To Do -->
        <div class="bg-blue-100 rounded-xl p-3">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-700">To Do List</h2>
                <button class="text-gray-500 hover:text-gray-700">
                    <!-- Icon 3 titik horizontal -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </button>
            </div>
            
            <div id="todo" class="space-y-3 min-h-[200px]">
                @foreach(range(1,2) as $i)
                    @php
                        $members = range(1,4);
                        $progress = 25;
                    @endphp

                    <div class="bg-white p-3 rounded-lg shadow hover:shadow-md cursor-move border border-gray-200">
                        {{-- Header (Label + Tanggal) --}}
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold px-2 py-0.5 bg-green-100 text-green-700 rounded">Content</span>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-yellow-100 text-gray-700 rounded">24 Okt</span>
                        </div>

                        {{-- Judul --}}
                        <p class="text-sm font-medium text-gray-800">Tugas Content IG</p>

                        {{-- Info --}}
                        <div class="flex items-center space-x-4 text-gray-500 text-xs mt-2">
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                </svg>
                                <span>2</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>3</span>
                            </div>
                        </div>

                        {{-- Progress + Avatars --}}
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1">
                                <div class="w-full bg-gray-200 h-1.5 rounded-full mr-2">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $progress }}%</span>
                            </div>
                            <div class="flex mt-2 justify-end -space-x-2">
                                @foreach($members as $j)
                                    <img src="https://i.pravatar.cc/40?img={{ $j }}" 
                                         class="w-6 h-6 rounded-full border-2 border-white" 
                                         alt="avatar">
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Dikerjakan -->
        <div class="bg-blue-100 rounded-xl p-3">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-700">Dikerjakan</h2>
                <button class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </button>
            </div>
            <div id="inprogress" class="space-y-3 min-h-[200px]">
                @foreach(range(1,2) as $i)
                    @php
                        $members = range(1,3);
                        $progress = 60;
                    @endphp
                    <div class="bg-white p-3 rounded-lg shadow hover:shadow-md cursor-move border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold px-2 py-0.5 bg-purple-100 text-purple-700 rounded">Design</span>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-yellow-100 text-gray-700 rounded">25 Okt</span>
                        </div>
                        <p class="text-sm font-medium text-gray-800">Buat Banner Promo</p>
                        <div class="flex items-center space-x-4 text-gray-500 text-xs mt-2">
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                </svg>
                                <span>4</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>6</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1">
                                <div class="w-full bg-gray-200 h-1.5 rounded-full mr-2">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $progress }}%</span>
                            </div>
                            <div class="flex mt-2 justify-end -space-x-2">
                                @foreach($members as $j)
                                    <img src="https://i.pravatar.cc/40?img={{ $j }}" 
                                         class="w-6 h-6 rounded-full border-2 border-white" 
                                         alt="avatar">
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Selesai -->
        <div class="bg-blue-100 rounded-xl p-3">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-700">Selesai</h2>
                <button class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </button>
            </div>
            <div id="done" class="space-y-3 min-h-[200px]">
                @foreach(range(1,2) as $i)
                    @php
                        $members = range(1,2);
                        $progress = 100;
                    @endphp
                    <div class="bg-white p-3 rounded-lg shadow hover:shadow-md cursor-move border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold px-2 py-0.5 bg-blue-100 text-blue-700 rounded">Testing</span>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-yellow-100 text-gray-700 rounded">22 Okt</span>
                        </div>
                        <p class="text-sm font-medium text-gray-800">Testing Fitur Login</p>
                        <div class="flex items-center space-x-4 text-gray-500 text-xs mt-2">
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                </svg>
                                <span>1</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>2</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1">
                                <div class="w-full bg-gray-200 h-1.5 rounded-full mr-2">
                                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $progress }}%</span>
                            </div>
                            <div class="flex mt-2 justify-end -space-x-2">
                                @foreach($members as $j)
                                    <img src="https://i.pravatar.cc/40?img={{ $j }}" 
                                         class="w-6 h-6 rounded-full border-2 border-white" 
                                         alt="avatar">
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Batal -->
        <div class="bg-blue-100 rounded-xl p-3">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-700">Batal</h2>
                <button class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </button>
            </div>
            <div id="cancel" class="space-y-3 min-h-[200px]">
                @foreach(range(1,2) as $i)
                    @php
                        $members = range(1,2);
                        $progress = 0;
                    @endphp
                    <div class="bg-white p-3 rounded-lg shadow hover:shadow-md cursor-move border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold px-2 py-0.5 bg-red-100 text-red-700 rounded">Canceled</span>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-yellow-100 text-gray-700 rounded">20 Okt</span>
                        </div>
                        <p class="text-sm font-medium text-gray-800">Riset Vendor Baru</p>
                        <div class="flex items-center space-x-4 text-gray-500 text-xs mt-2">
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                </svg>
                                <span>0</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>1</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1">
                                <div class="w-full bg-gray-200 h-1.5 rounded-full mr-2">
                                    <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $progress }}%</span>
                            </div>
                            <div class="flex mt-2 justify-end -space-x-2">
                                @foreach($members as $j)
                                    <img src="https://i.pravatar.cc/40?img={{ $j }}" 
                                         class="w-6 h-6 rounded-full border-2 border-white" 
                                         alt="avatar">
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Script SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    ['todo','inprogress','done','cancel'].forEach(id => {
        new Sortable(document.getElementById(id), {
            group: 'kanban',
            animation: 150,
            ghostClass: 'bg-blue-300'
        });
    });
</script>

@endsection
