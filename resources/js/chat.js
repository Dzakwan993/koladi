// resources/js/chat.js - UPDATED SMOOTH VERSION

// -----------------------------------------------------------------
// VARIABLES GLOBAL - WORKSPACE SCOPE
// -----------------------------------------------------------------
let WORKSPACE_ID, AUTH_USER_ID, API_URL, CSRF_TOKEN, CHAT_SCOPE;
let currentConversationId = null;
window.allConversations = [];
window.pendingDeletes = new Set();
window.pendingMessages = new Set();

let selectedFiles = [];
let isSending = false;
let currentReplyToMessage = null;
let currentEditMessage = null;
let loadedMessages = new Map();

// -----------------------------------------------------------------
// Typing Variables
// -----------------------------------------------------------------
let typingTimeout;
let isTyping = false;
let currentlyTypingUsers = new Set();

// -----------------------------------------------------------------
// INITIALIZATION - WORKSPACE SPECIFIC
// -----------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('chat-page-container');
    if (!container) return;

    // Set global variables dari data attributes
    WORKSPACE_ID = container.dataset.workspaceId;
    AUTH_USER_ID = container.dataset.authUserId;
    API_URL = container.dataset.apiUrl;
    CSRF_TOKEN = container.dataset.csrfToken;
    CHAT_SCOPE = 'workspace';

    console.log('Workspace Chat Initialized:', {
        WORKSPACE_ID,
        AUTH_USER_ID,
        CHAT_SCOPE
    });

    cacheDOMElements();
    setupInputListeners();
    await loadWorkspaceConversations();
    setupEchoListeners();
    scrollToBottom();
});

// -----------------------------------------------------------------
// WORKSPACE SPECIFIC FUNCTIONS
// -----------------------------------------------------------------

async function loadWorkspaceConversations() {
    const chatListContainer = document.getElementById('chatListContainer');
    chatListContainer.innerHTML = '<div class="p-4 text-center text-gray-500">Memuat percakapan workspace...</div>';

    try {
        const response = await fetch(`${API_URL}/api/workspace/${WORKSPACE_ID}/chat-data`);
        if (!response.ok) throw new Error('Gagal memuat percakapan workspace');

        const data = await response.json();

        window.allConversations = [
            data.main_group,
            ...(data.conversations || [])
        ].filter(Boolean);

        // 🔥 RENDER sidebar (main group + conversations + members)
        renderWorkspaceSidebar(data);

    } catch (error) {
        console.error('Error loading workspace conversations:', error);
        chatListContainer.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat percakapan workspace.</div>';
    }
}

function renderWorkspaceSidebar(data) {
    const chatListContainer = document.getElementById('chatListContainer');

    let html = '';

    // 1. RUANG KERJA (Main Group)
    if (data.main_group) {
        html += `
            <div class="px-6 pt-4 pb-2">
                <span class="text-xs font-semibold text-gray-500 uppercase">Ruang Kerja</span>
            </div>
            ${createConversationHTML(data.main_group)}
        `;
    }

    // 2. ANGGOTA TIM (Semua Members)
    if (data.members && data.members.length > 0) {
        html += `
            <div class="px-6 pt-4 pb-2 mt-2">
                <span class="text-xs font-semibold text-gray-500 uppercase">Anggota Tim</span>
            </div>
        `;

        // Tampilkan SEMUA member tanpa filter
        data.members.forEach(member => {
            html += createMemberHTML(member);
        });
    }

    if (html === '') {
        chatListContainer.innerHTML = '<div class="p-6 text-center text-gray-500">Belum ada data percakapan.</div>';
        return;
    }

    chatListContainer.innerHTML = html;
}

// -----------------------------------------------------------------
// OVERRIDE FUNCTIONS UNTUK WORKSPACE SCOPE
// -----------------------------------------------------------------

// 🔥 Load messages untuk workspace
async function loadMessages(conversationId) {
    if (!conversationId) return;

    currentConversationId = conversationId;
    const messageList = document.getElementById('messageList');
    messageList.innerHTML = '<div class="p-6 text-center text-gray-500">Memuat pesan...</div>';

    const chatInputBar = document.getElementById('chatInputBar');
    chatInputBar.style.display = 'block';

    try {
        const response = await fetch(`${API_URL}/api/chat/${conversationId}/messages`);
        if (!response.ok) throw new Error('Gagal memuat pesan');

        const messages = await response.json();

        messages.forEach(msg => {
            if (msg.reply_to) {
                msg.replyTo = msg.reply_to;
            }
        });

        messages.forEach(msg => {
            loadedMessages.set(msg.id, msg);
        });

        if (messages.length === 0) {
            messageList.innerHTML = '<div class="p-6 text-center text-gray-500">Belum ada pesan di percakapan ini.</div>';
        } else {
            renderMessages(messages);
        }

        // Update header title
        const conv = window.allConversations.find(c => c.id === conversationId);
        if (conv) {
            let chatName = conv.name;
            if (conv.type === 'private') {
                const other = conv.participants.find(p => p.user_id !== AUTH_USER_ID);
                chatName = other ? other.user.full_name : 'Unknown';
            }
            document.getElementById('chatHeaderTitle').textContent = chatName;
        }

        refreshSidebarHighlight();
        await markConversationAsRead(conversationId);

        chatContainer.scrollTop = chatContainer.scrollHeight;
        scrollToBottomBtn.style.display = 'none';

    } catch (error) {
        console.error('Error loading messages:', error);
        messageList.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat pesan.</div>';
    }
}

// 🔥 Start chat dengan anggota workspace
window.startChatWithUser = async function (userId, userName) {
    const chatHeaderTitle = document.getElementById('chatHeaderTitle');
    chatHeaderTitle.textContent = `Membuka chat dengan ${userName}...`;

    const messageList = document.getElementById('messageList');
    messageList.innerHTML = '<div class="p-6 text-center text-gray-500">Memuat...</div>';

    const chatInputBar = document.getElementById('chatInputBar');
    chatInputBar.style.display = 'none';

    try {
        // Cek existing chat
        const existingChat = window.allConversations.find(conv =>
            conv.type === 'private' &&
            conv.participants.some(p => p.user_id === userId)
        );

        if (existingChat) {
            await loadMessages(existingChat.id);
            return;
        }

        // Create new
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

        await loadWorkspaceConversations();
        await loadMessages(newConversationId);
        setupEchoListeners();

    } catch (error) {
        console.error(error);
        chatHeaderTitle.textContent = 'Gagal';
        messageList.innerHTML = '<div class="p-6 text-center text-red-500">Gagal membuat percakapan.</div>';
    }
}

