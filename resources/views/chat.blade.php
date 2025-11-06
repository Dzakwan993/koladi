@extends('layouts.app')

@section('title', 'Chat')

@section('content')
    {{-- Menjadi seperti ini (tambahkan id dan data- attributes) --}}
    <div id="chat-page-container"class="h-full bg-[#E9EFFD] flex flex-col" data-workspace-id="{{ $workspace->id }}"
        data-auth-user-id="{{ Auth::id() }}" data-api-url="{{ url('/') }}" data-csrf-token="{{ csrf_token() }}">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav')

        <div class="flex-1 flex overflow-hidden">
            {{-- Main Content Area --}}
            <div class="flex-1 flex p-6 gap-6 overflow-hidden">

                {{-- Central Chat Window --}}
                <div class="flex-1 flex flex-col bg-white rounded-xl shadow-md overflow-hidden min-w-0">

                    <div class="bg-[#9BB7F6] px-6 py-4 border-b border-gray-200 flex items-center justify-center shadow-sm">
                        <h2 id="chatHeaderTitle" class="text-lg font-semibold text-gray-800">Pilih Percakapan</h2>
                    </div>

                    {{-- Messages Container --}}
                    <div class="flex-1 overflow-y-auto px-6 py-4 bg-[#F8F9FB] relative" id="chatContainer">
                        {{-- Scroll to bottom button --}}
                        <button id="scrollToBottom"
                            class="absolute bottom-4 right-10 bg-white rounded-full p-2.5 shadow-lg border border-gray-200 hover:bg-gray-50 transition z-10"
                            style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5 text-gray-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        {{-- Pesan akan di-load di sini oleh JavaScript --}}
                        <div id="messageList" class="flex flex-col gap-4">
                            <div class="flex h-full items-center justify-center">
                                <p class="text-gray-500">Silakan pilih percakapan di sebelah kanan.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Chat Input Bar --}}
                    <div id="chatInputBar" class="border-t border-gray-200 px-6 py-4 bg-white" style="display: none;">

                        {{-- 1. Ganti 'rounded-xl' kembali ke 'rounded-full' untuk style bubble --}}
                        <form id="sendMessageForm" class="flex items-center bg-[#E9EFFD] rounded-full px-5 py-3">

                            {{-- Tombol Plus --}}
                            <button type="button"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white mr-3 hover:bg-blue-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>

                            {{-- Input Teks --}}
                            <input type="text" id="messageInput" placeholder="Ketik pesan disini..."
                                class="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 placeholder-gray-500 text-sm text-gray-800"
                                autocomplete="off" />

                            {{-- 2. TAMBAHKAN KEMBALI Tombol Mic --}}
                            <button type="button" id="micButton"
                                class="flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 transition"
                                style="display: flex;"> {{-- Tampil di awal --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 18.75a.375.375 0 0 0 .375-.375v-1.5a.375.375 0 0 0-.375-.375h-1.5a.375.375 0 0 0-.375.375v1.5a.375.375 0 0 0 .375.375h1.5ZM8.25 18.75a.375.375 0 0 0 .375-.375v-1.5a.375.375 0 0 0-.375-.375h-1.5a.375.375 0 0 0-.375.375v1.5a.375.375 0 0 0 .375.375h1.5ZM15.75 18.75a.375.375 0 0 0 .375-.375v-1.5a.375.375 0 0 0-.375-.375h-1.5a.375.375 0 0 0-.375.375v1.5a.375.375 0 0 0 .375.375h1.5Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15V6.75A.75.75 0 0 1 9 6h6a.75.75 0 0 1 .75.75v8.25m.75 3.75-3-3m0 0-3 3m3-3v3m-3.75 0H7.5a2.25 2.25 0 0 0-2.25 2.25v.75a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-.75a2.25 2.25 0 0 0-2.25-2.25h-.75" />
                                </svg>
                            </button>

                            {{-- 3. Tombol Send --}}
                            <button type="submit" id="sendButton"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white ml-2 hover:bg-blue-600 transition"
                                style="display: none;"> {{-- Sembunyi di awal --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-4 h-4 -rotate-45">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right Sidebar --}}
                <div class="w-80 flex-shrink-0 bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Chat</h3>
                    </div>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="relative">
                            <input type="text" placeholder="Cari rekan tim..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-full bg-[#F0F2F5] border-transparent focus:border-blue-500 focus:ring-blue-500 placeholder-gray-500 text-sm text-gray-800">
                            <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Chat List --}}
                    <div id="chatListContainer" class="flex-1 overflow-y-auto chat-list-scroll">
                        <div class="p-4 text-center text-gray-500">Memuat...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Scrollbar (dari kode Anda, sudah bagus) */
        #chatContainer::-webkit-scrollbar,
        .chat-list-scroll::-webkit-scrollbar {
            width: 6px;
        }

        #chatContainer::-webkit-scrollbar-track,
        .chat-list-scroll::-webkit-scrollbar-track {
            background: #F0F2F5;
            /* Ubah warna track scrollbar agar sesuai background chat */
        }

        #chatContainer::-webkit-scrollbar-thumb,
        .chat-list-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        #chatContainer::-webkit-scrollbar-thumb:hover,
        .chat-list-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .chat-list-scroll {
            scrollbar-gutter: stable;
        }
    </style>
@endsection

@push('scripts')
    @vite('resources/js/chat.js')
@endpush
