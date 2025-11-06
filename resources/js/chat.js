// -----------------------------------------------------------------
// LANGKAH 1: Setup Global Variables
// -----------------------------------------------------------------

// MODIFIKASI: Variabel-variabel ini sekarang dibaca dari data- attributes di HTML
let WORKSPACE_ID, AUTH_USER_ID, API_URL, CSRF_TOKEN;
let currentConversationId = null;
window.allConversations = [];

// -----------------------------------------------------------------
// LANGKAH 2: Ambil Elemen DOM
// -----------------------------------------------------------------
let chatContainer, messageList, chatListContainer, chatHeaderTitle,
    chatInputBar, sendMessageForm, messageInput, scrollToBottomBtn,
    micButton, sendButton;

function cacheDOMElements() {
    chatContainer = document.getElementById('chatContainer');
    messageList = document.getElementById('messageList');
    chatListContainer = document.getElementById('chatListContainer');
    chatHeaderTitle = document.getElementById('chatHeaderTitle');
    chatInputBar = document.getElementById('chatInputBar');
    sendMessageForm = document.getElementById('sendMessageForm');
    messageInput = document.getElementById('messageInput');
    scrollToBottomBtn = document.getElementById('scrollToBottom');
    micButton = document.getElementById('micButton');
    sendButton = document.getElementById('sendButton');
}

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
    let avatarBgClass = 'bg-blue-200 text-blue-800';

    if (conversation.type === 'private') {
        const otherParticipant = conversation.participants.find(p => p.user_id !== AUTH_USER_ID);
        if (otherParticipant) {
            chatName = otherParticipant.user.full_name;
            chatAvatarInitials = getInitials(otherParticipant.user.full_name);
            avatarBgClass = 'bg-indigo-100 text-indigo-800';
        } else {
            chatName = 'Percakapan Dihapus';
            chatAvatarInitials = 'X';
            avatarBgClass = 'bg-gray-200 text-gray-600';
        }
    }

    // MODIFIKASI: Gunakan 'last_message' & 'unread_count' dari API
    const lastMessage = conversation.last_message;
    const lastMessageText = lastMessage ?
        (lastMessage.sender_id === AUTH_USER_ID ? 'Anda: ' : '') + lastMessage.content :
        'Belum ada pesan';
    const unreadCount = conversation.unread_count || 0; // Ambil dari API

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
       <p id="preview-${conversation.id}" class="text-xs text-gray-500 truncate">${lastMessageText}</p>
     </div>

     <div id="unread-badge-${conversation.id}" class="ml-2 flex-shrink-0" style="${unreadCount > 0 ? 'display: block;' : 'display: none;'}">
         <div class="min-w-[18px] h-[18px] rounded-full bg-blue-500 flex items-center justify-center">
           <span id="unread-count-${conversation.id}" class="text-[10px] font-semibold text-white px-1">${unreadCount}</span>
           </div>
     </div>
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
    const isSender = message.sender_id === AUTH_USER_ID; // <-- Perbandingan String vs String
    const senderName = isSender ? 'Anda' : (message.sender ? message.sender.full_name : 'User');
    const initials = getInitials(senderName);
    const time = formatTime(message.created_at);

    if (isSender) {
        // Pesan "Anda" (Kanan)
        return `
      <div id="${message.id}" class="flex items-start justify-end">
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
        return `
      <div id="${message.id}" class="flex items-start justify-start">
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

/** Membuat HTML untuk pemisah tanggal */
function createDateSeparatorHTML(dateLabel) {
    return `
   <div class="flex justify-center items-center my-4">
       <span class="bg-white border border-gray-200 rounded-full px-4 py-1 text-xs text-gray-500 shadow-sm">
           ${dateLabel}
       </span>
   </div>
   `;
}

// -----------------------------------------------------------------
// LANGKAH 4: Fungsi Fetch Data (API Calls)
// -----------------------------------------------------------------

/** Muat daftar percakapan di sidebar */
async function loadConversations() {
    chatListContainer.innerHTML = '<div class="p-4 text-center text-gray-500">Memuat...</div>';
    try {
        const response = await fetch(`${API_URL}/api/workspace/${WORKSPACE_ID}/chat`);
        if (!response.ok) throw new Error('Gagal memuat percakapan');
        const data = await response.json();

        const sortedConversations = data.conversations.sort((a, b) => {
            const timeA = a.last_message ? new Date(a.last_message.created_at) : 0;
            const timeB = b.last_message ? new Date(b.last_message.created_at) : 0;
            return timeB - timeA;
        });

        window.allConversations = [data.main_group, ...sortedConversations].filter(Boolean);
        let html = '';

        const existingPrivateChatUserIds = data.conversations
            .filter(c => c.type === 'private')
            .flatMap(c => c.participants)
            .map(p => p.user_id)
            .filter(id => id !== AUTH_USER_ID);
        const existingUserSet = new Set(existingPrivateChatUserIds);
        const filteredMembers = data.members.filter(member => {
            return !existingUserSet.has(member.id);
        });

        if (data.main_group) {
            html += `<div class="px-6 pt-4 pb-2"><span class="text-xs font-semibold text-gray-500 uppercase">Ruang Kerja</span></div>`;
            html += createConversationHTML(data.main_group);
        }
        if (sortedConversations.length > 0) {
            html += `<div class="px-6 pt-4 pb-2 mt-2"><span class="text-xs font-semibold text-gray-500 uppercase">Percakapan</span></div>`;
            html += sortedConversations.map(createConversationHTML).join('');
        }
        if (filteredMembers.length > 0) {
            html += `<div class="px-6 pt-4 pb-2 mt-2"><span class="text-xs font-semibold text-gray-500 uppercase">Anggota Tim</span></div>`;
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
        chatListContainer.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat. (JS Error)</div>';
    }
}

/** Fungsi untuk memulai DM baru */
window.startChatWithUser = async function (userId, userName) {
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
        setupEchoListeners(); // Setup ulang listener untuk channel baru

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
    }

    // Update UI Sidebar (menandai yang aktif)
    document.querySelectorAll('#chatListContainer div[data-conversation-id]').forEach(div => {
        if (div.dataset.conversationId === conversationId) {
            div.classList.add('bg-[#E9EFFD]', 'text-blue-600', 'font-semibold');
            div.classList.remove('text-gray-800');
        } else {
            div.classList.remove('bg-[#E9EFFD]', 'text-blue-600', 'font-semibold');
            div.classList.add('text-gray-800');
        }
    });

    // BARU: Tandai pesan sebagai telah dibaca (Optimistic UI)
    const badge = document.getElementById(`unread-badge-${conversationId}`);
    const countSpan = document.getElementById(`unread-count-${conversationId}`);
    if (badge && countSpan && parseInt(countSpan.textContent) > 0) {
        badge.style.display = 'none';
        countSpan.textContent = '0';
        // Kirim request ke server di background
        markConversationAsRead(conversationId);
    }

    try {
        const response = await fetch(`${API_URL}/api/chat/${conversationId}/messages`);
        if (!response.ok) throw new Error('Gagal memuat pesan');
        const messages = await response.json();

        if (messages.length === 0) {
            messageList.innerHTML = '<div class="p-6 text-center text-gray-500">Belum ada pesan di percakapan ini.</div>';
        } else {
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
                    if (messageDateString === today) dateLabel = 'Hari ini';
                    else if (messageDateString === yesterday) dateLabel = 'Kemarin';

                    messageHTML += createDateSeparatorHTML(dateLabel);
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

/** BARU: Fungsi untuk mengirim "mark as read" ke server */
async function markConversationAsRead(conversationId) {
    try {
        await fetch(`${API_URL}/api/chat/${conversationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });
    } catch (error) {
        console.error('Gagal menandai telah dibaca:', error);
    }
}

/** Kirim pesan baru */
async function handleSendMessage(e) {
    e.preventDefault();
    const content = messageInput.value.trim();
    if (!content || !currentConversationId) return;

    // Optimistic UI: Tampilkan pesan sementara
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
    messageInput.dispatchEvent(new Event('input')); // Toggle tombol mic/send

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

        updateSidebarOnNewMessage(result.data, false);

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
    // Hapus 'empty state' jika ada
    const emptyState = messageList.querySelector('.flex.h-full.items-center.justify-center');
    if (emptyState) emptyState.remove();
    const emptyState2 = messageList.querySelector('.p-6.text-center.text-gray-500');
    if (emptyState2) emptyState2.remove();

    // Cek dan tambahkan separator tanggal "Hari ini" jika perlu
    const today = new Date().toDateString();
    const allSeparators = messageList.querySelectorAll('div.flex.justify-center.items-center span');
    let lastSeparatorText = null;
    if (allSeparators.length > 0) {
        lastSeparatorText = allSeparators[allSeparators.length - 1].textContent.trim();
    }

    if (!lastSeparatorText || lastSeparatorText !== 'Hari ini') {
        if (new Date(message.created_at).toDateString() === today) {
            messageList.insertAdjacentHTML('beforeend', createDateSeparatorHTML('Hari ini'));
        }
    }

    const messageHTML = createMessageHTML(message);
    messageList.insertAdjacentHTML('beforeend', messageHTML);
    scrollToBottom();
}


// -----------------------------------------------------------------
// LANGKAH 5: Real-time (Laravel Echo)
// -----------------------------------------------------------------

/** BARU: Setup listener Laravel Echo */
function setupEchoListeners() {
    if (typeof Echo === 'undefined') {
        console.error('Laravel Echo not configured (Echo is undefined).');
        return;
    }

    // Hentikan listener lama jika ada (saat memulai chat baru)
    window.allConversations.forEach(conversation => {
        Echo.leave(`conversation.${conversation.id}`);
    });

    // Dengarkan di setiap channel percakapan
    window.allConversations.forEach(conversation => {
        Echo.private(`conversation.${conversation.id}`)
            .listen('NewMessage', (e) => {
                console.log('Pesan baru diterima via Echo:', e);
                handleNewMessage(e.message);
            });
    });

    console.log('Echo listeners setup for all conversations.');
}

/** BARU: Menangani pesan baru yang masuk dari Echo */
function handleNewMessage(message) {
    // Cek 1: Apakah kita sedang membuka percakapan ini?
    if (message.conversation_id === currentConversationId) {
        // Ya, langsung tampilkan pesan
        appendMessage(message);
        // Dan langsung tandai sudah dibaca
        markConversationAsRead(message.conversation_id);
        // Update sidebar (hanya preview, tanpa increment)
        updateSidebarOnNewMessage(message, false);
    }
    // Cek 2: Jika tidak sedang dibuka
    else {
        // Update sidebar (preview DAN increment unread count)
        updateSidebarOnNewMessage(message, true);
    }
}

/** BARU: Meng-update sidebar (preview & unread) saat ada pesan baru */
function updateSidebarOnNewMessage(message, incrementUnread) {
    const conversationId = message.conversation_id;

    // 1. Update Teks Preview
    const previewText = document.getElementById(`preview-${conversationId}`);
    if (previewText) {
        const senderPrefix = message.sender_id === AUTH_USER_ID ? 'Anda: ' : '';
        previewText.textContent = senderPrefix + message.content;
    }

    // 2. Update (Increment) "Buletan" Unread
    if (incrementUnread) {
        const badge = document.getElementById(`unread-badge-${conversationId}`);
        const countSpan = document.getElementById(`unread-count-${conversationId}`);
        if (badge && countSpan) {
            let currentCount = parseInt(countSpan.textContent) || 0;
            currentCount++;
            countSpan.textContent = currentCount;
            badge.style.display = 'block';
        }
    }

    // 3. Pindahkan item ke atas (INI ADALAH LOGIKA YANG DIPERBAIKI)
    const sidebarItem = document.querySelector(`#chatListContainer div[data-conversation-id="${conversationId}"]`);
    if (sidebarItem) {
        // Cari header grup (misal "Percakapan" atau "Ruang Kerja")
        let currentElement = sidebarItem;
        let header = null;

        // Loop ke atas mencari elemen 'header' sebelumnya
        while (currentElement.previousElementSibling) {
            currentElement = currentElement.previousElementSibling;
            // Asumsi header Anda memiliki kelas ini (sesuai dari loadConversations)
            if (currentElement.classList.contains('px-6', 'pt-4', 'pb-2')) {
                header = currentElement;
                break;
            }
        }

        // Jika header ditemukan, letakkan item chat tepat di bawahnya
        if (header) {
            header.after(sidebarItem);
        } else {
            // Jika tidak ada header (fallback), pindahkan ke paling atas
            sidebarItem.parentElement.prepend(sidebarItem);
        }
    }
}


// -----------------------------------------------------------------
// LANGKAH 6: Event Listeners
// -----------------------------------------------------------------

function setupInputListeners() {
    messageInput.addEventListener('input', function () {
        const hasText = this.value.trim().length > 0;
        micButton.style.display = hasText ? 'none' : 'flex';
        sendButton.style.display = hasText ? 'flex' : 'none';
    });

    sendMessageForm.addEventListener('submit', handleSendMessage);
    scrollToBottomBtn.addEventListener('click', scrollToBottom);

    chatContainer.addEventListener('scroll', function () {
        const isAtBottom = chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer.clientHeight + 100;
        scrollToBottomBtn.style.display = isAtBottom ? 'none' : 'block';
    });

    messageInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessageForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    });
}

function scrollToBottom() {
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Global function
window.selectConversation = function (conversationId) {
    loadMessages(conversationId);
}

// -----------------------------------------------------------------
// LANGKAH 7: Inisialisasi
// -----------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async function () {
    // 1. Baca data- attributes
    const chatPageContainer = document.getElementById('chat-page-container');
    if (!chatPageContainer) {
        console.error('Chat container not found. Make sure to add id="chat-page-container" and data- attributes.');
        return;
    }

    WORKSPACE_ID = chatPageContainer.dataset.workspaceId;

    // 🔥🔥🔥 PERBAIKAN DI SINI! 🔥🔥🔥
    // Kita biarkan AUTH_USER_ID sebagai string (UUID), JANGAN di-parseInt
    AUTH_USER_ID = chatPageContainer.dataset.authUserId;

    API_URL = chatPageContainer.dataset.apiUrl;
    CSRF_TOKEN = chatPageContainer.dataset.csrfToken;

    // 2. Ambil elemen DOM
    cacheDOMElements();

    // 3. Setup listener input
    setupInputListeners();

    // 4. Muat data percakapan
    await loadConversations();

    // 5. Setup listener Echo setelah data percakapan dimuat
    setupEchoListeners();

    // 6. Scroll ke bawah
    scrollToBottom();
});