// 🔥 Send message untuk workspace
async function handleSendMessage(e) {
    e.preventDefault();

    if (isSending) return;

    const messageInput = document.getElementById('messageInput');
    const content = messageInput.value.trim();

    if (!content && selectedFiles.length === 0) return;
    if (!currentConversationId) return;

    isSending = true;

    const sendButton = document.getElementById('sendButton');
    const originalSendHTML = sendButton.innerHTML;
    sendButton.innerHTML = `<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>`;
    sendButton.disabled = true;
    messageInput.disabled = true;

    const uploadButton = document.getElementById('uploadButton');
    uploadButton.disabled = true;

    const formData = new FormData();
    formData.append('conversation_id', currentConversationId);
    formData.append('content', content);

    if (currentReplyToMessage) {
        formData.append('reply_to_message_id', currentReplyToMessage);
    }

    selectedFiles.forEach((file) => {
        formData.append('files[]', file, file.name);
    });

    messageInput.value = '';
    selectedFiles = [];
    renderFilePreview();

    if (currentReplyToMessage) {
        cancelReply();
    }

    updateSendButton();

    try {
        const response = await fetch(`${API_URL}/api/chat/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Gagal mengirim pesan: ${response.status}`);
        }

        const result = await response.json();

        updateSidebarOnNewMessage(result.data, false);

        // 🔥 Smart scroll setelah kirim pesan
        setTimeout(() => {
            smartScrollToBottom();
        }, 200);

    } catch (error) {
        console.error('❌ Error sending message:', error);
        await Swal.fire({
            title: 'Gagal Mengirim',
            text: 'Pesan gagal terkirim. Silakan coba lagi.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } finally {
        isSending = false;
        sendButton.innerHTML = originalSendHTML;
        sendButton.disabled = false;
        messageInput.disabled = false;
        uploadButton.disabled = false;
    }
}

// -----------------------------------------------------------------
// 🔥 SMART SCROLL FUNCTION
// -----------------------------------------------------------------
function smartScrollToBottom() {
    const scrollHeight = chatContainer.scrollHeight;
    const clientHeight = chatContainer.clientHeight;
    const scrollTop = chatContainer.scrollTop;
    const distanceFromBottom = scrollHeight - scrollTop - clientHeight;

    // 🔥 HANYA scroll jika user sudah dekat dengan bawah (dalam 200px)
    if (distanceFromBottom < 200) {
        chatContainer.scrollTo({
            top: scrollHeight,
            behavior: 'smooth'
        });

        // Sembunyikan tombol scroll setelah scroll
        setTimeout(() => {
            scrollToBottomBtn.style.display = 'none';
        }, 300);
    } else {
        // 🔥 Jika user sedang scroll ke atas, tampilkan tombol scroll
        scrollToBottomBtn.style.display = 'flex';
    }
}

// Cache DOM Elements
// -----------------------------------------------------------------
let container, chatContainer, messageList, chatListContainer, chatHeaderTitle,
    chatInputBar, sendMessageForm, messageInput, scrollToBottomBtn,
    sendButton, uploadButton, fileInput, filePreviewContainer, filePreviewList, dropZone;

function cacheDOMElements() {
    container = document.getElementById('chat-page-container');
    chatContainer = document.getElementById('chatContainer');
    messageList = document.getElementById('messageList');
    chatListContainer = document.getElementById('chatListContainer');
    chatHeaderTitle = document.getElementById('chatHeaderTitle');
    chatInputBar = document.getElementById('chatInputBar');
    sendMessageForm = document.getElementById('sendMessageForm');
    messageInput = document.getElementById('messageInput');
    scrollToBottomBtn = document.getElementById('scrollToBottom');
    sendButton = document.getElementById('sendButton');
    uploadButton = document.getElementById('uploadButton');
    fileInput = document.getElementById('fileInput');
    filePreviewContainer = document.getElementById('filePreviewContainer');
    filePreviewList = document.getElementById('filePreviewList');
    dropZone = document.getElementById('dropZone');
}

// Helper Functions
// -----------------------------------------------------------------
function getInitials(name) {
    if (!name) return '??';
    const names = name.split(' ');
    if (names.length === 1) return name.substring(0, 2).toUpperCase();
    return (names[0][0] + names[names.length - 1][0]).toUpperCase();
}

function formatTime(dateTimeString) {
    if (!dateTimeString) return '';
    const date = new Date(dateTimeString);
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
}

function getAvatarUrl(user) {
    if (!user) return null;
    if (user.avatar) {
        if (user.avatar.startsWith('http')) {
            return user.avatar;
        } else {
            if (user.avatar.startsWith('avatars/')) {
                return `${API_URL}/storage/${user.avatar}`;
            } else {
                return `${API_URL}/storage/avatars/${user.avatar}`;
            }
        }
    }
    return null;
}

function fixFileUrl(fileUrl) {
    if (!fileUrl) return '';
    
    const baseUrl = window.location.origin;
    
    if (fileUrl.startsWith('chat_files/')) {
        return `${baseUrl}/storage/${fileUrl}`;
    }
    
    if (fileUrl.startsWith('storage/')) {
        return `${baseUrl}/${fileUrl}`;
    }
    
    return fileUrl;
}

function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    }
    return bytes + ' B';
}

function getFileIcon(fileType) {
    // 🔥 FIX: Tambahkan null check
    if (!fileType || typeof fileType !== 'string') {
        return `<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>`;
    }

    if (fileType.startsWith('image/')) {
        return `<svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>`;
    } else if (fileType.startsWith('video/')) {
        return `<svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>`;
    } else if (fileType === 'application/pdf') {
        return `<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>`;
    } else {
        return `<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>`;
    }
}

function getReadStatusHTML(message) {
    const isRead = message.is_read;
    if (isRead) {
        return `
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <svg class="w-3.5 h-3.5 text-blue-500 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            `;
    } else {
        return `
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <svg class="w-3.5 h-3.5 text-gray-400 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            `;
    }
}

function createDateSeparatorHTML(dateLabel) {
    return `
    <div class="flex justify-center items-center my-4">
        <span class="bg-white border border-gray-200 rounded-full px-4 py-1 text-xs text-gray-500 shadow-sm">
            ${dateLabel}
        </span>
    </div>
    `;
}

