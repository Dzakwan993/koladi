@extends('layouts.app')

@section('title', 'Chat')

@section('content')
    <div class="h-full bg-[#E9EFFD] flex flex-col">
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
    <script>
        // -----------------------------------------------------------------
        // LANGKAH 1: Setup Global Variables
        // -----------------------------------------------------------------
        const WORKSPACE_ID = '{{ $workspace->id }}';
        const AUTH_USER_ID = '{{ Auth::id() }}';
        const API_URL = '{{ url('/') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        let currentConversationId = null;
        window.allConversations = [];

        // -----------------------------------------------------------------
        // LANGKAH 2: Ambil Elemen DOM
        // -----------------------------------------------------------------
        const chatContainer = document.getElementById('chatContainer');
        const messageList = document.getElementById('messageList');
        const chatListContainer = document.getElementById('chatListContainer');
        const chatHeaderTitle = document.getElementById('chatHeaderTitle');
        const chatInputBar = document.getElementById('chatInputBar');
        const sendMessageForm = document.getElementById('sendMessageForm');
        const messageInput = document.getElementById('messageInput');
        const scrollToBottomBtn = document.getElementById('scrollToBottom');
        const micButton = document.getElementById('micButton');
        const sendButton = document.getElementById('sendButton');

        // -----------------------------------------------------------------
        // LANGKAH 3: Fungsi Helper (Render HTML)
        // -----------------------------------------------------------------

        /** Mengubah "John Doe" menjadi "JD" */
        function getInitials(name) {
            if (!name) return '??';
            const names = name.split(' ');
            if (names.length === 1) return name.substring(0, 2).toUpperCase();
            return (names[0][0] + names[names.length - 1][0]).toUpperCase();
        }

        /** Mengubah format tanggal "2023-10-30T09:23:00..." menjadi "09:23" */
        function formatTime(dateTimeString) {
            if (!dateTimeString) return '';
            const date = new Date(dateTimeString);
            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }

        /** Membuat HTML untuk satu item di daftar chat (sidebar) */
        function createConversationHTML(conversation) {
            let chatName = conversation.name;
            let chatAvatarInitials = getInitials(conversation.name);
            let avatarBgClass = 'bg-blue-200 text-blue-800'; // Warna default untuk grup/private

            if (conversation.type === 'private') {
                const otherParticipant = conversation.participants.find(p => p.user_id !== AUTH_USER_ID);
                if (otherParticipant) {
                    chatName = otherParticipant.user.full_name;
                    chatAvatarInitials = getInitials(otherParticipant.user.full_name);
                    avatarBgClass = 'bg-indigo-100 text-indigo-800'; // Warna untuk private chat
                } else {
                    chatName = 'Percakapan Dihapus';
                    chatAvatarInitials = 'X';
                    avatarBgClass = 'bg-gray-200 text-gray-600';
                }
            }

            const lastMessage = conversation.messages.length > 0 ? conversation.messages[0] : null;
            const lastMessageText = lastMessage ? (lastMessage.sender_id === AUTH_USER_ID ? 'Anda: ' : '') + lastMessage
                .content : 'Belum ada pesan';
            const unreadCount = 0; // TODO: Hitung unread count

            // UBAH DI SINI: Dihapus bg-[#F0F2F5] dari kondisi 'selected'
            return `
        <div class="px-6 py-3 hover:bg-gray-50 cursor-pointer ${currentConversationId === conversation.id ? 'bg-[#E9EFFD] text-blue-600 font-semibold' : 'text-gray-800'}"
     data-conversation-id="${conversation.id}"
     onclick="selectConversation('${conversation.id}')">
            <div class="flex items-center">
                <div class="relative flex-shrink-0">
                    <div class="w-10 h-10 rounded-full ${avatarBgClass} flex items-center justify-center font-bold text-sm">${chatAvatarInitials}</div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <h4 class="text-sm truncate">${chatName}</h4>
                    <p class="text-xs text-gray-500 truncate">${lastMessageText}</p>
                </div>
                ${unreadCount > 0 ? `
                                                    <div class="ml-2 flex-shrink-0">
                                                        <div class="min-w-[18px] h-[18px] rounded-full bg-blue-500 flex items-center justify-center">
                                                            <span class="text-[10px] font-semibold text-white px-1">${unreadCount}</span>
                                                        </div>
                                                    </div>` : ''}
            </div>
        </div>
        `;
        }

        /** Membuat HTML untuk satu item Anggota */
        function createMemberHTML(member) {
            const initials = getInitials(member.full_name);
            return `
        <div class="px-6 py-3 hover:bg-gray-50 cursor-pointer text-gray-800"
             onclick="startChatWithUser('${member.id}', '${member.full_name}')">
            <div class="flex items-center">
                <div class="relative flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center font-bold text-sm">${initials}</div>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <h4 class="text-sm font-semibold truncate">${member.full_name}</h4>
                    <p class="text-xs text-gray-500 truncate">Mulai percakapan</p>
                </div>
            </div>
        </div>
        `;
        }

        /** Membuat HTML untuk satu gelembung pesan */
        function createMessageHTML(message) {
            const isSender = message.sender_id === AUTH_USER_ID;
            const senderName = isSender ? 'Anda' : (message.sender ? message.sender.full_name : 'User');
            const initials = getInitials(senderName);
            const time = formatTime(message.created_at);

            if (isSender) {
                // Pesan "Anda" (Kanan)
                // UBAH DI SINI: rounded-tr-none -> rounded-br-none
                return `
            <div id="${message.id}" class="flex items-end justify-end">
                <div class="flex flex-col items-end">
                    <div class="flex items-baseline mb-1">
                        <span class="text-xs text-gray-500 mr-2">${time}</span>
                        <span class="font-semibold text-gray-700 text-sm">Anda</span>
                    </div>
                    <div class="bg-blue-500 text-white rounded-lg rounded-br-none px-4 py-2 max-w-xl shadow-sm">
                        <p class="text-sm" style="word-break: break-word;">${message.content}</p>
                    </div>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <div class="w-8 h-8 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center font-bold text-xs">
                        ${initials}
                    </div>
                </div>
            </div>
            `;
            } else {
                // Pesan Orang Lain (Kiri)
                // UBAH DI SINI: bg-gray-100 -> bg-white border border-gray-200
                return `
            <div id="${message.id}" class="flex items-end justify-start">
                <div class="flex-shrink-0 mr-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center font-bold text-xs">
                        ${initials}
                    </div>
                </div>
                <div class="flex flex-col items-start">
                    <div class="flex items-baseline mb-1">
                        <span class="font-semibold text-gray-700 text-sm">${senderName}</span>
                        <span class="text-xs text-gray-500 ml-2">${time}</span>
                    </div>
                    <div class="bg-white border border-gray-200 text-gray-800 rounded-lg rounded-tl-none px-4 py-2 max-w-xl shadow-sm">
                        <p class="text-sm" style="word-break: break-word;">${message.content}</p>
                    </div>
                </div>
            </div>
            `;
            }
        }

        // -----------------------------------------------------------------
        // LANGKAH 4: Fungsi Fetch Data (API Calls)
        // -----------------------------------------------------------------

        /** Muat daftar percakapan di sidebar */
        async function loadConversations() {
            try {
                const response = await fetch(`${API_URL}/api/workspace/${WORKSPACE_ID}/chat`);
                if (!response.ok) throw new Error('Gagal memuat percakapan');
                const data = await response.json();

                window.allConversations = [data.main_group, ...data.conversations].filter(Boolean);
                let html = '';

                // --- UBAH DI SINI: Filter Anggota Tim ---

                // 1. Dapatkan daftar ID user yang sudah punya DM (private chat)
                const existingPrivateChatUserIds = data.conversations
                    .filter(c => c.type === 'private') // Ambil yg private
                    .flatMap(c => c.participants) // Ambil semua participants
                    .map(p => p.user_id) // Ambil user_id nya
                    .filter(id => id !== AUTH_USER_ID); // Kecualikan diri sendiri

                // 2. Buat Set untuk pencarian cepat (unik)
                const existingUserSet = new Set(existingPrivateChatUserIds);

                // 3. Filter data.members
                const filteredMembers = data.members.filter(member => {
                    // JANGAN tampilkan member jika ID-nya SUDAH ADA di Set
                    return !existingUserSet.has(member.id);
                });

                // --- Akhir Perubahan ---


                if (data.main_group) {
                    html +=
                        `<div class="px-6 pt-4 pb-2"><span class="text-xs font-semibold text-gray-500 uppercase">Ruang Kerja</span></div>`;
                    html += createConversationHTML(data.main_group);
                }
                if (data.conversations.length > 0) {
                    html +=
                        `<div class="px-6 pt-4 pb-2 mt-2"><span class="text-xs font-semibold text-gray-500 uppercase">Percakapan</span></div>`;
                    html += data.conversations.map(createConversationHTML).join('');
                }

                // UBAH DI SINI: Gunakan 'filteredMembers'
                if (filteredMembers.length > 0) {
                    html +=
                        `<div class="px-6 pt-4 pb-2 mt-2"><span class="text-xs font-semibold text-gray-500 uppercase">Anggota Tim</span></div>`;
                    // Gunakan 'filteredMembers' BUKAN 'data.members'
                    filteredMembers.forEach(member => {
                        html += createMemberHTML(member);
                    });
                }

                if (html === '') {
                    chatListContainer.innerHTML = '<div class="p-6 text-center text-gray-500">Belum ada data.</div>';
                    return;
                }
                chatListContainer.innerHTML = html;

            } catch (error) {
                console.error(error);
                chatListContainer.innerHTML =
                    '<div class="p-6 text-center text-red-500">Gagal memuat. (JS Error)</div>';
            }
        }
        /** Fungsi untuk memulai DM baru */
        async function startChatWithUser(userId, userName) {
            chatHeaderTitle.textContent = `Membuka chat dengan ${userName}...`;
            messageList.innerHTML = '<div class="p-6 text-center text-gray-500">Memuat...</div>';
            chatInputBar.style.display = 'none';
            try {
                const response = await fetch(`${API_URL}/api/chat/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        workspace_id: WORKSPACE_ID,
                        type: 'private',
                        participants: [userId]
                    })
                });
                if (!response.ok) throw new Error('Gagal membuat percakapan');
                const result = await response.json();
                const newConversationId = result.conversation.id;
                await loadConversations();
                await loadMessages(newConversationId);
            } catch (error) {
                console.error(error);
                chatHeaderTitle.textContent = 'Gagal';
                messageList.innerHTML = '<div class="p-6 text-center text-red-500">Gagal membuat percakapan.</div>';
            }
        }

        /** Muat isi pesan untuk percakapan yang dipilih */
        async function loadMessages(conversationId) {
            if (!conversationId) return;

            currentConversationId = conversationId;
            messageList.innerHTML = '<div class="p-6 text-center text-gray-500">Memuat pesan...</div>';
            chatInputBar.style.display = 'block';

            const conv = window.allConversations.find(c => c.id === conversationId);
            if (conv) {
                let chatName = conv.name;
                if (conv.type === 'private') {
                    const other = conv.participants.find(p => p.user_id !== AUTH_USER_ID);
                    chatName = other ? other.user.full_name : 'Unknown';
                }
                chatHeaderTitle.textContent = chatName;

                // UBAH DI SINI: Blok update avatar di header dihapus karena header di-simplify
            }

            document.querySelectorAll('#chatListContainer div[data-conversation-id]').forEach(div => {
                if (div.dataset.conversationId === conversationId) {
                    div.classList.add('bg-[#E9EFFD]', 'text-blue-600', 'font-semibold'); // <-- TAMBAH BG
                    div.classList.remove('text-gray-800');
                } else {
                    div.classList.remove('bg-[#E9EFFD]', 'text-blue-600', 'font-semibold'); // <-- HAPUS BG
                    div.classList.add('text-gray-800');
                }
            });

            try {
                const response = await fetch(`${API_URL}/api/chat/${conversationId}/messages`);
                if (!response.ok) throw new Error('Gagal memuat pesan');
                const messages = await response.json();

                if (messages.length === 0) {
                    messageList.innerHTML =
                        '<div class="p-6 text-center text-gray-500">Belum ada pesan di percakapan ini.</div>';
                } else {
                    // UBAH DI SINI: Tambah logika untuk pemisah tanggal
                    let messageHTML = '';
                    let lastMessageDate = null;
                    const today = new Date().toDateString();
                    const yesterday = new Date(Date.now() - 86400000).toDateString();

                    messages.forEach(message => {
                        const messageDate = new Date(message.created_at);
                        const messageDateString = messageDate.toDateString();

                        if (messageDateString !== lastMessageDate) {
                            let dateLabel = messageDate.toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric'
                            });
                            if (messageDateString === today) {
                                dateLabel = 'Hari ini';
                            } else if (messageDateString === yesterday) {
                                dateLabel = 'Kemarin';
                            }

                            messageHTML += `
                                <div class="flex justify-center items-center my-4">
                                    <span class="bg-white border border-gray-200 rounded-full px-4 py-1 text-xs text-gray-500 shadow-sm">
                                        ${dateLabel}
                                    </span>
                                </div>
                            `;
                            lastMessageDate = messageDateString;
                        }
                        messageHTML += createMessageHTML(message);
                    });
                    messageList.innerHTML = messageHTML;
                }
                scrollToBottom();
            } catch (error) {
                console.error(error);
                messageList.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat pesan.</div>';
            }
        }

        /** Kirim pesan baru */
        async function handleSendMessage(e) {
            e.preventDefault();
            const content = messageInput.value.trim();
            if (!content || !currentConversationId) return;

            const tempMessage = {
                id: 'temp-' + Date.now(),
                content: content,
                sender_id: AUTH_USER_ID,
                sender: {
                    full_name: 'Anda'
                },
                created_at: new Date().toISOString()
            };
            appendMessage(tempMessage);
            messageInput.value = '';
            messageInput.dispatchEvent(new Event('input'));

            try {
                const response = await fetch(`${API_URL}/api/chat/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: currentConversationId,
                        content: content
                    })
                });
                if (!response.ok) throw new Error('Gagal mengirim pesan');

                const result = await response.json();

                // Ganti pesan sementara dengan data asli dari server
                const tempMsgElement = document.getElementById(tempMessage.id);
                if (tempMsgElement) {
                    tempMsgElement.outerHTML = createMessageHTML(result.data);
                }

                // Update sidebar (preview pesan terakhir)
                const newMsgContent = result.data.content;
                const sidebarItem = document.querySelector(
                    `#chatListContainer div[data-conversation-id="${currentConversationId}"]`
                );
                if (sidebarItem) {
                    const previewText = sidebarItem.querySelector('p.text-xs');
                    if (previewText) {
                        previewText.textContent = `Anda: ${newMsgContent}`;
                    }
                }

            } catch (error) {
                console.error(error);
                const tempMsgElement = document.getElementById(tempMessage.id);
                if (tempMsgElement) {
                    tempMsgElement.querySelector('p').textContent += ' (Gagal terkirim)';
                    tempMsgElement.classList.add('opacity-50');
                }
            }
        }

        /** Tambahkan satu pesan ke DOM */
        function appendMessage(message) {
            const emptyState = messageList.querySelector(
                'div.h-full.items-center.justify-center'); // Cari div "Silakan pilih percakapan"
            if (emptyState) emptyState.remove();

            const emptyState2 = messageList.querySelector(
                'div.p-6.text-center.text-gray-500'); // Cari div "Memuat pesan..."
            if (emptyState2) emptyState2.remove();

            // --- PERBAIKAN LOGIKA "HARI INI" ---
            const today = new Date().toDateString();

            // 1. Cari separator tanggal TERAKHIR yang ada di list
            const allSeparators = messageList.querySelectorAll('div.flex.justify-center.items-center span');
            let lastSeparatorText = null;
            if (allSeparators.length > 0) {
                // Tambahkan .trim() untuk membersihkan spasi aneh
                lastSeparatorText = allSeparators[allSeparators.length - 1].textContent.trim();
            }

            // 2. Hanya tambahkan separator "Hari ini" JIKA:
            //    - Belum ada separator sama sekali (!lastSeparatorText)
            //    - ATAU separator terakhir BUKAN "Hari ini"
            if (!lastSeparatorText || lastSeparatorText !== 'Hari ini') {
                // Dan pastikan pesan ini memang dikirim hari ini (selalu true untuk tempMessage)
                if (new Date(message.created_at).toDateString() === today) {
                    const dateHTML = `
                        <div class="flex justify-center items-center my-4">
                            <span class="bg-white border border-gray-200 rounded-full px-4 py-1 text-xs text-gray-500 shadow-sm">
                                Hari ini
                            </span>
                        </div>
                    `;
                    messageList.insertAdjacentHTML('beforeend', dateHTML);
                }
            }
            // --- AKHIR PERBAIKAN ---

            const messageHTML = createMessageHTML(message);
            messageList.insertAdjacentHTML('beforeend', messageHTML);
            scrollToBottom();
        }


        // -----------------------------------------------------------------
        // LANGKAH 5: Event Listeners
        // -----------------------------------------------------------------

        function setupInputListeners() {
            messageInput.addEventListener('input', function() {
                const hasText = this.value.trim().length > 0;
                micButton.style.display = hasText ? 'none' : 'flex';
                sendButton.style.display = hasText ? 'flex' : 'none';
            });

            sendMessageForm.addEventListener('submit', handleSendMessage);

            scrollToBottomBtn.addEventListener('click', scrollToBottom);

            chatContainer.addEventListener('scroll', function() {
                const isAtBottom = chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer
                    .clientHeight + 100;
                scrollToBottomBtn.style.display = isAtBottom ? 'none' : 'block';
            });

            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    handleSendMessage(e);
                }
            });
        }

        function scrollToBottom() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        window.selectConversation = function(conversationId) {
            loadMessages(conversationId);
        }

        // -----------------------------------------------------------------
        // LANGKAH 6: Inisialisasi
        // -----------------------------------------------------------------
        document.addEventListener('DOMContentLoaded', function() {
            setupInputListeners();
            loadConversations();
            scrollToBottom(); // Panggil sekali di awal

            // TODO: Setup Laravel Echo untuk real-time
        });
    </script>
@endpush
