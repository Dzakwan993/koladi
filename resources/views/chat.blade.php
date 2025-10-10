@extends('layouts.app')

@section('title', 'Chat')

@section('content')
    <div class="bg-[#e8ecf4] min-h-screen">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav')

        <div class="h-[calc(100vh-120px)] flex flex-col overflow-hidden">
            {{-- Content Area --}}
            <div class="flex flex-1 p-6 gap-6 overflow-hidden">
                {{-- Main Chat Area --}}
                <div class="flex-1 flex flex-col bg-white rounded-xl shadow-sm overflow-hidden">
                    {{-- Chat Header --}}
                    <div class="bg-[#a8c5f7] px-6 py-3 border-b border-gray-200 flex items-center justify-center">
                        <h2 class="text-lg font-semibold text-gray-900">Div. Marketing</h2>
                    </div>

                    {{-- Messages Container --}}
                    <div class="flex-1 overflow-y-auto px-6 py-4 bg-[#f8f9fc]" id="chatContainer">

                        {{-- Date Separator --}}
                        <div class="flex items-center justify-center my-6">
                            <div class="border-t border-gray-300 flex-1"></div>
                            <span class="px-4 text-xs text-gray-500">Hari ini</span>
                            <div class="border-t border-gray-300 flex-1"></div>
                        </div>

                        {{-- Message 1 --}}
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-9 h-9 rounded-full bg-gray-700 flex items-center justify-center text-white font-medium text-xs">
                                    SA</div>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-baseline mb-1">
                                    <span class="font-semibold text-gray-900 text-sm">Sahroni</span>
                                    <span class="text-xs text-gray-500 ml-2">09:23</span>
                                </div>
                                <div class="bg-[#b8cff9] rounded-2xl rounded-tl-sm px-4 py-2 inline-block max-w-md">
                                    <p class="text-gray-900 text-sm">Siapa namamu?</p>
                                </div>
                            </div>
                        </div>

                        {{-- Message 2 --}}
                        <div class="flex items-start mb-4 justify-end">
                            <div class="mr-3 flex-1 text-right">
                                <div class="flex items-baseline justify-end mb-1">
                                    <span class="text-xs text-gray-500 mr-2">09:24</span>
                                    <span class="font-semibold text-gray-900 text-sm">Anda</span>
                                </div>
                                <div class="inline-block">
                                    <div
                                        class="bg-[#6b9aff] text-white rounded-2xl rounded-tr-sm px-4 py-2 inline-block max-w-md">
                                        <p class="text-sm">KOLADI</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div
                                    class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium text-xs">
                                    AN</div>
                            </div>
                        </div>

                         {{-- Message 3 --}}
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-9 h-9 rounded-full bg-gray-700 flex items-center justify-center text-white font-medium text-xs">SA</div>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-baseline mb-1">
                                    <span class="font-semibold text-gray-900 text-sm">Sahroni</span>
                                    <span class="text-xs text-gray-500 ml-2">09:23</span>
                                </div>
                                <div class="bg-[#b8cff9] rounded-2xl rounded-tl-sm px-4 py-2 inline-block max-w-md">
                                    <p class="text-gray-900 text-sm">Kamu tahu DSS ga?</p>
                                </div>
                            </div>
                        </div>

                        {{-- Message 4 --}}
                        <div class="flex items-start mb-4 justify-end">
                            <div class="mr-3 flex-1 text-right">
                                <div class="flex items-baseline justify-end mb-1">
                                    <span class="text-xs text-gray-500 mr-2">09:25</span>
                                    <span class="font-semibold text-gray-900 text-sm">Anda</span>
                                </div>
                                <div class="inline-block">
                                    <div
                                        class="bg-[#6b9aff] text-white rounded-2xl rounded-tr-sm px-4 py-2 inline-block max-w-md">
                                        <p class="text-sm">ohh, tahu tahu</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div
                                    class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium text-xs">
                                    AN</div>
                            </div>
                        </div>

                         {{-- Message 7 --}}
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-9 h-9 rounded-full bg-gray-700 flex items-center justify-center text-white font-medium text-xs">SA</div>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-baseline mb-1">
                                    <span class="font-semibold text-gray-900 text-sm">Sahroni</span>
                                    <span class="text-xs text-gray-500 ml-2">09:23</span>
                                </div>
                                <div class="bg-[#b8cff9] rounded-2xl rounded-tl-sm px-4 py-2 inline-block max-w-md">
                                    <p class="text-gray-900 text-sm">Nah, Apa peran DSS dalam aplikasi KOLADI</p>
                                </div>
                            </div>
                        </div>

                        {{-- Message 8 --}}
                        <div class="flex items-start mb-4 justify-end">
                            <div class="mr-3 flex-1 text-right">
                                <div class="flex items-baseline justify-end mb-1">
                                    <span class="text-xs text-gray-500 mr-2">09:24</span>
                                    <span class="font-semibold text-gray-900 text-sm">Anda</span>
                                </div>
                                <div class="inline-block">
                                    <div class="bg-[#6b9aff] text-white rounded-2xl rounded-tr-sm px-4 py-2 inline-block max-w-md">
                                        <p class="text-sm">Banyak</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium text-xs">AN</div>
                            </div>
                        </div>

                    </div>

                    {{-- Chat Input Bar --}}
                    <div class="border-t border-gray-200 px-4 py-3 bg-white relative">
                        {{-- Scroll to bottom button --}}
                        <button id="scrollToBottom"
                            class="absolute -top-14 right-6 bg-[#E9EFFD] rounded-full p-2.5 shadow-lg hover:bg-gray-50 transition z-10">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>

                        <div class="flex items-center bg-[#e8edf9] rounded-full px-4 py-2.5">
                            {{-- Tombol Plus --}}
                            <button type="button"
                                class="flex items-center justify-center w-7 h-7 rounded-full hover:bg-[#d5ddf2] transition mr-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>

                            {{-- Input Text --}}
                            <input type="text" id="messageInput" placeholder="Ketik pesan disini..."
                                class="flex-1 bg-transparent px-2 text-gray-800 text-sm border-none focus:ring-0 focus:outline-none placeholder-gray-500" />

                            {{-- Tombol Mic --}}
                            <button type="button" id="micButton"
                                class="flex items-center justify-center w-7 h-7 rounded-full hover:bg-[#d5ddf2] transition ml-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </button>

                            {{-- Tombol Send --}}
                            <button type="button" id="sendButton"
                                class="items-center justify-center w-7 h-7 rounded-full bg-blue-500 hover:bg-blue-600 transition ml-2"
                                style="display: none;">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Right Sidebar --}}
                <div class="w-80 bg-white border border-gray-200 flex flex-col rounded-xl shadow-sm overflow-hidden">
                    {{-- Sidebar Header --}}
                    <div class="px-4 py-3.5 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Chat</h3>
                    </div>

                    {{-- Search --}}
                    <div class="px-4 py-3 border-b border-gray-200">
                        <div class="relative">
                            <input type="text" placeholder="Cari rekan tim..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-400 text-sm bg-[#E9EFFD] placeholder-gray-400">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Chat List --}}
                    <div class="flex-1 overflow-y-auto chat-list-scroll">
                        {{-- Chat Item 1 - Active --}}
                        <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer bg-blue-50 border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-xs">
                                        DM</div>
                                    <span
                                        class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">Div. Marketing</h4>
                                    <p class="text-xs text-gray-500 truncate">Banyak</p>
                                </div>
                                <div class="ml-2 flex-shrink-0">
                                    <div
                                        class="min-w-[18px] h-[18px] rounded-full bg-[#FBD644] flex items-center justify-center">
                                        <span class="text-[10px] font-semibold text-white px-1">3</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Chat Item 2 --}}
                        <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-l-4 border-transparent">
                            <div class="flex items-center">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold text-xs">
                                        SA</div>
                                    <span
                                        class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">Sahroni</h4>
                                    <p class="text-xs text-gray-500 truncate">Selamat sih hari??</p>
                                </div>
                                <div class="ml-2 flex-shrink-0">
                                    <div
                                        class="min-w-[18px] h-[18px] rounded-full bg-[#FBD644] flex items-center justify-center">
                                        <span class="text-[10px] font-semibold text-white px-1">5</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Chat Item 3 --}}
                        <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-l-4 border-transparent">
                            <div class="flex items-center">
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-pink-500 flex items-center justify-center text-white font-semibold text-xs">
                                        NA</div>
                                    <span
                                        class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-gray-400 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-gray-900 truncate">Nadya</h4>
                                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">10:23</span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate">Ada disini?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Custom Scrollbar */
            #chatContainer {
                overflow-y: scroll;
            }

            #chatContainer::-webkit-scrollbar {
                width: 6px;
            }

            #chatContainer::-webkit-scrollbar-track {
                background: #f1f5f9;
            }

            #chatContainer::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }

            #chatContainer::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            .chat-list-scroll {
                scrollbar-gutter: stable;
            }

            .chat-list-scroll::-webkit-scrollbar {
                width: 6px;
            }

            .chat-list-scroll::-webkit-scrollbar-track {
                background: #f1f5f9;
            }

            .chat-list-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }

            .chat-list-scroll::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chatContainer = document.getElementById('chatContainer');
                const messageInput = document.getElementById('messageInput');
                const scrollToBottomBtn = document.getElementById('scrollToBottom');
                const micButton = document.getElementById('micButton');
                const sendButton = document.getElementById('sendButton');

                // Toggle between mic and send button
                if (messageInput && micButton && sendButton) {
                    messageInput.addEventListener('input', function() {
                        const hasText = this.value.trim().length > 0;
                        if (hasText) {
                            micButton.style.display = 'none';
                            sendButton.style.display = 'flex';
                        } else {
                            micButton.style.display = 'flex';
                            sendButton.style.display = 'none';
                        }
                    });
                }

                // Send message function
                function sendMessage() {
                    if (messageInput && messageInput.value.trim()) {
                        console.log('Sending message:', messageInput.value);
                        messageInput.value = '';
                        messageInput.dispatchEvent(new Event('input'));
                        scrollToBottom();
                    }
                }

                // Send button click
                if (sendButton) {
                    sendButton.addEventListener('click', sendMessage);
                }

                // Auto scroll
                function scrollToBottom() {
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                }
                scrollToBottom();

                // Show/hide scroll button
                if (chatContainer && scrollToBottomBtn) {
                    chatContainer.addEventListener('scroll', function() {
                        const isAtBottom = chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer
                            .clientHeight + 100;
                        scrollToBottomBtn.style.display = isAtBottom ? 'none' : 'block';
                    });
                }

                // Scroll to bottom button click
                if (scrollToBottomBtn) {
                    scrollToBottomBtn.addEventListener('click', scrollToBottom);
                }

                // Enter to send
                if (messageInput) {
                    messageInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            sendMessage();
                        }
                    });
                }
            });
        </script>
    </div>
@endsection