function detectAndCreateLinks(text) {
    if (!text) return '';

    const urlRegex = /(https?:\/\/[^\s<]+[^<.,:;"')\]\s])/g;

    return text.replace(urlRegex, function (url) {
        let cleanUrl = url;
        const punctuation = ['.', ',', '!', '?', ';', ':', ')', ']', '}'];

        while (punctuation.includes(cleanUrl.slice(-1))) {
            cleanUrl = cleanUrl.slice(0, -1);
        }

        try {
            const urlObj = new URL(cleanUrl);
            let displayText = urlObj.hostname.replace('www.', '');

            if (urlObj.pathname !== '/' && displayText.length + urlObj.pathname.length < 30) {
                displayText += urlObj.pathname;
            }

            return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer"
                        class="text-blue-500 hover:text-blue-600 hover:underline break-words"
                        title="${cleanUrl}">${displayText}</a>`;

        } catch (e) {
            return url;
        }
    });
}

function updateSendButton() {
    const hasText = messageInput.value.trim().length > 0;
    const hasFiles = selectedFiles.length > 0;
    sendButton.style.display = (hasText || hasFiles) ? 'flex' : 'none';
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function scrollToBottom() {
    chatContainer.scrollTop = chatContainer.scrollHeight;

    setTimeout(() => {
        scrollToBottomBtn.style.display = 'none';
    }, 300);
}

// Create HTML Functions
// -----------------------------------------------------------------
function createConversationHTML(conversation) {
    let chatName = conversation.name || 'Unnamed';
    let chatAvatar = null;
    let chatAvatarInitials = getInitials(conversation.name);
    let avatarBgClass = 'bg-blue-200 text-blue-800';

    if (conversation.type === 'private') {
        const otherParticipant = conversation.participants?.find(p => p.user_id !== AUTH_USER_ID);
        if (otherParticipant) {
            chatName = otherParticipant.user?.full_name || 'Unknown';
            chatAvatarInitials = getInitials(otherParticipant.user?.full_name);
            chatAvatar = getAvatarUrl(otherParticipant.user);
            avatarBgClass = 'bg-indigo-100 text-indigo-800';
        }
    }

    const avatarHTML = chatAvatar
        ? `<img src="${chatAvatar}" alt="${chatName}" class="w-10 h-10 rounded-full object-cover border border-gray-200">`
        : `<div class="w-10 h-10 rounded-full ${avatarBgClass} flex items-center justify-center font-bold text-sm">${chatAvatarInitials}</div>`;

    const lastMessage = conversation.last_message;
    let lastMessageText = 'Belum ada pesan';

    // 🔥 FIX: Tambahkan null check untuk semua properti lastMessage
    if (lastMessage) {
        const isDeleted = lastMessage.message_type === 'deleted' ||
            (lastMessage.deleted_at !== null && lastMessage.deleted_at !== undefined) ||
            (lastMessage.content === null && (!lastMessage.attachments || lastMessage.attachments.length === 0));

        if (isDeleted) {
            if (lastMessage.sender_id === AUTH_USER_ID) {
                lastMessageText = 'Kamu telah menghapus pesan ini';
            } else {
                const senderName = lastMessage.sender?.full_name?.split(' ')[0] || 'User';
                lastMessageText = `${senderName}: Pesan telah dihapus`;
            }
        }
        else if (conversation.type === 'group') {
            let senderName = 'Anda';
            if (lastMessage.sender_id !== AUTH_USER_ID) {
                if (lastMessage.sender?.full_name) {
                    senderName = lastMessage.sender.full_name.split(' ')[0];
                } else {
                    senderName = 'User';
                }
            }

            if (lastMessage.attachments && lastMessage.attachments.length > 0) {
                // 🔥 FIX: Tambahkan null check untuk content
                if (lastMessage.content && lastMessage.content.trim() !== '') {
                    lastMessageText = `${senderName}: ${lastMessage.content}`;
                } else {
                    const fileCount = lastMessage.attachments.length;
                    const fileType = lastMessage.attachments[0]?.file_type || 'file';

                    if (fileType.startsWith('image/')) {
                        lastMessageText = `${senderName}: 📷 Gambar`;
                    } else if (fileType.startsWith('video/')) {
                        lastMessageText = `${senderName}: 🎬 Video`;
                    } else if (fileType === 'application/pdf') {
                        lastMessageText = `${senderName}: 📄 PDF`;
                    } else {
                        lastMessageText = `${senderName}: 📎 ${fileCount} file`;
                    }
                }
            } else {
                // 🔥 FIX: Tambahkan default value jika content null
                lastMessageText = `${senderName}: ${lastMessage.content || 'Mengirim pesan'}`;
            }
        } else {
            const senderPrefix = lastMessage.sender_id === AUTH_USER_ID ? 'Anda: ' : '';
            if (lastMessage.attachments && lastMessage.attachments.length > 0) {
                // 🔥 FIX: Tambahkan null check untuk content
                if (lastMessage.content && lastMessage.content.trim() !== '') {
                    lastMessageText = senderPrefix + lastMessage.content;
                } else {
                    const fileCount = lastMessage.attachments.length;
                    const fileType = lastMessage.attachments[0]?.file_type || 'file';

                    if (fileType.startsWith('image/')) {
                        lastMessageText = senderPrefix + '📷 Gambar';
                    } else if (fileType.startsWith('video/')) {
                        lastMessageText = senderPrefix + '🎬 Video';
                    } else if (fileType === 'application/pdf') {
                        lastMessageText = senderPrefix + '📄 PDF';
                    } else {
                        lastMessageText = senderPrefix + `📎 ${fileCount} file`;
                    }
                }
            } else {
                // 🔥 FIX: Tambahkan default value jika content null
                lastMessageText = senderPrefix + (lastMessage.content || 'Mengirim pesan');
            }
        }
    }

    const unreadCount = conversation.unread_count || 0;
    const isActive = currentConversationId === conversation.id;
    const activeClasses = isActive
        ? 'bg-blue-100 border-l-4 border-blue-500'
        : 'hover:bg-gray-50';

    return `
    <div class="px-6 py-3 cursor-pointer ${activeClasses} transition-all duration-200"
         data-conversation-id="${conversation.id}"
         onclick="selectConversation('${conversation.id}')">
        <div class="flex items-center">
            <div class="relative flex-shrink-0">
                ${avatarHTML}
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <h4 class="text-sm font-semibold truncate ${isActive ? 'text-blue-700' : 'text-gray-800'}">${chatName}</h4>
                <p id="preview-${conversation.id}" class="text-xs truncate ${isActive ? 'text-blue-600' : 'text-gray-500'}">${lastMessageText}</p>
            </div>
            <div id="unread-badge-${conversation.id}" class="ml-2 flex-shrink-0" style="${unreadCount > 0 ? 'display: block;' : 'display: none;'}">
                <div class="min-w-[18px] h-[18px] rounded-full bg-blue-500 flex items-center justify-center">
                    <span id="unread-count-${conversation.id}" class="text-[10px] font-semibold text-white px-1">${unreadCount}</span>
                </div>
            </div>
        </div>
    </div>`;
}

function createMemberHTML(member) {
    const initials = getInitials(member.full_name);
    const memberAvatar = getAvatarUrl(member);

    const avatarHTML = memberAvatar
        ? `<img src="${memberAvatar}" alt="${member.full_name}" class="w-10 h-10 rounded-full object-cover border border-gray-200">`
        : `<div class="w-10 h-10 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center font-bold text-sm">${initials}</div>`;

    // 🔥 CARI private conversation dengan member ini
    const privateChat = window.allConversations.find(conv =>
        conv.type === 'private' &&
        conv.participants.some(p => p.user_id == member.id)
    );

    // 🔥 SET default values
    let previewText = 'Mulai percakapan';
    let unreadCount = 0;

    // 🔥 JIKA ADA private chat, ambil preview dari last_message
    if (privateChat && privateChat.last_message) {
        const lastMessage = privateChat.last_message;

        const isDeleted = lastMessage.message_type === 'deleted' ||
            lastMessage.deleted_at !== null;

        if (isDeleted) {
            previewText = lastMessage.sender_id == AUTH_USER_ID
                ? 'Kamu telah menghapus pesan ini'
                : 'Pesan telah dihapus';
        } else {
            const senderPrefix = lastMessage.sender_id == AUTH_USER_ID ? 'Anda: ' : '';

            if (lastMessage.attachments && lastMessage.attachments.length > 0) {
                if (lastMessage.content && lastMessage.content.trim() !== '') {
                    previewText = senderPrefix + lastMessage.content;
                } else {
                    const fileType = lastMessage.attachments[0].file_type;
                    if (fileType.startsWith('image/')) {
                        previewText = senderPrefix + '📷 Gambar';
                    } else if (fileType.startsWith('video/')) {
                        previewText = senderPrefix + '🎬 Video';
                    } else if (fileType === 'application/pdf') {
                        previewText = senderPrefix + '📄 PDF';
                    } else {
                        previewText = senderPrefix + `📎 ${lastMessage.attachments.length} file`;
                    }
                }
            } else if (lastMessage.content) {
                previewText = senderPrefix + lastMessage.content;
            }
        }

        unreadCount = privateChat.unread_count || 0;
    }

    // 🔥 CEK apakah ini conversation yang sedang aktif
    const isActive = privateChat && currentConversationId === privateChat.id;
    const activeClasses = isActive
        ? 'bg-blue-100 border-l-4 border-blue-500'
        : 'hover:bg-gray-50';

    return `
    <div class="px-6 py-3 cursor-pointer ${activeClasses} transition-all duration-200"
         data-member-id="${member.id}"
         ${privateChat ? `data-conversation-id="${privateChat.id}"` : ''}
         onclick="startChatWithUser('${member.id}', '${member.full_name}')">
        <div class="flex items-center">
            <div class="relative flex-shrink-0">
                ${avatarHTML}
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <h4 class="text-sm font-semibold truncate ${isActive ? 'text-blue-700' : 'text-gray-800'}">${member.full_name}</h4>
                <p id="preview-member-${member.id}" class="text-xs truncate ${isActive ? 'text-blue-600' : 'text-gray-500'}">${previewText}</p>
            </div>
            <div id="unread-badge-member-${member.id}" class="ml-2 flex-shrink-0" style="${unreadCount > 0 ? 'display: block;' : 'display: none;'}">
                <div class="min-w-[18px] h-[18px] rounded-full bg-blue-500 flex items-center justify-center">
                    <span id="unread-count-member-${member.id}" class="text-[10px] font-semibold text-white px-1">${unreadCount}</span>
                </div>
            </div>
        </div>
    </div>`;
}

function createMessageHTML(message) {
    const isSender = message.sender_id === AUTH_USER_ID;
    const senderName = isSender ? 'Anda' : (message.sender ? message.sender.full_name : 'User');
    const initials = getInitials(senderName);

    const senderAvatar = message.sender ? getAvatarUrl(message.sender) : null;
    const avatarHTML = senderAvatar
        ? `<img src="${senderAvatar}" alt="${senderName}" class="w-8 h-8 rounded-full object-cover border border-gray-200 flex-shrink-0">`
        : `<div class="w-8 h-8 rounded-full ${isSender ? 'bg-blue-200 text-blue-800' : 'bg-gray-200 text-gray-800'} flex items-center justify-center font-bold text-xs flex-shrink-0">${initials}</div>`;

    const time = formatTime(message.created_at);
    const editIndicator = message.is_edited ? `<span class="text-xs text-gray-400 ml-2">(diedit)</span>` : '';

    const isDeleted = message.message_type === 'deleted' ||
        (message.deleted_at !== null && message.deleted_at !== undefined) ||
        (message.content === null && (!message.attachments || message.attachments.length === 0));

    // 🔥 JIKA DELETED, RETURN EARLY
    if (isDeleted) {
        const deletedText = isSender ? 'Kamu telah menghapus pesan ini' : 'Pesan ini telah dihapus';

        if (isSender) {
            return `
            <div id="${message.id}" class="flex items-start justify-end deleted-message mb-4">
                <div class="flex flex-col items-end max-w-[70%]">
                    <div class="flex items-center justify-end gap-2 mb-1">
                        <span class="text-xs text-gray-500">${time}</span>
                        <span class="font-semibold text-gray-700 text-sm">Anda</span>
                    </div>
                    <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-br-md px-4 py-3 shadow-sm italic">
                        <p class="text-sm">${deletedText}</p>
                    </div>
                </div>
                <div class="flex-shrink-0 ml-3">
                    ${avatarHTML}
                </div>
            </div>
        `;
        } else {
            return `
            <div id="${message.id}" class="flex items-start justify-start deleted-message mb-4">
                <div class="flex-shrink-0 mr-3">
                    ${avatarHTML}
                </div>
                <div class="flex flex-col items-start max-w-[70%]">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-700 text-sm">${senderName}</span>
                        <span class="text-xs text-gray-500">${time}</span>
                    </div>
                    <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm italic">
                        <p class="text-sm">${deletedText}</p>
                    </div>
                </div>
            </div>
        `;
        }
    }

    // Attachments
    let attachmentsHTML = '';
    if (message.attachments && message.attachments.length > 0) {
        attachmentsHTML = '<div class="mt-2 space-y-2">';
        message.attachments.forEach(att => {
            // 🔥 FIX: Tambahkan null check untuk file_type
            const fileType = att.file_type || '';
            const isImage = fileType && fileType.startsWith('image/');
            const isVideo = fileType && fileType.startsWith('video/');
            const isPDF = fileType === 'application/pdf';

            if (att.uploading && att.preview_url && isImage) {
                attachmentsHTML += `
                <div class="relative">
                    <img src="${att.preview_url}" alt="${att.file_name || 'File'}"
                        class="max-w-xs rounded-xl shadow-md opacity-70">
                    <div class="absolute inset-0 bg-black bg-opacity-30 rounded-xl flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
                    </div>
                    <p class="text-xs text-gray-300 mt-1">Mengunggah...</p>
                </div>
            `;
            } else if (att.uploading) {
                attachmentsHTML += `
                <div class="flex items-center gap-2 bg-white bg-opacity-20 rounded-lg p-3">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    <span class="text-sm">Mengunggah ${att.file_name || 'file'}...</span>
                </div>
            `;
            } else if (isImage) {
                const imageUrl = fixFileUrl(att.file_url);
                attachmentsHTML += `
                <div class="relative group max-w-sm">
                    <img src="${imageUrl}"
                         alt="${att.file_name || 'Image'}"
                         class="rounded-xl shadow-md cursor-pointer max-h-96 object-cover w-full"
                         onclick="openImageModal('${imageUrl}', '${att.file_name || 'image'}')"
                         loading="lazy">
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="event.stopPropagation(); downloadImage('${imageUrl}', '${att.file_name || 'image'}')"
                                class="bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition"
                                title="Download gambar">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            } else {
                const fileUrl = fixFileUrl(att.file_url);
                const fileIcon = getFileIcon(fileType); // 🔥 Sekarang aman karena sudah di-check
                const fileSize = formatFileSize(att.file_size || 0);

                attachmentsHTML += `
                <div class="bg-white border border-gray-200 rounded-lg p-3 max-w-xs">
                    <a href="${fileUrl}" target="_blank" class="flex items-center gap-3 hover:bg-gray-50 rounded-lg p-2 transition">
                        <div class="flex-shrink-0">
                            ${fileIcon}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">${att.file_name || 'File'}</p>
                            <p class="text-xs text-gray-500">${fileSize}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            `;
            }
        });
        attachmentsHTML += '</div>';
    }

    let replyPreviewHTML = '';
    if (message.reply_to_message_id) {
        if (message.reply_to && typeof message.reply_to === 'object') {
            const repliedMessage = message.reply_to;
            const repliedSenderName = repliedMessage.sender_id === AUTH_USER_ID ?
                'Anda' : (repliedMessage.sender?.full_name || 'User');

            const isRepliedMessageDeleted = repliedMessage.message_type === 'deleted' ||
                (repliedMessage.deleted_at !== null && repliedMessage.deleted_at !== undefined);

            let repliedContent = '';
            let repliedAttachmentIcon = '';

            if (isRepliedMessageDeleted) {
                repliedContent = 'Pesan telah dihapus';
            } else if (repliedMessage.attachments && repliedMessage.attachments.length > 0) {
                const fileCount = repliedMessage.attachments.length;
                // 🔥 FIX: Tambahkan null check untuk file_type
                const fileType = repliedMessage.attachments[0]?.file_type || '';

                if (fileType.startsWith('image/')) {
                    repliedContent = 'Gambar';
                    repliedAttachmentIcon = '🖼️';
                } else if (fileType.startsWith('video/')) {
                    repliedContent = 'Video';
                    repliedAttachmentIcon = '🎬';
                } else if (fileType === 'application/pdf') {
                    repliedContent = 'PDF';
                    repliedAttachmentIcon = '📄';
                } else {
                    repliedContent = `${fileCount} file`;
                    repliedAttachmentIcon = '📎';
                }

                // 🔥 FIX: Tambahkan null check untuk content
                if (repliedMessage.content && repliedMessage.content.trim() !== '') {
                    repliedContent = repliedMessage.content;
                }
            } else {
                repliedContent = repliedMessage.content || 'Pesan kosong';
            }

            const displayContent = repliedContent.length > 50 ?
                repliedContent.substring(0, 50) + '...' : repliedContent;

            replyPreviewHTML = `
            <div class="reply-info mb-2 p-2 bg-blue-50 rounded-lg border-l-4 border-blue-400 cursor-pointer hover:bg-blue-100 transition-colors"
                 onclick="scrollToMessage('${message.reply_to_message_id}')">
                <div class="flex items-start gap-2">
                    <div class="text-blue-500 mt-0.5 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-blue-700 mb-1">
                            Membalas ${repliedSenderName}
                        </div>
                        <div class="text-xs text-blue-600 truncate flex items-center gap-1">
                            ${repliedAttachmentIcon ? `<span>${repliedAttachmentIcon}</span>` : ''}
                            <span class="truncate">${displayContent}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        } else {
            replyPreviewHTML = `
            <div class="reply-info mb-2 p-2 bg-gray-50 rounded-lg border-l-4 border-gray-300">
                <div class="flex items-start gap-2">
                    <div class="text-gray-400 mt-0.5 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-gray-500 mb-1">
                            Membalas pesan
                        </div>
                        <div class="text-xs text-gray-400 italic">
                            Data pesan tidak tersedia
                        </div>
                    </div>
                </div>
            </div>
        `;
        }
    }

    let actionButtonsHTML = '';
    if (!isDeleted) {
        if (isSender) {
            actionButtonsHTML = `
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button class="edit-message-btn text-gray-400 hover:text-blue-500 p-1 rounded"
                    title="Edit pesan"
                    onclick="startEditMessage('${message.id}')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <button class="reply-message-btn text-gray-400 hover:text-green-500 p-1 rounded"
                    title="Balas pesan"
                    onclick="startReplyMessage('${message.id}')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
            </button>
            <button class="delete-message-btn text-gray-400 hover:text-red-500 p-1 rounded"
                    title="Hapus pesan"
                    onclick="deleteMessage('${message.id}')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    `;
        } else {
            actionButtonsHTML = `
                <button class="reply-message-btn opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-green-500 p-1 rounded"
                        title="Balas pesan"
                        onclick="startReplyMessage('${message.id}')">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </button>
            `;
        }
    }

    let contentHTML = '';
    if (message.content && message.content.trim() !== '') {
        const processedContent = detectAndCreateLinks(message.content);
        contentHTML = `<div class="message-content text-sm" style="word-break: break-word;">${processedContent}</div>`;
    } else if (message.attachments && message.attachments.length > 0) {
        const fileCount = message.attachments.length;
        if (fileCount === 1) {
            // 🔥 FIX: Tambahkan null check untuk file_type
            const fileType = message.attachments[0]?.file_type || '';

            if (fileType.startsWith('image/')) {
                contentHTML = `<div class="message-content text-sm italic">📷 Gambar</div>`;
            } else if (fileType.startsWith('video/')) {
                contentHTML = `<div class="message-content text-sm italic">🎬 Video</div>`;
            } else if (fileType === 'application/pdf') {
                contentHTML = `<div class="message-content text-sm italic">📄 PDF</div>`;
            } else {
                contentHTML = `<div class="message-content text-sm italic">📎 File</div>`;
            }
        } else {
            contentHTML = `<div class="message-content text-sm italic">📎 ${fileCount} files</div>`;
        }
    }

    // Pesan normal - BUBBLE MENYESUAIKAN KONTEN
    if (isSender) {
        return `
            <div id="${message.id}" class="flex items-start justify-end group message-new mb-4">
                <div class="flex flex-col items-end max-w-[75%] min-w-0">
                    <div class="flex items-center justify-end gap-2 mb-1 w-full">
                        ${actionButtonsHTML}
                        <span class="text-xs text-gray-500 whitespace-nowrap">${time} ${editIndicator}</span>
                        <div class="flex items-center read-status">
                            ${getReadStatusHTML(message)}
                        </div>
                        <span class="font-semibold text-gray-700 text-sm whitespace-nowrap">Anda</span>
                    </div>

                    <div class="bg-blue-100  rounded-2xl rounded-br-md px-4 py-3 shadow-sm w-auto min-w-0 max-w-full">
                        ${replyPreviewHTML}
                        ${contentHTML}
                        ${attachmentsHTML}
                    </div>
                </div>
                <div class="flex-shrink-0 ml-3">
                    ${avatarHTML}
                </div>
            </div>
        `;
    } else {
        return `
            <div id="${message.id}" class="flex items-start justify-start group message-new mb-4">
                <div class="flex-shrink-0 mr-3">
                    ${avatarHTML}
                </div>
                <div class="flex flex-col items-start max-w-[75%] min-w-0">
                    <div class="flex items-center gap-2 mb-1 w-full">
                        <span class="font-semibold text-gray-700 text-sm whitespace-nowrap">${senderName}</span>
                        <span class="text-xs text-gray-500 whitespace-nowrap">${time} ${editIndicator}</span>
                        ${actionButtonsHTML}
                    </div>

                    <div class="bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm w-auto min-w-0 max-w-full">
                        ${replyPreviewHTML}
                        ${contentHTML}
                        ${attachmentsHTML}
                    </div>
                </div>
            </div>
        `;
    }
}

function appendMessage(message) {
    const emptyState = messageList.querySelector('.flex.h-full.items-center.justify-center');
    if (emptyState) emptyState.remove();
    const emptyState2 = messageList.querySelector('.p-6.text-center.text-gray-500');
    if (emptyState2) emptyState2.remove();

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

    setTimeout(() => {
        smartScrollToBottom();
    }, 100);
}

// File Handling Functions
// -----------------------------------------------------------------
function renderFilePreview() {
    if (selectedFiles.length === 0) {
        filePreviewContainer.style.display = 'none';
        return;
    }

    filePreviewContainer.style.display = 'block';
    filePreviewList.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        const isImage = file.type.startsWith('image/');
        let previewHTML = '';

        if (isImage) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.getElementById(`file-preview-${index}`);
                if (preview) {
                    preview.querySelector('img').src = e.target.result;
                }
            };
            reader.readAsDataURL(file);

            previewHTML = `
                    <div id="file-preview-${index}" class="relative bg-white rounded-lg border border-gray-200 p-2 w-24 h-24">
                        <img src="" alt="${file.name}" class="w-full h-16 object-cover rounded">
                        <button type="button" onclick="removeFile(${index})"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                            ×
                        </button>
                        <p class="text-xs text-gray-600 mt-1 truncate">${file.name}</p>
                    </div>
                `;
        } else {
            previewHTML = `
                    <div id="file-preview-${index}" class="relative bg-white rounded-lg border border-gray-200 p-3 flex items-center gap-2 max-w-xs">
                        <div class="flex-shrink-0">
                            ${getFileIcon(file.type)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">${file.name}</p>
                            <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                        </div>
                        <button type="button" onclick="removeFile(${index})"
                            class="flex-shrink-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600">
                            ×
                        </button>
                    </div>
                `;
        }

        filePreviewList.insertAdjacentHTML('beforeend', previewHTML);
    });

    updateSendButton();
}

