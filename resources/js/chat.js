// -----------------------------------------------------------------
// LANGKAH 1: Setup Global Variables
// -----------------------------------------------------------------

let WORKSPACE_ID, AUTH_USER_ID, API_URL, CSRF_TOKEN;
let currentConversationId = null;
window.allConversations = [];

let selectedFiles = [];
let isSending = false;

// -----------------------------------------------------------------
// LANGKAH 1.5: Typing Variables (TAMBAHKAN SETELAH VARIABLE GLOBAL)
// -----------------------------------------------------------------

let typingTimeout;
let isTyping = false;
let currentlyTypingUsers = new Set();

// -----------------------------------------------------------------
// LANGKAH 2: Ambil Elemen DOM
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

// -----------------------------------------------------------------
// LANGKAH 3: Fungsi Helper (Render HTML)
// -----------------------------------------------------------------

function getInitials(name) {
    if (!name) return '??';
    const names = name.split(' ');
    if (names.length === 1) return name.substring(0, 2).toUpperCase();
    return (names[0][0] + names[names.length - 1][0]).toUpperCase();
}

/** 🔥 PERBAIKAN: Fungsi untuk menghapus pesan dengan SweetAlert */
window.deleteMessage = async function (messageId) {
    console.log('🗑️ Attempting to delete message:', messageId);

    if (!messageId || messageId.startsWith('temp-')) {
        console.error('Invalid message ID:', messageId);
        return;
    }

    // 🔥 FIX: Prevent layout shift
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

    // 🔥 RESTORE: Reset body styles
    body.style.overflow = '';
    body.style.paddingRight = '';
    body.style.position = '';
    body.style.top = '';
    body.style.width = '';
    window.scrollTo(0, scrollY);

    if (!willDelete) {
        console.log('User cancelled deletion');
        return;
    }

    try {
        console.log('📤 Sending DELETE request...');

        const response = await fetch(`${API_URL}/api/chat/message/${messageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'include'
        });

        console.log('📥 Response status:', response.status);

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
        console.log('✅ Delete result:', result);

        if (result.success) {
            console.log('🎉 Delete successful, updating UI...');

            // Optimistic update
            const messageElement = document.getElementById(messageId);
            if (messageElement) {
                const isOwnMessage = messageElement.classList.contains('justify-end');
                replaceMessageWithDeletedText(messageId, isOwnMessage);
                updateSidebarPreviewAfterDelete(currentConversationId);
            }

            // Success notification
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
    }
}

function updateSidebarPreviewAfterDelete(conversationId) {
    const previewElement = document.getElementById(`preview-${conversationId}`);
    if (!previewElement) return;

    // Cari pesan terakhir yang belum dihapus dari DOM
    const messageElements = Array.from(document.querySelectorAll('#messageList > [id^="message-"]'));
    const nonDeletedMessages = messageElements.filter(el => {
        return !el.querySelector('.bg-gray-300.text-gray-600.italic'); // Bukan pesan yang sudah dihapus
    });

    console.log('🔄 Updating sidebar preview:', {
        conversationId,
        totalMessages: messageElements.length,
        nonDeletedMessages: nonDeletedMessages.length
    });

    if (nonDeletedMessages.length > 0) {
        const lastMessageElement = nonDeletedMessages[nonDeletedMessages.length - 1];
        const lastMessageText = lastMessageElement.querySelector('p.text-sm')?.textContent || '';

        // 🔥 PERBAIKAN: Handle pesan yang dihapus
        const isDeletedMessage = lastMessageElement.classList.contains('deleted-message') ||
            lastMessageElement.querySelector('.bg-gray-300.text-gray-600.italic');

        if (isDeletedMessage) {
            // Cek apakah pesan dihapus oleh pengirim atau orang lain
            const isOwnMessage = lastMessageElement.classList.contains('justify-end');
            if (isOwnMessage) {
                previewElement.textContent = 'Kamu telah menghapus pesan ini';
            } else {
                const senderName = lastMessageElement.querySelector('.font-semibold.text-gray-700')?.textContent || 'User';
                previewElement.textContent = `${senderName}: Pesan telah dihapus`;
            }
        }
        else if (lastMessageText.includes('Mengirim')) {
            previewElement.textContent = lastMessageText;
        } else {
            // Ambil info pengirim dan konten
            const isOwnMessage = lastMessageElement.classList.contains('justify-end');
            const senderName = isOwnMessage ? 'Anda' :
                lastMessageElement.querySelector('.font-semibold.text-gray-700')?.textContent || 'User';

            const content = lastMessageText || 'Mengirim file';
            previewElement.textContent = isOwnMessage ? `Anda: ${content}` : `${senderName}: ${content}`;
        }
    } else {
        // Tidak ada pesan yang tersisa
        previewElement.textContent = 'Belum ada pesan';
    }
}

function replaceMessageWithDeletedText(messageId, isOwnMessage = true) {
    const messageElement = document.getElementById(messageId);
    if (!messageElement) {
        console.warn(`Element dengan ID ${messageId} tidak ditemukan`);
        return;
    }

    const deletedText = isOwnMessage ? 'Kamu telah menghapus pesan ini' : 'Pesan ini telah dihapus';
    const timeElement = messageElement.querySelector('.text-xs.text-gray-500');
    const time = timeElement ? timeElement.textContent : '';

    let senderName = 'User';
    if (!isOwnMessage) {
        const nameElement = messageElement.querySelector('.font-semibold.text-gray-700');
        if (nameElement) {
            senderName = nameElement.textContent;
        }
    }

    const initials = getInitials(isOwnMessage ? 'Anda' : senderName);

    const replacementHTML = isOwnMessage ? `
        <div id="${messageId}" class="flex items-start justify-end deleted-message">
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
                <div class="w-8 h-8 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center font-bold text-xs">
                    ${initials}
                </div>
            </div>
        </div>
    ` : `
        <div id="${messageId}" class="flex items-start justify-start deleted-message">
            <div class="flex-shrink-0 mr-3">
                <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center font-bold text-xs">
                    ${initials}
                </div>
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

    // Smooth transition
    messageElement.style.transition = 'all 0.3s ease';
    messageElement.style.opacity = '0';
    messageElement.style.transform = 'scale(0.9)';

    setTimeout(() => {
        messageElement.outerHTML = replacementHTML;
        const newElement = document.getElementById(messageId);
        if (newElement) {
            newElement.style.opacity = '0';
            newElement.style.transform = 'scale(0.9)';
            setTimeout(() => {
                newElement.style.transition = 'all 0.3s ease';
                newElement.style.opacity = '1';
                newElement.style.transform = 'scale(1)';

                // 🔥 PERBAIKAN: Update sidebar setelah animasi selesai
                setTimeout(() => {
                    updateSidebarPreviewAfterDelete(currentConversationId);
                }, 350);
            }, 50);
        }
    }, 300);
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

    console.log('🔍 User data for avatar:', user); // Debug

    // Jika user punya avatar, generate URL yang benar
    if (user.avatar) {
        // Cek apakah avatar sudah full URL atau relative path
        if (user.avatar.startsWith('http')) {
            return user.avatar;
        } else {
            // Handle path storage Laravel
            if (user.avatar.startsWith('avatars/')) {
                return `${API_URL}/storage/${user.avatar}`;
            } else {
                return `${API_URL}/storage/avatars/${user.avatar}`;
            }
        }
    }
    return null;
}

function createConversationHTML(conversation) {
    let chatName = conversation.name;
    let chatAvatar = null;
    let chatAvatarInitials = getInitials(conversation.name);
    let avatarBgClass = 'bg-blue-200 text-blue-800';

    if (conversation.type === 'private') {
        const otherParticipant = conversation.participants.find(p => p.user_id !== AUTH_USER_ID);
        if (otherParticipant) {
            chatName = otherParticipant.user.full_name;
            chatAvatarInitials = getInitials(otherParticipant.user.full_name);

            // 🔥 PERBAIKAN: Ambil avatar URL
            chatAvatar = getAvatarUrl(otherParticipant.user);
            console.log('💬 Conversation avatar:', {
                name: chatName,
                avatar: chatAvatar,
                user: otherParticipant.user
            });
            avatarBgClass = 'bg-indigo-100 text-indigo-800';
        }
    }

    // 🔥 PERBAIKAN: Tampilkan avatar image jika ada
    const avatarHTML = chatAvatar
        ? `<img src="${chatAvatar}" alt="${chatName}" class="w-10 h-10 rounded-full object-cover border border-gray-200">`
        : `<div class="w-10 h-10 rounded-full ${avatarBgClass} flex items-center justify-center font-bold text-sm">${chatAvatarInitials}</div>`;

    const lastMessage = conversation.last_message;
    let lastMessageText = 'Belum ada pesan';

    if (lastMessage) {
        // 🔥 PERBAIKAN: Handle pesan yang sudah dihapus dengan benar
        const isDeleted = lastMessage.message_type === 'deleted' ||
            (lastMessage.deleted_at !== null && lastMessage.deleted_at !== undefined);

        if (isDeleted) {
            if (lastMessage.sender_id === AUTH_USER_ID) {
                lastMessageText = 'Kamu telah menghapus pesan ini';
            } else {
                const senderName = lastMessage.sender ? lastMessage.sender.full_name.split(' ')[0] : 'User';
                lastMessageText = `${senderName}: Pesan telah dihapus`;
            }
        }
        else if (conversation.type === 'group') {
            let senderName = 'Anda';
            if (lastMessage.sender_id !== AUTH_USER_ID) {
                if (lastMessage.sender && lastMessage.sender.full_name) {
                    senderName = lastMessage.sender.full_name.split(' ')[0];
                } else {
                    senderName = 'User';
                }
            }

            if (lastMessage.attachments && lastMessage.attachments.length > 0) {
                if (lastMessage.content && lastMessage.content.trim() !== '') {
                    lastMessageText = `${senderName}: ${lastMessage.content}`;
                } else {
                    const fileCount = lastMessage.attachments.length;
                    const fileType = lastMessage.attachments[0].file_type;
                    const isImage = fileType.startsWith('image/');
                    const isVideo = fileType.startsWith('video/');

                    if (isImage) {
                        lastMessageText = `${senderName}: 📷 Gambar`;
                    } else if (isVideo) {
                        lastMessageText = `${senderName}: 🎬 Video`;
                    } else if (fileType === 'application/pdf') {
                        lastMessageText = `${senderName}: 📄 PDF`;
                    } else {
                        lastMessageText = `${senderName}: 📎 ${fileCount} file`;
                    }
                }
            } else {
                lastMessageText = `${senderName}: ${lastMessage.content}`;
            }
        } else {
            const senderPrefix = lastMessage.sender_id === AUTH_USER_ID ? 'Anda: ' : '';
            if (lastMessage.attachments && lastMessage.attachments.length > 0) {
                if (lastMessage.content && lastMessage.content.trim() !== '') {
                    lastMessageText = senderPrefix + lastMessage.content;
                } else {
                    const fileCount = lastMessage.attachments.length;
                    const fileType = lastMessage.attachments[0].file_type;
                    const isImage = fileType.startsWith('image/');
                    const isVideo = fileType.startsWith('video/');

                    if (isImage) {
                        lastMessageText = senderPrefix + '📷 Gambar';
                    } else if (isVideo) {
                        lastMessageText = senderPrefix + '🎬 Video';
                    } else if (fileType === 'application/pdf') {
                        lastMessageText = senderPrefix + '📄 PDF';
                    } else {
                        lastMessageText = senderPrefix + `📎 ${fileCount} file`;
                    }
                }
            } else {
                lastMessageText = senderPrefix + lastMessage.content;
            }
        }
    }

    const unreadCount = conversation.unread_count || 0;

    return `
<div class="px-6 py-3 hover:bg-gray-50 cursor-pointer ${currentConversationId === conversation.id ? 'bg-[#E9EFFD] text-blue-600 font-semibold' : 'text-gray-800'}"
   data-conversation-id="${conversation.id}"
   onclick="selectConversation('${conversation.id}')">
   <div class="flex items-center">
     <div class="relative flex-shrink-0">
       ${avatarHTML}
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
</div>`;
}


function createMemberHTML(member) {
    const initials = getInitials(member.full_name);

    // 🔥 PERBAIKAN: Ambil avatar untuk anggota tim
    const memberAvatar = getAvatarUrl(member);
    console.log('👥 Member avatar:', {
        name: member.full_name,
        avatar: memberAvatar,
        member: member
    });

    const avatarHTML = memberAvatar
        ? `<img src="${memberAvatar}" alt="${member.full_name}" class="w-10 h-10 rounded-full object-cover border border-gray-200">`
        : `<div class="w-10 h-10 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center font-bold text-sm">${initials}</div>`;

    return `
<div class="px-6 py-3 hover:bg-gray-50 cursor-pointer text-gray-800"
     onclick="startChatWithUser('${member.id}', '${member.full_name}')">
   <div class="flex items-center">
     <div class="relative flex-shrink-0">
       ${avatarHTML}
     </div>
     <div class="ml-3 flex-1 min-w-0">
       <h4 class="text-sm font-semibold truncate">${member.full_name}</h4>
       <p class="text-xs text-gray-500 truncate">Mulai percakapan</p>
     </div>
   </div>
</div>`;
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

function fixFileUrl(fileUrl) {
    if (!fileUrl) return '';
    if (fileUrl.includes('http://localhost/storage/')) {
        return fileUrl.replace('http://localhost/storage/', 'http://127.0.0.1:8000/storage/');
    }
    if (fileUrl.startsWith('chat_files/')) {
        return `${API_URL}/storage/${fileUrl}`;
    }
    if (fileUrl.startsWith('/storage/')) {
        return `${API_URL}${fileUrl}`;
    }
    return fileUrl;
}

function createMessageHTML(message) {
    const isSender = message.sender_id === AUTH_USER_ID;
    const senderName = isSender ? 'Anda' : (message.sender ? message.sender.full_name : 'User');
    const initials = getInitials(senderName);

    // 🔥 PERBAIKAN: Ambil avatar sender
    const senderAvatar = message.sender ? getAvatarUrl(message.sender) : null;

    // Avatar HTML - pakai image jika ada
    const avatarHTML = senderAvatar
        ? `<img src="${senderAvatar}" alt="${senderName}" class="w-8 h-8 rounded-full object-cover border border-gray-200">`
        : `<div class="w-8 h-8 rounded-full ${isSender ? 'bg-blue-200 text-blue-800' : 'bg-gray-200 text-gray-800'} flex items-center justify-center font-bold text-xs">${initials}</div>`;

    const time = formatTime(message.created_at);

    // 🔥 PERBAIKAN: Cek apakah pesan benar-benar dihapus
    const isDeleted = message.message_type === 'deleted' ||
        (message.deleted_at !== null && message.deleted_at !== undefined);

    // 🔥 PERBAIKAN: Untuk file message, content bisa kosong tapi bukan deleted
    const isFileMessage = message.message_type === 'file' ||
        (message.attachments && message.attachments.length > 0);

    let attachmentsHTML = '';
    if (message.attachments && message.attachments.length > 0) {
        attachmentsHTML = '<div class="mt-2 space-y-2">';
        message.attachments.forEach(att => {
            const isImage = att.file_type && att.file_type.startsWith('image/');
            const isVideo = att.file_type && att.file_type.startsWith('video/');
            const isPDF = att.file_type === 'application/pdf';

            if (att.uploading && att.preview_url && att.file_type.startsWith('image/')) {
                attachmentsHTML += `
                    <div class="relative">
                        <img src="${att.preview_url}" alt="${att.file_name}"
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
                        <span class="text-sm">Mengunggah ${att.file_name}...</span>
                    </div>
                `;
            } else if (isImage) {
                const imageUrl = fixFileUrl(att.file_url);
                attachmentsHTML += `
                    <div class="relative group max-w-xs">
                        <img src="${imageUrl}" alt="${att.file_name}"
                             class="rounded-xl shadow-md cursor-pointer"
                             onclick="openImageModal('${imageUrl}', '${att.file_name}')">
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="downloadImage('${imageUrl}', '${att.file_name}')"
                                    class="bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition"
                                    title="Download gambar">
                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // Handle semua jenis file (PDF, DOC, ZIP, dll)
                const fileUrl = fixFileUrl(att.file_url);
                const fileIcon = getFileIcon(att.file_type);
                const fileSize = formatFileSize(att.file_size);

                attachmentsHTML += `
                    <div class="bg-white border border-gray-200 rounded-lg p-3 max-w-xs">
                        <a href="${fileUrl}" target="_blank" class="flex items-center gap-3 hover:bg-gray-50 rounded-lg p-2 transition">
                            <div class="flex-shrink-0">
                                ${fileIcon}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">${att.file_name}</p>
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

    // 🔥 PERBAIKAN KRUSIAL: Hanya tampilkan sebagai deleted jika benar-benar dihapus
    if (isDeleted) {
        const deletedText = isSender ? 'Kamu telah menghapus pesan ini' : 'Pesan ini telah dihapus';

        if (isSender) {
            return `
                <div id="${message.id}" class="flex items-start justify-end">
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
                <div id="${message.id}" class="flex items-start justify-start">
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

    // 🔥 FUNGSI BARU: Deteksi dan ubah URL menjadi link
    function detectAndCreateLinks(text) {
        if (!text) return '';

        // Regex untuk mendeteksi URL
        const urlRegex = /(https?:\/\/[^\s]+)/g;

        return text.replace(urlRegex, function (url) {
            // Bersihkan URL dari karakter yang tidak diinginkan di akhir
            let cleanUrl = url;
            if (url.endsWith('.') || url.endsWith(',') || url.endsWith('!') || url.endsWith('?')) {
                cleanUrl = url.slice(0, -1);
            }

            // Tampilkan domain sebagai teks link
            let displayText = cleanUrl;
            try {
                const domain = new URL(cleanUrl).hostname;
                displayText = domain.replace('www.', '');
            } catch (e) {
                // Jika URL tidak valid, tetap gunakan URL asli
            }

            return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-600 hover:underline break-all">${displayText}</a>`;
        });
    }

    // 🔥 PERBAIKAN: Untuk file message tanpa content, tampilkan default text
    let contentHTML = '';
    if (message.content && message.content.trim() !== '') {
        const processedContent = detectAndCreateLinks(message.content);
        contentHTML = `<p class="text-sm" style="word-break: break-word;">${processedContent}</p>`;
    } else if (isFileMessage) {
        // Tampilkan text default untuk file message tanpa content
        const fileCount = message.attachments ? message.attachments.length : 0;
        if (fileCount === 1) {
            const fileType = message.attachments[0].file_type;
            if (fileType.startsWith('image/')) {
                contentHTML = `<p class="text-sm italic">📷 Gambar</p>`;
            } else if (fileType.startsWith('video/')) {
                contentHTML = `<p class="text-sm italic">🎬 Video</p>`;
            } else if (fileType === 'application/pdf') {
                contentHTML = `<p class="text-sm italic">📄 PDF</p>`;
            } else {
                contentHTML = `<p class="text-sm italic">📎 File</p>`;
            }
        } else {
            contentHTML = `<p class="text-sm italic">📎 ${fileCount} files</p>`;
        }
    }

    // Tampilan normal untuk pesan yang belum dihapus
    if (isSender) {
        return `
            <div id="${message.id}" class="flex items-start justify-end group">
                <div class="flex flex-col items-end max-w-[70%]">
                    <div class="flex items-center justify-end gap-2 mb-1">
                        <button class="delete-message-btn opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-red-500 p-1 rounded"
                                title="Hapus pesan"
                                onclick="deleteMessage('${message.id}')">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        <span class="text-xs text-gray-500">${time}</span>
                        <div class="flex items-center read-status">
                            ${getReadStatusHTML(message)}
                        </div>
                        <span class="font-semibold text-gray-700 text-sm">Anda</span>
                    </div>
                    <div class="bg-blue-100  rounded-2xl rounded-br-md px-4 py-3 shadow-sm">
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
            <div id="${message.id}" class="flex items-start justify-start">
                <div class="flex-shrink-0 mr-3">
                    ${avatarHTML}
                </div>
                <div class="flex flex-col items-start max-w-[70%]">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-700 text-sm">${senderName}</span>
                        <span class="text-xs text-gray-500">${time}</span>
                    </div>
                    <div class="bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                        ${contentHTML}
                        ${attachmentsHTML}
                    </div>
                </div>
            </div>
        `;
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

function updateSendButton() {
    const hasText = messageInput.value.trim().length > 0;
    const hasFiles = selectedFiles.length > 0;
    sendButton.style.display = (hasText || hasFiles) ? 'flex' : 'none';
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

// -----------------------------------------------------------------
// LANGKAH 3.5: Debounce Helper
// -----------------------------------------------------------------

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

// -----------------------------------------------------------------
// LANGKAH 3.6: Typing Functions (TAMBAHKAN SETELAH FUNGSI HELPER)
// -----------------------------------------------------------------

function showTypingIndicator(userId, userName) {
    // Hapus indicator lama jika ada
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
    scrollToBottom();
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) indicator.remove();
}

function handleUserTyping(data) {
    if (data.user_id === AUTH_USER_ID) return; // Jangan tampilkan untuk diri sendiri

    currentlyTypingUsers.add(data.user_id);
    showTypingIndicator(data.user_id, data.user_name);

    // Auto hide setelah 3 detik
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

function renderSidebarContent(data, sortedConversations) {
    let html = '';

    // 1. RUANG KERJA (Group utama)
    if (data.main_group) {
        html += `<div class="px-6 pt-4 pb-2"><span class="text-xs font-semibold text-gray-500 uppercase">Ruang Kerja</span></div>`;
        html += createConversationHTML(data.main_group);
    }

    // 2. PERCAKAPAN (Chat yang sudah ada)
    if (sortedConversations.length > 0) {
        html += `<div class="px-6 pt-4 pb-2 mt-2"><span class="text-xs font-semibold text-gray-500 uppercase">Percakapan</span></div>`;
        html += sortedConversations.map(createConversationHTML).join('');
    }

    // 3. ANGGOTA TIM (User yang bisa diajak chat)
    const existingPrivateChatUserIds = data.conversations
        .filter(c => c.type === 'private')
        .flatMap(c => c.participants)
        .map(p => p.user_id)
        .filter(id => id !== AUTH_USER_ID);
    const existingUserSet = new Set(existingPrivateChatUserIds);
    const availableMembers = data.members.filter(member => {
        return !existingUserSet.has(member.id) && member.id !== AUTH_USER_ID;
    });

    if (availableMembers.length > 0) {
        html += `<div class="px-6 pt-4 pb-2 mt-2"><span class="text-xs font-semibold text-gray-500 uppercase">Anggota Tim</span></div>`;
        availableMembers.forEach(member => {
            html += createMemberHTML(member);
        });
    }

    if (html === '') {
        chatListContainer.innerHTML = '<div class="p-6 text-center text-gray-500">Belum ada data.</div>';
        return;
    }

    chatListContainer.innerHTML = html;
}

// Search function
async function searchUsers(searchTerm) {
    if (!searchTerm.trim()) {
        await loadConversations(); // Load semua kembali
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

// -----------------------------------------------------------------
// LANGKAH 4: Fungsi Fetch Data (API Calls)
// -----------------------------------------------------------------

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

        // 🔥 PERBAIKAN: Render sidebar dengan urutan yang benar
        renderSidebarContent(data, sortedConversations);

    } catch (error) {
        console.error(error);
        chatListContainer.innerHTML = '<div class="p-6 text-center text-red-500">Gagal memuat. (JS Error)</div>';
    }
}


// -----------------------------------------------------------------
// 🔥 FUNGSI DOWNLOAD & PREVIEW GAMBAR (TAMBAHKAN SEBELUM formatFileSize)
// -----------------------------------------------------------------

/** Download gambar */
window.downloadImage = async function (imageUrl, fileName) {
    try {
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

        // Tampilkan notifikasi sukses
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'success',
            title: 'Gambar berhasil diunduh'
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

/** Preview gambar full screen dengan modal */
window.openImageModal = function (imageUrl, fileName) {
    Swal.fire({
        imageUrl: imageUrl,
        imageAlt: fileName,
        showCloseButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-download"></i> Download',
        confirmButtonColor: '#3B82F6',
        showCancelButton: true,
        cancelButtonText: 'Tutup',
        customClass: {
            popup: 'rounded-2xl',
            image: 'rounded-lg',
            confirmButton: 'rounded-lg px-4 py-2',
            cancelButton: 'rounded-lg px-4 py-2'
        },
        width: 'auto',
        padding: '2rem',
        background: '#1f2937',
        color: '#fff',
        imageWidth: '90%',
        imageHeight: 'auto'
    }).then((result) => {
        if (result.isConfirmed) {
            downloadImage(imageUrl, fileName);
        }
    });
}

// -----------------------------------------------------------------
// FILE UPLOAD HELPERS
// -----------------------------------------------------------------

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

window.removeFile = function (index) {
    selectedFiles.splice(index, 1);
    renderFilePreview();
}

// 🔥 FUNGSI DETECT LINKS YANG LEBIH KOMPREHENSIF
function detectAndCreateLinks(text) {
    if (!text) return '';

    // Regex untuk mendeteksi berbagai jenis URL
    const urlRegex = /(https?:\/\/[^\s<]+[^<.,:;"')\]\s])/g;

    return text.replace(urlRegex, function (url) {
        // Bersihkan URL dari karakter punctuation di akhir
        let cleanUrl = url;
        const punctuation = ['.', ',', '!', '?', ';', ':', ')', ']', '}'];

        while (punctuation.includes(cleanUrl.slice(-1))) {
            cleanUrl = cleanUrl.slice(0, -1);
        }

        // Coba buat URL object untuk validasi
        try {
            const urlObj = new URL(cleanUrl);
            let displayText = urlObj.hostname.replace('www.', '');

            // Tambahkan path jika tidak terlalu panjang
            if (urlObj.pathname !== '/' && displayText.length + urlObj.pathname.length < 30) {
                displayText += urlObj.pathname;
            }

            return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer"
                    class="text-blue-500 hover:text-blue-600 hover:underline break-words"
                    title="${cleanUrl}">${displayText}</a>`;

        } catch (e) {
            // Jika URL tidak valid, return teks asli
            return url;
        }
    });
}

/** 🔥 PERBAIKAN KRUSIAL: Setup listener Laravel Echo */
function setupEchoListeners() {
    if (typeof Echo === 'undefined') {
        console.error('❌ Laravel Echo not configured (Echo is undefined).');
        return;
    }

    console.log('🚀 Setting up Echo listeners...');
    console.log('📋 All conversations:', window.allConversations);

    // Hentikan listener lama
    window.allConversations.forEach(conversation => {
        const channelName = `conversation.${conversation.id}`;
        console.log(`🔌 Leaving old channel: private-${channelName}`);
        Echo.leave(channelName);
    });

    // Dengarkan di setiap channel percakapan
    window.allConversations.forEach(conversation => {
        const channelName = `conversation.${conversation.id}`;
        console.log(`🔔 Subscribing to: private-${channelName}`);

        const channel = Echo.private(channelName);

        channel.subscribed(() => {
            console.log(`✅ Successfully subscribed to: private-${channelName}`);
        });

        channel.error((error) => {
            console.error(`❌ Error subscribing to ${channelName}:`, error);
        });

        // 🔥 Listen untuk NewMessageSent event
        channel.listen('.NewMessageSent', (e) => {
            console.log('📨 ===== NEW MESSAGE EVENT =====');
            console.log('📨 Channel:', channelName);
            console.log('📨 Raw event:', e);
            console.log('📨 Message data:', e.message);
            console.log('📨 Current conversation:', currentConversationId);
            console.log('📨 ==============================');

            if (e.message) {
                handleNewMessage(e.message);
            } else {
                console.error('❌ Event tidak punya property "message":', e);
            }
        });

        // 🔥 Event untuk message deleted
        channel.listen('.MessageDeleted', (e) => {
            console.log('🗑️ ===== DELETE MESSAGE EVENT =====');
            console.log('🗑️ Channel:', channelName);
            console.log('🗑️ Raw event:', e);
            console.log('🗑️ ==================================');

            if (e.message_id || e.id) {
                handleMessageDeleted(e);
            }
        });

        // 🔥 PERBAIKAN: Tambahkan typing indicators
        channel.listenForWhisper('typing', (data) => {
            console.log('⌨️ Typing event received:', data);
            handleUserTyping(data);
        });

        channel.listenForWhisper('stop-typing', (data) => {
            console.log('⏹️ Stop typing event received:', data);
            handleUserStopTyping(data);
        });
    });

    console.log('✅ Echo listeners setup complete!');
    console.log('📡 Active channels:', Object.keys(window.Echo.connector.channels));
}


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
        setupEchoListeners();

    } catch (error) {
        console.error(error);
        chatHeaderTitle.textContent = 'Gagal';
        messageList.innerHTML = '<div class="p-6 text-center text-red-500">Gagal membuat percakapan.</div>';
    }
}

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

    document.querySelectorAll('#chatListContainer div[data-conversation-id]').forEach(div => {
        if (div.dataset.conversationId === conversationId) {
            div.classList.add('bg-[#E9EFFD]', 'text-blue-600', 'font-semibold');
            div.classList.remove('text-gray-800');
        } else {
            div.classList.remove('bg-[#E9EFFD]', 'text-blue-600', 'font-semibold');
            div.classList.add('text-gray-800');
        }
    });

    const badge = document.getElementById(`unread-badge-${conversationId}`);
    const countSpan = document.getElementById(`unread-count-${conversationId}`);
    if (badge && countSpan && parseInt(countSpan.textContent) > 0) {
        badge.style.display = 'none';
        countSpan.textContent = '0';
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

async function handleSendMessage(e) {
    e.preventDefault();

    if (isSending) {
        console.log('⚠️ Masih proses kirim sebelumnya, skip...');
        return;
    }

    const content = messageInput.value.trim();

    if (!content && selectedFiles.length === 0) {
        console.log('❌ Tidak ada konten atau file');
        return;
    }

    if (!currentConversationId) {
        console.log('❌ Tidak ada conversation yang dipilih');
        return;
    }

    console.log('📤 Preparing to send:', {
        content: content,
        files: selectedFiles.length,
        conversationId: currentConversationId
    });

    isSending = true;

    // Tampilkan loading state
    const originalSendHTML = sendButton.innerHTML;
    sendButton.innerHTML = `
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
    `;
    sendButton.disabled = true;
    messageInput.disabled = true;
    uploadButton.disabled = true;

    const formData = new FormData();
    formData.append('conversation_id', currentConversationId);
    formData.append('content', content);

    // File upload
    selectedFiles.forEach((file, index) => {
        formData.append('files[]', file, file.name);
        console.log(`📎 File ${index}:`, file.name, file.size, file.type);
    });

    // 🔥 PERBAIKAN: Hanya buat optimistic UI jika benar-benar perlu
    const tempMessageId = 'temp-' + Date.now();
    let shouldShowOptimistic = true;

    // Cek apakah sudah ada temp message yang sama
    const existingTempMessages = document.querySelectorAll('[id^="temp-"]');
    if (existingTempMessages.length > 0) {
        shouldShowOptimistic = false;
        console.log('⚠️ Ada temp message lain, skip optimistic UI');
    }

    if (shouldShowOptimistic) {
        const tempAttachments = selectedFiles.map(file => {
            const isImage = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            const isPDF = file.type === 'application/pdf';

            return {
                file_name: file.name,
                file_size: file.size,
                file_type: file.type,
                uploading: true,
                preview_url: isImage ? URL.createObjectURL(file) : null
            };
        });

        const tempMessage = {
            id: tempMessageId,
            content: content || `📎 Mengirim ${selectedFiles.length} file...`,
            sender_id: AUTH_USER_ID,
            sender: { full_name: 'Anda' },
            created_at: new Date().toISOString(),
            message_type: selectedFiles.length > 0 ? 'file' : 'text',
            is_read: false,
            attachments: tempAttachments
        };

        appendMessage(tempMessage);
    }

    // Reset input
    messageInput.value = '';
    selectedFiles = [];
    renderFilePreview();
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
            const errorText = await response.text();
            console.error('❌ Server error:', errorText);
            throw new Error(`Gagal mengirim pesan: ${response.status}`);
        }

        const result = await response.json();
        console.log('✅ Message sent successfully:', result.data.id);

        // 🔥 PERBAIKAN: Hapus temp message hanya jika optimistic UI ditampilkan
        if (shouldShowOptimistic) {
            const tempMsgElement = document.getElementById(tempMessageId);
            if (tempMsgElement) {
                tempMsgElement.remove();
                console.log('🗑️ Temp message removed, waiting for real message from broadcast...');
            }
        } else {
            console.log('⏩ Skip temp removal, no optimistic UI was shown');
        }

        // Update sidebar preview
        updateSidebarOnNewMessage(result.data, false);

    } catch (error) {
        console.error('❌ Error sending message:', error);

        // Tampilkan error di temp message
        const tempMsgElement = document.getElementById(tempMessageId);
        if (tempMsgElement) {
            const contentElement = tempMsgElement.querySelector('p');
            if (contentElement) {
                contentElement.textContent += ' ❌ (Gagal terkirim)';
                contentElement.classList.add('text-red-300');
            }
            tempMsgElement.classList.add('opacity-50');
        }

        // Tampilkan alert
        await Swal.fire({
            title: 'Gagal Mengirim',
            text: 'Pesan gagal terkirim. Silakan coba lagi.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } finally {
        // Reset loading state dengan benar
        isSending = false;
        sendButton.innerHTML = originalSendHTML;
        sendButton.disabled = false;
        messageInput.disabled = false;
        uploadButton.disabled = false;
        console.log('🔄 Reset isSending to false');
    }
}

// -----------------------------------------------------------------
// DRAG & DROP HANDLERS
// -----------------------------------------------------------------

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
    scrollToBottom();
}

// -----------------------------------------------------------------
// 🔥 PERBAIKAN UTAMA: Real-time (Laravel Echo)
// -----------------------------------------------------------------

function handleNewMessage(message) {
    console.log('📨 Received message:', message.id, 'from:', message.sender_id);

    const isOwnMessage = message.sender_id === AUTH_USER_ID;

    // 🔥 PERBAIKAN KRUSIAL: Untuk pesan sendiri, handle dengan benar
    if (isOwnMessage) {
        console.log('📤 Own message from broadcast:', message.id);

        // Cek apakah pesan sudah ada di DOM (untuk avoid duplicate)
        const existingMessage = document.getElementById(message.id);
        if (existingMessage) {
            console.log('✅ Message already exists, updating read status only');

            // Update read status saja, jangan buat duplicate
            const statusElement = existingMessage.querySelector('.read-status');
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
            return; // 🔥 STOP di sini, jangan append message lagi
        }

        // Jika di conversation yang sama dan belum ada, tampilkan pesan real
        if (message.conversation_id === currentConversationId) {
            console.log('✅ Displaying own message in current conversation');
            appendMessage(message);
        }

        // Update sidebar (tanpa increment unread)
        updateSidebarOnNewMessage(message, false);
        return;
    }

    // Pesan dari orang lain
    if (message.conversation_id === currentConversationId) {
        // Tampilkan di chat aktif
        appendMessage(message);
        markConversationAsRead(message.conversation_id);
        updateAllOwnMessagesToRead();
        updateSidebarOnNewMessage(message, false);
    } else {
        // Update sidebar dengan unread count
        updateSidebarOnNewMessage(message, true);
    }
}

/** 🔥 PERBAIKAN: Handle message deleted event */
function handleMessageDeleted(data) {
    console.log('🗑️ Message deleted event:', data);

    const messageId = data.message_id || data.id;
    const senderId = data.sender_id;
    const conversationId = data.conversation_id;

    // Ganti bubble dengan text "telah dihapus"
    const isOwnMessage = senderId === AUTH_USER_ID;
    replaceMessageWithDeletedText(messageId, isOwnMessage);

    // 🔥 PERBAIKAN: Update sidebar preview
    updateSidebarPreviewAfterDelete(conversationId);
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

// -----------------------------------------------------------------
// LANGKAH 6: Event Listeners
// -----------------------------------------------------------------

function setupInputListeners() {
    messageInput.addEventListener('input', updateSendButton);
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

    // 🔥 PERBAIKAN: Tambahkan search dengan debounce
    const searchInput = document.querySelector('input[placeholder="Cari rekan tim..."]');
    if (searchInput) {
        const debouncedSearch = debounce(searchUsers, 300);
        searchInput.addEventListener('input', (e) => {
            debouncedSearch(e.target.value);
        });

        // Clear search ketika dikosongkan
        searchInput.addEventListener('blur', (e) => {
            if (!e.target.value.trim()) {
                setTimeout(() => loadConversations(), 100);
            }
        });
    }

    // 🔥 PERBAIKAN: Typing indicators
    messageInput.addEventListener('input', function () {
        if (!currentConversationId) return;

        if (!isTyping) {
            isTyping = true;
            // Broadcast typing event
            console.log('⌨️ Sending typing event...');
            window.Echo.private(`conversation.${currentConversationId}`)
                .whisper('typing', {
                    user_id: AUTH_USER_ID,
                    user_name: 'Anda'
                });
        }

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            isTyping = false;
            // Broadcast stop typing
            console.log('⏹️ Sending stop typing event...');
            window.Echo.private(`conversation.${currentConversationId}`)
                .whisper('stop-typing', {
                    user_id: AUTH_USER_ID
                });
        }, 1000);
    });

    // Drag & Drop listeners
    chatInputBar.addEventListener('dragenter', handleDragEnter);
    chatInputBar.addEventListener('dragover', handleDragOver);
    chatInputBar.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);
    dropZone.addEventListener('dragenter', handleDragEnter);
    dropZone.addEventListener('dragover', handleDragOver);
}

function scrollToBottom() {
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

window.selectConversation = function (conversationId) {
    loadMessages(conversationId);
}

// -----------------------------------------------------------------
// LANGKAH 7.5: Enhanced Error Handling (TAMBAHKAN SEBELUM DOMContentLoaded)
// -----------------------------------------------------------------

// Global error handler
window.addEventListener('unhandledrejection', event => {
    console.error('Unhandled promise rejection:', event.reason);

    // Hanya tampilkan alert untuk error yang critical
    if (event.reason.message && !event.reason.message.includes('Failed to fetch')) {
        Swal.fire({
            title: 'Terjadi Kesalahan',
            text: 'Silakan refresh halaman dan coba lagi',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
});

// Network status monitor
window.addEventListener('online', () => {
    console.log('🟢 Koneksi internet pulih');
    // Bisa tambahkan notifikasi subtle di sini
});

window.addEventListener('offline', () => {
    console.log('🔴 Koneksi internet terputus');
    Swal.fire({
        title: 'Koneksi Terputus',
        text: 'Periksa koneksi internet Anda',
        icon: 'warning',
        confirmButtonText: 'OK'
    });
});

// -----------------------------------------------------------------
// LANGKAH 7: Inisialisasi
// -----------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async function () {
    const chatPageContainer = document.getElementById('chat-page-container');
    if (!chatPageContainer) {
        console.error('Chat container not found.');
        return;
    }

    WORKSPACE_ID = chatPageContainer.dataset.workspaceId;
    AUTH_USER_ID = chatPageContainer.dataset.authUserId; // Tetap string UUID
    API_URL = chatPageContainer.dataset.apiUrl;
    CSRF_TOKEN = chatPageContainer.dataset.csrfToken;

    cacheDOMElements();
    setupInputListeners();
    await loadConversations();
    setupEchoListeners();
    scrollToBottom();
});


