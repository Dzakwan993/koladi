@extends('layouts.app')

@section('title', 'Chat Perusahaan')

@section('content')
    @push('scripts')
        @vite('resources/js/company-chat.js')
    @endpush

    <style>
        /* Custom Scrollbar */
        #chatContainer::-webkit-scrollbar,
        .chat-list-scroll::-webkit-scrollbar {
            width: 6px;
        }

        #chatContainer::-webkit-scrollbar-track,
        .chat-list-scroll::-webkit-scrollbar-track {
            background: #F0F2F5;
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

        /* SweetAlert Modal Fix */
        .swal2-container {
            backdrop-filter: blur(2px);
            background: rgba(0, 0, 0, 0.4) !important;
            z-index: 10000 !important;
        }

        .swal2-popup {
            border-radius: 12px !important;
        }

        html {
            overflow-y: scroll;
        }

        body.swal2-shown {
            overflow: hidden !important;
        }

        /* 🔥 TAMBAHKAN: Image Modal Styles */
        #imageModalOverlay {
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* 🔥 TAMBAHKAN: Better image thumbnail in chat */
        .message-new img {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .message-new img:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* 🔥 TAMBAHKAN: Smooth scroll behavior */
        #chatContainer {
            position: relative;
            /* 🔥 INI PENTING! */
            height: 100%;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        /* 🔥 TAMBAHKAN: Active conversation highlight animation */
        [data-conversation-id],
        [data-member-id] {
            transition: all 0.2s ease;
        }

        [data-conversation-id]:hover,
        [data-member-id]:hover {
            transform: translateX(2px);
        }

        /* 🔥 TAMBAHKAN: Unread badge animation */
        [id^="unread-badge-"] {
            transition: all 0.3s ease;
        }

        /* 🔥 TAMBAHKAN: Message bubble animations */
        .message-new {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 🔥 TAMBAHKAN: Deleted message style */
        .deleted-message {
            opacity: 0.7;
        }

        /* 🔥 TAMBAHKAN: Loading spinner */
        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* 🔥 TAMBAHKAN: File preview animations */
        #filePreviewList>div {
            animation: scaleIn 0.2s ease;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* 🔥 TAMBAHKAN: Reply preview style enhancement */
        #replyPreviewContainer {
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }

            to {
                opacity: 1;
                max-height: 100px;
            }
        }

        /* 🔥 TAMBAHKAN: Typing indicator animation */
        #typing-indicator .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        /* 🔥 TAMBAHKAN: Search input focus effect */
        input[type="text"]:focus {
            transition: all 0.3s ease;
        }

        /* 🔥 TAMBAHKAN: Button hover effects */
        button {
            transition: all 0.2s ease;
        }

        button:active {
            transform: scale(0.95);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 🔥 TAMBAHKAN: Drop zone style enhancement */
        #dropZone {
            transition: all 0.3s ease;
        }

        /* 🔥 TAMBAHKAN: Message action buttons */
        .group:hover .opacity-0 {
            transition: opacity 0.2s ease;
        }

        /* 🔥 TAMBAHKAN: Responsive image container */
        .message-new img {
            max-width: 100%;
            height: auto;
        }

        /* 🔥 TAMBAHKAN: Better mobile support */
        @media (max-width: 768px) {
            #imageModalOverlay {
                padding: 1rem;
            }

            #imageModalOverlay img {
                max-height: 70vh !important;
            }
        }
    </style>

    {{-- Main Container dengan data attributes --}}
    <div id="chat-page-container" class="h-full bg-[#E9EFFD] flex flex-col" data-company-id="{{ $company->id }}"
        data-auth-user-id="{{ Auth::id() }}" data-api-url="{{ url('/') }}" data-csrf-token="{{ csrf_token() }}"
        data-chat-scope="company">

        <div class="flex-1 flex overflow-hidden">
            {{-- Main Content Area --}}
            <div class="flex-1 flex p-6 gap-6 overflow-hidden">

                {{-- Central Chat Window --}}
                <div class="flex-1 flex flex-col bg-white rounded-xl shadow-md overflow-hidden min-w-0 relative">


                    <div class="bg-[#9BB7F6] px-6 py-4 border-b border-gray-200 flex items-center justify-center shadow-sm">
                        <h2 id="chatHeaderTitle" class="text-lg font-semibold text-gray-800">Pilih Percakapan</h2>
                    </div>

                    {{-- Messages Container --}}
                    <div class="flex-1 overflow-y-auto px-6 py-4 bg-[#F8F9FB] relative" id="chatContainer">
                        <div id="messageList" class="flex flex-col gap-4">
                            <div class="flex h-full items-center justify-center">
                                <p class="text-gray-500">Silakan pilih percakapan di sebelah kanan.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Scroll to bottom button --}}
                    <button id="scrollToBottom"
                        class="absolute right-6 bottom-[100px] bg-white rounded-full p-2.5 shadow-lg border border-gray-200 hover:bg-gray-50 transition z-20"
                        style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-gray-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Chat Input Bar --}}
                    <div id="chatInputBar" class="border-t border-gray-200 px-6 py-4 bg-white" style="display: none;">

                        {{-- File Preview Container --}}
                        <div id="filePreviewContainer" class="mb-3" style="display: none;">
                            <div id="filePreviewList" class="flex flex-wrap gap-2"></div>
                        </div>

                        {{-- Reply Preview Container --}}
                        <div id="replyPreviewContainer" class="mb-3 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-3"
                            style="display: none;">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p id="replySenderName" class="text-xs font-semibold text-blue-700"></p>
                                    <p id="replyContent" class="text-xs text-blue-600 truncate"></p>
                                </div>
                                <button type="button" onclick="cancelReply()"
                                    class="text-blue-500 hover:text-blue-700 ml-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <form id="sendMessageForm" class="flex items-center bg-[#E9EFFD] rounded-full px-5 py-3">

                            {{-- Hidden File Input --}}
                            <input type="file" id="fileInput" multiple accept="*/*" style="display: none;">

                            {{-- Upload Button --}}
                            <button type="button" id="uploadButton"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white mr-3 hover:bg-blue-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>

                            {{-- Text Input --}}
                            <input type="text" id="messageInput" placeholder="Ketik pesan disini..."
                                class="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 placeholder-gray-500 text-sm text-gray-800"
                                autocomplete="off" />

                            {{-- Send Button --}}
                            <button type="submit" id="sendButton"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white ml-2 hover:bg-blue-600 transition"
                                style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-4 h-4 -rotate-45">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                            </button>
                        </form>

                        {{-- Drop Zone Overlay --}}
                        <div id="dropZone"
                            class="absolute inset-0 bg-blue-50 bg-opacity-90 border-4 border-dashed border-blue-400 rounded-xl flex items-center justify-center"
                            style="display: none; z-index: 1000;">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto text-blue-500 mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                    </path>
                                </svg>
                                <p class="text-xl font-semibold text-blue-700">Drop file di sini</p>
                                <p class="text-sm text-gray-600 mt-2">Atau klik tombol + untuk memilih file</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Sidebar --}}
                <div class="w-80 flex-shrink-0 bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Chat</h3>
                    </div>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="relative">
                            <input type="text" placeholder="Cari rekan kerja..." id="searchInput"
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

@endsection