function getMessageDataById(messageId) {
    if (loadedMessages.has(messageId)) {
        return loadedMessages.get(messageId);
    }

    const messageElement = document.getElementById(messageId);
    if (!messageElement) return null;

    return {
        id: messageId,
        sender_id: messageElement.classList.contains('justify-end') ? AUTH_USER_ID : 'other',
        sender: {
            full_name: messageElement.querySelector('.font-semibold')?.textContent || 'User'
        },
        content: messageElement.querySelector('.message-content')?.textContent || '',
        attachments: []
    };
}

function renderMessages(messages) {
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

async function markConversationAsRead(conversationId) {
    try {
        const response = await fetch(`${API_URL}/api/chat/${conversationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const badge = document.getElementById(`unread-badge-${conversationId}`);
            const countSpan = document.getElementById(`unread-count-${conversationId}`);
            if (badge && countSpan) {
                badge.style.display = 'none';
                countSpan.textContent = '0';
            }

            const conversation = window.allConversations.find(c => c.id === conversationId);
            if (conversation && conversation.type === 'private') {
                const otherParticipant = conversation.participants.find(p => p.user_id != AUTH_USER_ID);
                if (otherParticipant) {
                    const memberId = otherParticipant.user_id;
                    const memberBadge = document.getElementById(`unread-badge-member-${memberId}`);
                    const memberCount = document.getElementById(`unread-count-member-${memberId}`);

                    if (memberBadge && memberCount) {
                        memberBadge.style.display = 'none';
                        memberCount.textContent = '0';
                    }
                }
            }
        }
    } catch (error) {
        console.error('Gagal menandai telah dibaca:', error);
    }
}

async function searchUsers(searchTerm) {
    if (!searchTerm.trim()) {
        await loadWorkspaceConversations();
        return;
    }

    try {
        const response = await fetch(`${API_URL}/api/workspace/${WORKSPACE_ID}/search-users?q=${encodeURIComponent(searchTerm)}`);
        if (!response.ok) throw new Error('Gagal mencari pengguna');
        const users = await response.json();

        renderSearchResults(users);
    } catch (error) {
        console.error('Search error:', error);
    }
}

function renderSearchResults(users) {
    let html = '';

    if (users.length > 0) {
        html += `<div class="px-6 pt-4 pb-2"><span class="text-xs font-semibold text-gray-500 uppercase">Hasil Pencarian</span></div>`;
        users.forEach(user => {
            html += createMemberHTML(user);
        });
    } else {
        html = '<div class="p-6 text-center text-gray-500">Tidak ada hasil ditemukan</div>';
    }

    chatListContainer.innerHTML = html;
}

window.removeFile = function (index) {
    selectedFiles.splice(index, 1);
    renderFilePreview();
}

window.downloadImage = async function (imageUrl, fileName) {
    try {
        const loadingToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        loadingToast.fire({
            icon: 'info',
            title: 'Mengunduh gambar...'
        });

        const response = await fetch(imageUrl);
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName || 'image.jpg';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        loadingToast.fire({
            icon: 'success',
            title: 'Gambar berhasil diunduh!'
        });
    } catch (error) {
        console.error('Error downloading image:', error);
        await Swal.fire({
            title: 'Gagal',
            text: 'Gagal mengunduh gambar',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

window.openImageModal = function (imageUrl, fileName) {
    const modalHTML = `
        <div id="imageModalOverlay" class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             style="background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px);"
             onclick="closeImageModal()">

            <div class="relative max-w-7xl max-h-[90vh] flex flex-col items-center"
                 onclick="event.stopPropagation()">

                <div class="relative bg-white rounded-lg shadow-2xl overflow-hidden max-h-[80vh]">
                    <img src="${imageUrl}"
                         alt="${fileName}"
                         class="max-w-full max-h-[80vh] w-auto h-auto object-contain"
                         style="display: block;">
                </div>

                <div class="mt-4 flex gap-3">
                    <button onclick="downloadImage('${imageUrl}', '${fileName}')"
                            class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span class="font-medium">Download</span>
                    </button>

                    <button onclick="closeImageModal()"
                            class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="font-medium">Tutup</span>
                    </button>
                </div>

                <p class="mt-3 text-sm text-white text-center max-w-md truncate">${fileName}</p>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    document.body.style.overflow = 'hidden';

    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    };
    document.addEventListener('keydown', handleEscape);
    window.imageModalEscapeHandler = handleEscape;
}

window.closeImageModal = function () {
    const modal = document.getElementById('imageModalOverlay');
    if (modal) {
        modal.style.transition = 'opacity 0.2s ease';
        modal.style.opacity = '0';

        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';

            if (window.imageModalEscapeHandler) {
                document.removeEventListener('keydown', window.imageModalEscapeHandler);
                delete window.imageModalEscapeHandler;
            }
        }, 200);
    }
}

// Message Actions Functions
window.startReplyMessage = function (messageId) {
    const messageElement = document.getElementById(messageId);
    if (!messageElement) {
        console.error('❌ Message element not found:', messageId);
        return;
    }

    const messageData = getMessageDataById(messageId);

    if (!messageData) {
        console.error('❌ Message data not found:', messageId);
        return;
    }

    currentReplyToMessage = messageId;

    const replyPreviewContainer = document.getElementById('replyPreviewContainer');
    const replySenderName = document.getElementById('replySenderName');
    const replyContent = document.getElementById('replyContent');

    if (replyPreviewContainer && replySenderName && replyContent) {
        const senderName = messageData.sender_id === AUTH_USER_ID ?
            'Anda' : (messageData.sender ? messageData.sender.full_name : 'User');

        let content = messageData.content || '';
        if (!content && messageData.attachments && messageData.attachments.length > 0) {
            const fileType = messageData.attachments[0].file_type;
            if (fileType.startsWith('image/')) {
                content = 'Gambar';
            } else if (fileType.startsWith('video/')) {
                content = 'Video';
            } else {
                content = 'File';
            }
        }

        replySenderName.textContent = `Membalas ${senderName}`;
        replyContent.textContent = content.length > 50 ?
            content.substring(0, 50) + '...' : content;
        replyPreviewContainer.style.display = 'block';
    }

    cancelEdit();

    if (messageInput) {
        messageInput.focus();
        setTimeout(() => {
            messageInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
}

window.cancelReply = function () {
    currentReplyToMessage = null;
    const replyPreviewContainer = document.getElementById('replyPreviewContainer');

    if (replyPreviewContainer) {
        replyPreviewContainer.style.display = 'none';
    }
}

window.startEditMessage = function (messageId) {
    const messageElement = document.getElementById(messageId);
    if (!messageElement) {
        console.error('❌ Message element not found:', messageId);
        return;
    }

    currentEditMessage = messageId;

    let messageContent = '';

    const contentElement = messageElement.querySelector('.message-content');
    if (contentElement) {
        messageContent = contentElement.textContent || '';
    } else {
        const textElements = messageElement.querySelectorAll('p.text-sm');
        for (let element of textElements) {
            if (!element.classList.contains('text-gray-500') &&
                !element.classList.contains('italic')) {
                messageContent = element.textContent || '';
                break;
            }
        }
    }

    cancelReply();

    messageInput.value = messageContent.trim();
    messageInput.focus();

    const editModeHTML = `
        <div id="editMode" class="mb-2 p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500 flex justify-between items-center">
            <div class="flex-1">
                <p class="text-xs font-medium text-yellow-700">Mengedit pesan</p>
                <p class="text-xs text-yellow-600 truncate">${messageContent.substring(0, 50)}${messageContent.length > 50 ? '...' : ''}</p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="saveEditMessage()" class="text-xs bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition">
                    Simpan
                </button>
                <button type="button" onclick="cancelEdit()" class="text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition">
                    Batal
                </button>
            </div>
        </div>
    `;

    const existingEditMode = document.getElementById('editMode');
    if (existingEditMode) existingEditMode.remove();

    sendMessageForm.insertAdjacentHTML('beforebegin', editModeHTML);

    updateSendButton();

    setTimeout(() => {
        messageInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
}

window.saveEditMessage = async function () {
    if (!currentEditMessage) return;

    const content = messageInput.value.trim();
    if (!content) {
        await Swal.fire({
            title: 'Error',
            text: 'Pesan tidak boleh kosong',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    try {
        const response = await fetch(`${API_URL}/api/chat/message/${currentEditMessage}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ content })
        });

        if (!response.ok) throw new Error('Gagal mengedit pesan');

        const result = await response.json();

        if (result.success) {
            cancelEdit();

            await Swal.fire({
                title: 'Berhasil!',
                text: 'Pesan berhasil diedit',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    } catch (error) {
        console.error('Edit error:', error);
        await Swal.fire({
            title: 'Gagal!',
            text: 'Gagal mengedit pesan',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

window.cancelEdit = function () {
    currentEditMessage = null;
    messageInput.value = '';

    const editMode = document.getElementById('editMode');
    if (editMode) editMode.remove();

    updateSendButton();
}

window.scrollToMessage = function (messageId) {
    const messageElement = document.getElementById(messageId);
    if (!messageElement) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'info',
            title: 'Pesan tidak ditemukan atau belum di-load'
        });
        return;
    }

    const containerRect = chatContainer.getBoundingClientRect();
    const messageRect = messageElement.getBoundingClientRect();

    const scrollTop = chatContainer.scrollTop;
    const offsetTop = messageRect.top - containerRect.top + scrollTop;

    const targetScroll = offsetTop - (chatContainer.clientHeight / 2) + (messageRect.height / 2);

    chatContainer.scrollTo({
        top: targetScroll,
        behavior: 'smooth'
    });

    messageElement.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
    messageElement.style.backgroundColor = 'rgba(59, 130, 246, 0.2)';
    messageElement.style.transform = 'scale(1.02)';
    messageElement.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.4)';

    setTimeout(() => {
        messageElement.style.backgroundColor = '';
        messageElement.style.transform = '';
        messageElement.style.boxShadow = '';
    }, 2000);
}

window.deleteMessage = async function (messageId) {
    if (!messageId || messageId.startsWith('temp-')) {
        return;
    }

    if (!window.pendingDeletes) {
        window.pendingDeletes = new Set();
    }

    if (window.pendingDeletes.has(messageId)) {
        return;
    }

    window.pendingDeletes.add(messageId);

    const body = document.body;
    const scrollY = window.scrollY;
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

    body.style.overflow = 'hidden';
    body.style.paddingRight = scrollbarWidth + 'px';
    body.style.position = 'fixed';
    body.style.top = `-${scrollY}px`;
    body.style.width = '100%';

    const { value: willDelete } = await Swal.fire({
        title: 'Hapus Pesan?',
        text: "Pesan yang sudah dihapus tidak dapat dikembalikan",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            container: 'swal-no-shift'
        }
    });

    body.style.overflow = '';
    body.style.paddingRight = '';
    body.style.position = '';
    body.style.top = '';
    body.style.width = '';
    window.scrollTo(0, scrollY);

    if (!willDelete) {
        window.pendingDeletes.delete(messageId);
        return;
    }

    try {
        const messageElement = document.getElementById(messageId);
        const isOwnMessage = messageElement && messageElement.classList.contains('justify-end');

        if (messageElement && messageElement.parentNode && document.body.contains(messageElement)) {
            replaceMessageWithDeletedText(messageId, isOwnMessage);
        }

        const response = await fetch(`${API_URL}/api/chat/message/${messageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'include'
        });

        if (response.status === 404) {
            throw new Error('Pesan tidak ditemukan (404)');
        }

        if (response.status === 403) {
            throw new Error('Anda tidak memiliki akses untuk menghapus pesan ini');
        }

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server error:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const result = await response.json();

        if (result.success) {
            await Swal.fire({
                title: 'Berhasil!',
                text: 'Pesan berhasil dihapus',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            throw new Error(result.error || 'Gagal menghapus pesan');
        }

    } catch (error) {
        console.error('❌ Delete error:', error);

        await Swal.fire({
            title: 'Gagal!',
            text: error.message || 'Terjadi kesalahan saat menghapus pesan',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } finally {
        window.pendingDeletes.delete(messageId);
    }
}

function updateSidebarPreviewAfterDelete(conversationId) {
    const previewElement = document.getElementById(`preview-${conversationId}`);
    if (!previewElement) {
        return;
    }

    const messageElements = Array.from(document.querySelectorAll('#messageList > [id]'));
    const nonDeletedMessages = messageElements.filter(el => {
        const id = el.id;
        if (!id || id.includes('typing-indicator')) return false;

        const isDeleted = el.classList.contains('deleted-message') ||
            el.querySelector('.bg-gray-300.text-gray-600.italic');

        return !isDeleted;
    });

    if (nonDeletedMessages.length === 0) {
        previewElement.textContent = 'Belum ada pesan';
        return;
    }

    const lastMessageElement = nonDeletedMessages[nonDeletedMessages.length - 1];

    let lastMessageText = '';
    const contentElement = lastMessageElement.querySelector('.message-content');

    if (contentElement) {
        lastMessageText = contentElement.textContent || '';
    }

    const isOwnMessage = lastMessageElement.classList.contains('justify-end');

    if (lastMessageText) {
        const conversation = window.allConversations.find(c => c.id === conversationId);
        const isGroup = conversation && conversation.type === 'group';

        if (isGroup) {
            const senderName = isOwnMessage ? 'Anda' :
                (lastMessageElement.querySelector('.font-semibold.text-gray-700')?.textContent || 'User');
            previewElement.textContent = `${senderName}: ${lastMessageText}`;
        } else {
            const prefix = isOwnMessage ? 'Anda: ' : '';
            previewElement.textContent = prefix + lastMessageText;
        }
    } else {
        previewElement.textContent = 'Mengirim file';
    }

    const conversation = window.allConversations.find(c => c.id === conversationId);
    if (conversation && conversation.type === 'private') {
        const otherParticipant = conversation.participants.find(p => p.user_id !== AUTH_USER_ID);
        if (otherParticipant) {
            const memberId = otherParticipant.user_id;
            const memberItem = document.querySelector(`[data-member-id="${memberId}"]`);

            if (memberItem) {
                const memberPreview = memberItem.querySelector('p.text-xs');
                if (memberPreview) {
                    memberPreview.textContent = previewElement.textContent;
                }
            }
        }
    }
}

function replaceMessageWithDeletedText(messageId, isOwnMessage = true) {
    const messageElement = document.getElementById(messageId);
    if (!messageElement) {
        console.warn(`❌ Element dengan ID ${messageId} tidak ditemukan`);
        updateSidebarPreviewAfterDelete(currentConversationId);
        return;
    }

    if (!messageElement.parentNode || !document.body.contains(messageElement)) {
        console.warn(`❌ Element dengan ID ${messageId} tidak ada di DOM`);
        updateSidebarPreviewAfterDelete(currentConversationId);
        return;
    }

    const deletedText = isOwnMessage ? 'Kamu telah menghapus pesan ini' : 'Pesan ini telah dihapus';
    const timeElement = messageElement.querySelector('.text-xs.text-gray-500');
    const time = timeElement ? timeElement.textContent.split('(')[0].trim() : '';

    let senderName = 'User';
    if (!isOwnMessage) {
        const nameElement = messageElement.querySelector('.font-semibold.text-gray-700');
        if (nameElement) {
            senderName = nameElement.textContent;
        }
    }

    const existingAvatar = messageElement.querySelector('img[alt], .w-8.h-8.rounded-full');
    let avatarHTML = '';

    if (existingAvatar && existingAvatar.tagName === 'IMG') {
        avatarHTML = existingAvatar.outerHTML;
    } else if (existingAvatar) {
        avatarHTML = existingAvatar.outerHTML;
    } else {
        const initials = getInitials(isOwnMessage ? 'Anda' : senderName);
        avatarHTML = `<div class="w-8 h-8 rounded-full ${isOwnMessage ? 'bg-blue-200 text-blue-800' : 'bg-gray-200 text-gray-800'} flex items-center justify-center font-bold text-xs">${initials}</div>`;
    }

    const replacementHTML = isOwnMessage ? `
        <div id="${messageId}" class="flex items-start justify-end deleted-message mb-4">
            <div class="flex flex-col items-end max-w-[70%]">
                <div class="flex items-center justify-end gap-2 mb-1">
                    <span class="text-xs text-gray-500">${time}</span>
                    <span class="font-semibold text-gray-700 text-sm">Anda</span>
                </div>
                <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-br-md px-4 py-3 shadow-sm italic">
                    <p class="text-sm">${deletedText}</p>
                </div>
            </div>
            <div class="flex-shrink-0 ml-3">
                ${avatarHTML}
            </div>
        </div>
    ` : `
        <div id="${messageId}" class="flex items-start justify-start deleted-message mb-4">
            <div class="flex-shrink-0 mr-3">
                ${avatarHTML}
            </div>
            <div class="flex flex-col items-start max-w-[70%]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-gray-700 text-sm">${senderName}</span>
                    <span class="text-xs text-gray-500">${time}</span>
                </div>
                <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm italic">
                    <p class="text-sm">${deletedText}</p>
                </div>
            </div>
        </div>
    `;

    try {
        messageElement.style.transition = 'opacity 0.3s ease';
        messageElement.style.opacity = '0';

        setTimeout(() => {
            if (messageElement.parentNode && document.body.contains(messageElement)) {
                messageElement.outerHTML = replacementHTML;

                const newElement = document.getElementById(messageId);
                if (newElement) {
                    newElement.style.opacity = '0';
                    setTimeout(() => {
                        newElement.style.transition = 'opacity 0.3s ease';
                        newElement.style.opacity = '1';
                    }, 50);
                }
            }

            updateSidebarPreviewAfterDelete(currentConversationId);
        }, 300);

    } catch (error) {
        console.error('❌ Error in replaceMessageWithDeletedText:', error);
        updateSidebarPreviewAfterDelete(currentConversationId);
    }
}

function updateAllOwnMessagesToRead() {
    const ownMessageElements = document.querySelectorAll('.flex.items-start.justify-end');
    ownMessageElements.forEach(element => {
        const statusElement = element.querySelector('.read-status');
        if (statusElement) {
            statusElement.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg class="w-3.5 h-3.5 text-blue-500 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                `;
        }
    });
}

function updateSidebarOnNewMessage(message, incrementUnread) {
    const conversationId = message.conversation_id;

    const previewText = document.getElementById(`preview-${conversationId}`);
    if (previewText) {
        let previewContent = '';
        const conversation = window.allConversations.find(c => c.id === conversationId);
        const isGroup = conversation && conversation.type === 'group';

        if (isGroup) {
            let senderName = 'Anda';
            if (message.sender_id !== AUTH_USER_ID) {
                if (message.sender && message.sender.full_name) {
                    senderName = message.sender.full_name.split(' ')[0];
                } else {
                    senderName = 'User';
                }
            }

            if (message.attachments && message.attachments.length > 0) {
                if (message.content && message.content.trim() !== '') {
                    previewContent = `${senderName}: ${message.content}`;
                } else {
                    const fileCount = message.attachments.length;
                    previewContent = `${senderName}: Mengirim ${fileCount} file`;
                }
            } else {
                previewContent = `${senderName}: ${message.content}`;
            }
        } else {
            const senderPrefix = message.sender_id === AUTH_USER_ID ? 'Anda: ' : '';
            if (message.attachments && message.attachments.length > 0) {
                if (message.content && message.content.trim() !== '') {
                    previewContent = senderPrefix + message.content;
                } else {
                    const fileCount = message.attachments.length;
                    previewContent = senderPrefix + `Mengirim ${fileCount} file`;
                }
            } else {
                previewContent = senderPrefix + message.content;
            }
        }

        previewText.textContent = previewContent;
    }

    // Update member preview jika ini private chat
    const conversation = window.allConversations.find(c => c.id === conversationId);
    if (conversation && conversation.type === 'private') {
        const otherParticipant = conversation.participants.find(p => p.user_id !== AUTH_USER_ID);
        if (otherParticipant) {
            const memberId = otherParticipant.user_id;
            const memberItem = document.querySelector(`[data-member-id="${memberId}"]`);

            if (memberItem) {
                const memberPreview = memberItem.querySelector('p');
                if (memberPreview) {
                    let previewContent = '';
                    const senderPrefix = message.sender_id === AUTH_USER_ID ?
                        'Anda: ' :
                        `${otherParticipant.user.full_name.split(' ')[0]}: `;

                    if (message.attachments && message.attachments.length > 0) {
                        if (message.content && message.content.trim() !== '') {
                            previewContent = senderPrefix + message.content;
                        } else {
                            const fileType = message.attachments[0].file_type;
                            if (fileType.startsWith('image/')) {
                                previewContent = senderPrefix + '📷 Gambar';
                            } else if (fileType.startsWith('video/')) {
                                previewContent = senderPrefix + '🎬 Video';
                            } else {
                                previewContent = senderPrefix + `📎 ${message.attachments.length} file`;
                            }
                        }
                    } else {
                        previewContent = senderPrefix + message.content;
                    }

                    memberPreview.textContent = previewContent;
                }
            }
        }
    }

    // Update unread badge
    if (incrementUnread) {
        const badge = document.getElementById(`unread-badge-${conversationId}`);
        const countSpan = document.getElementById(`unread-count-${conversationId}`);
        if (badge && countSpan) {
            let currentCount = parseInt(countSpan.textContent) || 0;
            currentCount++;
            countSpan.textContent = currentCount;
            badge.style.display = 'block';
        }

        // Update badge di member item jika private chat
        if (conversation && conversation.type === 'private') {
            const otherParticipant = conversation.participants.find(p => p.user_id !== AUTH_USER_ID);
            if (otherParticipant) {
                const memberId = otherParticipant.user_id;
                const memberBadge = document.getElementById(`unread-badge-member-${memberId}`);
                const memberCount = document.getElementById(`unread-count-member-${memberId}`);

                if (memberBadge && memberCount) {
                    let currentCount = parseInt(memberCount.textContent) || 0;
                    currentCount++;
                    memberCount.textContent = currentCount;
                    memberBadge.style.display = 'block';
                } else {
                    loadWorkspaceConversations();
                }
            }
        }
    }

    // Move to top
    const sidebarItem = document.querySelector(`#chatListContainer div[data-conversation-id="${conversationId}"]`);
    if (sidebarItem) {
        let currentElement = sidebarItem;
        let header = null;

        while (currentElement.previousElementSibling) {
            currentElement = currentElement.previousElementSibling;
            if (currentElement.classList.contains('px-6', 'pt-4', 'pb-2')) {
                header = currentElement;
                break;
            }
        }

        if (header) {
            header.after(sidebarItem);
        } else {
            sidebarItem.parentElement.prepend(sidebarItem);
        }
    }
}

// Event Handler Functions
let dragCounter = 0;

function handleDragEnter(e) {
    e.preventDefault();
    e.stopPropagation();
    dragCounter++;
    if (dragCounter === 1) {
        dropZone.style.display = 'flex';
    }
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    dragCounter--;
    if (dragCounter === 0) {
        dropZone.style.display = 'none';
    }
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    dragCounter = 0;
    dropZone.style.display = 'none';

    const files = Array.from(e.dataTransfer.files);
    const validFiles = files.filter(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`File "${file.name}" terlalu besar. Maksimal 10MB per file.`);
            return false;
        }
        return true;
    });

    if (validFiles.length > 0) {
        selectedFiles = [...selectedFiles, ...validFiles];
        renderFilePreview();
        messageInput.focus();
    }
}

function showTypingIndicator(userId, userName) {
    hideTypingIndicator();

    const indicatorHTML = `
            <div id="typing-indicator" class="flex items-center gap-2 text-gray-500 text-sm italic mb-4">
                <div class="flex gap-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
                <span>${userName} sedang mengetik...</span>
            </div>
        `;

    messageList.insertAdjacentHTML('beforeend', indicatorHTML);
    smartScrollToBottom();
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) indicator.remove();
}

function handleUserTyping(data) {
    if (data.user_id === AUTH_USER_ID) return;

    currentlyTypingUsers.add(data.user_id);
    showTypingIndicator(data.user_id, data.user_name);

    setTimeout(() => {
        currentlyTypingUsers.delete(data.user_id);
        if (currentlyTypingUsers.size === 0) {
            hideTypingIndicator();
        }
    }, 3000);
}

function handleUserStopTyping(data) {
    currentlyTypingUsers.delete(data.user_id);
    if (currentlyTypingUsers.size === 0) {
        hideTypingIndicator();
    }
}

function handleMessageEdited(message) {
    if (message.reply_to) {
        message.replyTo = message.reply_to;
    }

    const messageElement = document.getElementById(message.id);
    if (messageElement) {
        const newMessageHTML = createMessageHTML(message);

        messageElement.style.transition = 'opacity 0.3s ease';
        messageElement.style.opacity = '0';

        setTimeout(() => {
            messageElement.outerHTML = newMessageHTML;

            const updatedElement = document.getElementById(message.id);
            if (updatedElement) {
                updatedElement.style.opacity = '0';
                setTimeout(() => {
                    updatedElement.style.transition = 'opacity 0.3s ease';
                    updatedElement.style.opacity = '1';
                }, 50);
            }
        }, 300);
    }

    updateSidebarOnNewMessage(message, false);
}

function handleMessageDeleted(data) {
    const messageId = data.message_id || data.id;
    const senderId = data.sender_id;
    const conversationId = data.conversation_id;

    if (window.pendingDeletes && window.pendingDeletes.has(messageId)) {
        return;
    }

    const messageElement = document.getElementById(messageId);
    if (!messageElement) {
        updateSidebarPreviewAfterDelete(conversationId);
        return;
    }

    if (!messageElement.parentNode) {
        updateSidebarPreviewAfterDelete(conversationId);
        return;
    }

    const isOwnMessage = senderId === AUTH_USER_ID;
    replaceMessageWithDeletedText(messageId, isOwnMessage);

    updateSidebarPreviewAfterDelete(conversationId);
}

function handleNewMessage(message) {
    if (message.reply_to) {
        message.replyTo = message.reply_to;
    } else if (message.reply_to_message_id) {
    }

    const isOwnMessage = message.sender_id === AUTH_USER_ID;

    if (isOwnMessage) {
        const existingMessage = document.getElementById(message.id);
        if (existingMessage) {
            const statusElement = existingMessage.querySelector('.read-status');
            if (statusElement) {
                statusElement.innerHTML = getReadStatusHTML(message);
            }
            return;
        }

        if (message.conversation_id === currentConversationId) {
            appendMessage(message);
        }

        updateSidebarOnNewMessage(message, false);
        return;
    }

    if (message.conversation_id === currentConversationId) {
        appendMessage(message);
        markConversationAsRead(message.conversation_id);
        updateAllOwnMessagesToRead();
        updateSidebarOnNewMessage(message, false);
    } else {
        updateSidebarOnNewMessage(message, true);
    }
}

function setupInputListeners() {
    messageInput.addEventListener('input', updateSendButton);
    sendMessageForm.addEventListener('submit', handleSendMessage);

    scrollToBottomBtn.addEventListener('click', function () {
        scrollToBottom();
        setTimeout(() => {
            scrollToBottomBtn.style.display = 'none';
        }, 300);
    });

    chatContainer.addEventListener('scroll', function () {
        const scrollTop = chatContainer.scrollTop;
        const scrollHeight = chatContainer.scrollHeight;
        const clientHeight = chatContainer.clientHeight;

        const distanceFromBottom = scrollHeight - scrollTop - clientHeight;

        const shouldShow = distanceFromBottom > 100;

        scrollToBottomBtn.style.display = shouldShow ? 'flex' : 'none';
    });

    messageInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessageForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    });

    uploadButton.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        const validFiles = files.filter(file => {
            if (file.size > 10 * 1024 * 1024) {
                alert(`File "${file.name}" terlalu besar. Maksimal 10MB per file.`);
                return false;
            }
            return true;
        });

        if (validFiles.length > 0) {
            selectedFiles = [...selectedFiles, ...validFiles];
            renderFilePreview();
        }
        fileInput.value = '';
    });

    const searchInput = document.querySelector('#searchInput');
    if (searchInput) {
        const debouncedSearch = debounce(searchUsers, 300);
        searchInput.addEventListener('input', (e) => {
            debouncedSearch(e.target.value);
        });

        searchInput.addEventListener('blur', (e) => {
            if (!e.target.value.trim()) {
                setTimeout(() => loadWorkspaceConversations(), 100);
            }
        });
    }

    messageInput.addEventListener('input', function () {
        if (!currentConversationId) return;

        if (!isTyping) {
            isTyping = true;
            window.Echo.private(`conversation.${currentConversationId}`)
                .whisper('typing', {
                    user_id: AUTH_USER_ID,
                    user_name: 'Anda'
                });
        }

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            isTyping = false;
            window.Echo.private(`conversation.${currentConversationId}`)
                .whisper('stop-typing', {
                    user_id: AUTH_USER_ID
                });
        }, 1000);
    });

    chatInputBar.addEventListener('dragenter', handleDragEnter);
    chatInputBar.addEventListener('dragover', handleDragOver);
    chatInputBar.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);
    dropZone.addEventListener('dragenter', handleDragEnter);
    dropZone.addEventListener('dragover', handleDragOver);
}

function setupEchoListeners() {
    if (typeof Echo === 'undefined') {
        console.error('❌ Laravel Echo not configured (Echo is undefined).');
        return;
    }

    window.allConversations.forEach(conversation => {
        const channelName = `conversation.${conversation.id}`;
        Echo.leave(channelName);
    });

    window.allConversations.forEach(conversation => {
        const channelName = `conversation.${conversation.id}`;

        const channel = Echo.private(channelName);

        channel.subscribed(() => {
        });

        channel.error((error) => {
            console.error(`❌ Error subscribing to ${channelName}:`, error);
        });

        channel.listen('.NewMessageSent', (e) => {
            if (e.message) {
                handleNewMessage(e.message);
            } else {
                console.error('❌ Event tidak punya property "message":', e);
            }
        });

        channel.listen('.MessageDeleted', (e) => {
            if (e.message_id || e.id) {
                handleMessageDeleted(e);
            }
        });

        channel.listenForWhisper('typing', (data) => {
            handleUserTyping(data);
        });

        channel.listenForWhisper('stop-typing', (data) => {
            handleUserStopTyping(data);
        });

        channel.listen('.MessageEdited', (e) => {
            if (e.message) {
                handleMessageEdited(e.message);
            }
        });
    });
}

function refreshSidebarHighlight() {
    document.querySelectorAll('[data-conversation-id]').forEach(item => {
        const conversationId = item.dataset.conversationId;
        const isActive = conversationId === currentConversationId;

        if (isActive) {
            item.classList.remove('hover:bg-gray-50');
            item.classList.add('bg-blue-100', 'border-l-4', 'border-blue-500');

            const title = item.querySelector('h4');
            const preview = item.querySelector('p');
            if (title) {
                title.classList.remove('text-gray-800');
                title.classList.add('text-blue-700');
            }
            if (preview) {
                preview.classList.remove('text-gray-500');
                preview.classList.add('text-blue-600');
            }
        } else {
            item.classList.remove('bg-blue-100', 'border-l-4', 'border-blue-500');
            item.classList.add('hover:bg-gray-50');

            const title = item.querySelector('h4');
            const preview = item.querySelector('p');
            if (title) {
                title.classList.remove('text-blue-700');
                title.classList.add('text-gray-800');
            }
            if (preview) {
                preview.classList.remove('text-blue-600');
                preview.classList.add('text-gray-500');
            }
        }
    });
}

// -----------------------------------------------------------------
// GLOBAL FUNCTIONS
// -----------------------------------------------------------------
window.selectConversation = function (conversationId) {
    loadMessages(conversationId);
    refreshSidebarHighlight();
}
