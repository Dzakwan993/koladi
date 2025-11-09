// -----------------------------------------------------------------
// LANGKAH 1: Setup Global Variables
// -----------------------------------------------------------------

// MODIFIKASI: Variabel-variabel ini sekarang dibaca dari data- attributes di HTML
let WORKSPACE_ID, AUTH_USER_ID, API_URL, CSRF_TOKEN;
let currentConversationId = null;
window.allConversations = [];

// 🔥 TAMBAHAN: Variable untuk file upload
let selectedFiles = [];

// 🔥🔥🔥 TAMBAHAN: Variable untuk cek status pengiriman
let isSending = false;

// -----------------------------------------------------------------
// LANGKAH 2: Ambil Elemen DOM
// -----------------------------------------------------------------
let container, chatContainer, messageList, chatListContainer, chatHeaderTitle,
    chatInputBar, sendMessageForm, messageInput, scrollToBottomBtn,
    micButton, sendButton,
    uploadButton, fileInput, filePreviewContainer, filePreviewList, dropZone;

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
    // HAPUS: micButton
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

/** Mengubah "John Doe" menjadi "JD" */
function getInitials(name) {
    if (!name) return '??';
    const names = name.split(' ');
    if (names.length === 1) return name.substring(0, 2).toUpperCase();
    return (names[0][0] + names[names.length - 1][0]).toUpperCase();
}

/** Fungsi untuk menghapus pesan */
/** 🔥 Fungsi untuk menghapus pesan dengan SweetAlert & bubble replacement */
window.deleteMessage = async function (messageId) {
    // SweetAlert confirmation
    const { value: willDelete } = await Swal.fire({
        title: 'Hapus Pesan?',
        text: "Pesan yang sudah dihapus tidak dapat dikembalikan",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    });

    if (!willDelete) return;

    try {
        const response = await fetch(`${API_URL}/api/chat/message/${messageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Gagal menghapus pesan');

        // SweetAlert success
        await Swal.fire({
            title: 'Terhapus!',
            text: 'Pesan berhasil dihapus',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });

        // 🔥 GANTI BUBBLE DENGAN PESAN "TELAH DIHAPUS" (WhatsApp Style)
        replaceMessageWithDeletedText(messageId);

    } catch (error) {
        console.error('Error deleting message:', error);

        // SweetAlert error
        await Swal.fire({
            title: 'Gagal!',
            text: 'Gagal menghapus pesan',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

/** 🔥 Ganti bubble chat dengan teks "pesan telah dihapus" */
/** 🔥 Ganti bubble chat dengan teks "pesan telah dihapus" */
function replaceMessageWithDeletedText(messageId, isOwnMessage = true) {
    const messageElement = document.getElementById(messageId);
    if (!messageElement) return;

    const deletedText = isOwnMessage ? 'Kamu telah menghapus pesan ini' : 'Pesan ini telah dihapus';
    const time = messageElement.querySelector('.text-xs.text-gray-500')?.textContent || '';

    // Cari nama pengirim asli (untuk pesan orang lain)
    let senderName = 'User';
    if (!isOwnMessage) {
        const nameElement = messageElement.querySelector('.font-semibold.text-gray-700');
        if (nameElement) {
            senderName = nameElement.textContent;
        }
    }

    const initials = getInitials(isOwnMessage ? 'Anda' : senderName);

    // Buat HTML bubble replacement
    const replacementHTML = isOwnMessage ? `
        <div id="${messageId}" class="flex items-start justify-end">
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
        <div id="${messageId}" class="flex items-start justify-start">
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

    // Animasi fade out dan replace
    messageElement.style.opacity = '0';
    messageElement.style.transform = 'scale(0.9)';

    setTimeout(() => {
        messageElement.outerHTML = replacementHTML;

        // Animasi fade in
        const newElement = document.getElementById(messageId);
        if (newElement) {
            newElement.style.opacity = '0';
            newElement.style.transform = 'scale(0.9)';

            setTimeout(() => {
                newElement.style.opacity = '1';
                newElement.style.transform = 'scale(1)';
                newElement.style.transition = 'all 0.3s ease';
            }, 50);
        }
    }, 300);
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

    // 🔥 PERBAIKAN: Handle preview BERBEDA untuk group vs private
    const lastMessage = conversation.last_message;
    let lastMessageText = 'Belum ada pesan';

    if (lastMessage) {
        // UNTUK GROUP: Tampilkan "Nama: [pesan]"
        if (conversation.type === 'group') {
            let senderName = 'Anda';
            if (lastMessage.sender_id !== AUTH_USER_ID) {
                if (lastMessage.sender && lastMessage.sender.full_name) {
                    // Ambil nama depan saja
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
                    const fileWord = fileCount > 1 ? 'file' : 'file';
                    lastMessageText = `${senderName}: Mengirim ${fileCount} ${fileWord}`;
                }
            } else {
                lastMessageText = `${senderName}: ${lastMessage.content}`;
            }
        }
        // UNTUK PRIVATE CHAT: Hanya tampilkan isi pesan (tanpa nama)
        else {
            const senderPrefix = lastMessage.sender_id === AUTH_USER_ID ? 'Anda: ' : '';

            if (lastMessage.attachments && lastMessage.attachments.length > 0) {
                if (lastMessage.content && lastMessage.content.trim() !== '') {
                    lastMessageText = senderPrefix + lastMessage.content;
                } else {
                    const fileCount = lastMessage.attachments.length;
                    const fileWord = fileCount > 1 ? 'file' : 'file';
                    lastMessageText = senderPrefix + `Mengirim ${fileCount} ${fileWord}`;
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

/** 🔥 Helper untuk status baca - 2 centang biru jika sudah dibaca */
function getReadStatusHTML(message) {
    const isRead = message.is_read;

    if (isRead) {
        // SUDAH DIBACA - 2 CENTANG BIRU (WhatsApp style)
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
        // BELUM DIBACA - 2 CENTANG ABU
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

/** Helper function untuk memperbaiki URL file */
function fixFileUrl(fileUrl) {
    if (!fileUrl) return '';

    // Jika URL menggunakan localhost tanpa port, ganti ke 127.0.0.1:8000
    if (fileUrl.includes('http://localhost/storage/')) {
        return fileUrl.replace('http://localhost/storage/', 'http://127.0.0.1:8000/storage/');
    }

    // Jika URL relatif, tambahkan base URL
    if (fileUrl.startsWith('chat_files/')) {
        return `${API_URL}/storage/${fileUrl}`;
    }

    // Jika URL tanpa domain (hanya /storage/...)
    if (fileUrl.startsWith('/storage/')) {
        return `${API_URL}${fileUrl}`;
    }

    // Jika sudah URL lengkap, return as-is
    return fileUrl;
}

/** Membuat HTML untuk satu gelembung pesan (urutan: Delete - Time - Centang - Nama) */
function createMessageHTML(message) {
    const isSender = message.sender_id === AUTH_USER_ID;
    const senderName = isSender ? 'Anda' : (message.sender ? message.sender.full_name : 'User');
    const initials = getInitials(senderName);
    const time = formatTime(message.created_at);

    // Render attachments
    let attachmentsHTML = '';
    if (message.attachments && message.attachments.length > 0) {
        attachmentsHTML = '<div class="mt-2 space-y-2">';
        message.attachments.forEach(att => {
            const isImage = att.file_type && att.file_type.startsWith('image/');
            const fileUrl = fixFileUrl(att.file_url);

            // Dalam createMessageHTML, bagian render attachments:
            if (att.uploading && att.preview_url && att.file_type.startsWith('image/')) {
                // Preview gambar yang sedang diupload
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
                // File non-gambar yang sedang diupload
                attachmentsHTML += `
        <div class="flex items-center gap-2 bg-white bg-opacity-20 rounded-lg p-3">
            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
            <span class="text-sm">Mengunggah ${att.file_name}...</span>
        </div>
    `;
            } else if (isImage) {
                // Gambar yang sudah terupload
                const imageUrl = fixFileUrl(att.file_url);
                attachmentsHTML += `
        <a href="${imageUrl}" target="_blank" class="block">
            <img src="${imageUrl}" alt="${att.file_name}"
                 class="max-w-xs rounded-xl shadow-md hover:opacity-90 transition">
        </a>
    `;
            }
        });
        attachmentsHTML += '</div>';
    }

    if (isSender) {
        return `
      <div id="${message.id}" class="flex items-start justify-end group">
          <div class="flex flex-col items-end max-w-[70%]">
              <!-- 🔥 HEADER DENGAN URUTAN: DELETE - TIME - CENTANG - NAMA -->
              <div class="flex items-center justify-end gap-2 mb-1">
    <!-- Tombol Delete -->
    <button class="delete-message-btn opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-red-500 p-1 rounded"
            title="Hapus pesan"
            onclick="deleteMessage('${message.id}')">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
    </button>

    <!-- Waktu -->
    <span class="text-xs text-gray-500">${time}</span>

    <!-- Status Baca -->
    <div class="flex items-center read-status">
        ${getReadStatusHTML(message)} <!-- 🔥 PASS THE WHOLE MESSAGE -->
    </div>

    <!-- Nama "Anda" -->
    <span class="font-semibold text-gray-700 text-sm">Anda</span>
</div>

              <!-- Bubble Chat -->
              <div class="bg-blue-500 text-white rounded-2xl rounded-br-md px-4 py-3 shadow-sm">
                  ${message.content ? `<p class="text-sm" style="word-break: break-word;">${message.content}</p>` : ''}
                  ${attachmentsHTML}
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
        return `
      <div id="${message.id}" class="flex items-start justify-start">
          <div class="flex-shrink-0 mr-3">
              <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center font-bold text-xs">
                  ${initials}
              </div>
          </div>
          <div class="flex flex-col items-start max-w-[70%]">
              <!-- Header untuk pesan orang lain -->
              <div class="flex items-center gap-2 mb-1">
                  <span class="font-semibold text-gray-700 text-sm">${senderName}</span>
                  <span class="text-xs text-gray-500">${time}</span>
              </div>

              <!-- Bubble Chat -->
              <div class="bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                  ${message.content ? `<p class="text-sm" style="word-break: break-word;">${message.content}</p>` : ''}
                  ${attachmentsHTML}
              </div>
          </div>
      </div>
      `;
    }
}

/** 🔥 Update status baca pesan real-time */
function updateMessageReadStatus(conversationId) {
    // Update semua pesan di conversation yang belum dibaca
    const messageElements = document.querySelectorAll(`[id^="message-"]`);
    messageElements.forEach(element => {
        const messageId = element.id.replace('message-', '');
        const statusElement = element.querySelector('.read-status');

        // Hanya update pesan sendiri yang belum dibaca
        if (statusElement && !statusElement.classList.contains('read')) {
            statusElement.innerHTML = getReadStatusHTML({ is_read: true });
            statusElement.classList.add('read');
        }
    });
}

function handleNewMessage(message) {
    console.log('📨 Received message:', message.id, 'from:', message.sender_id);

    const isOwnMessage = message.sender_id === AUTH_USER_ID;

    // Skip pesan sendiri yang baru dikirim (sudah ditampilkan via optimistic UI)
    if (isOwnMessage && message.id.includes('temp-')) {
        console.log('✅ Skip own temp message');
        return;
    }

    if (message.conversation_id === currentConversationId) {
        if (!isOwnMessage) {
            // Pesan dari orang lain - TAMPILKAN
            appendMessage(message);
            markConversationAsRead(message.conversation_id);
            updateAllOwnMessagesToRead();
        } else {
            // Pesan sendiri dari real-time (bukan yang baru dikirim)
            appendMessage(message);
        }
        updateSidebarOnNewMessage(message, false);
    } else {
        if (!isOwnMessage) {
            updateSidebarOnNewMessage(message, true);
        } else {
            updateSidebarOnNewMessage(message, false);
        }
    }
}

/** 🔥 Update semua pesan sendiri jadi status dibaca */
function updateAllOwnMessagesToRead() {
    const ownMessageElements = document.querySelectorAll('.flex.items-start.justify-end');

    ownMessageElements.forEach(element => {
        const statusElement = element.querySelector('.read-status');
        if (statusElement) {
            // Ganti dengan 2 centang biru
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

// Di updateSendButton(), hapus logika mic
function updateSendButton() {
    const hasText = messageInput.value.trim().length > 0;
    const hasFiles = selectedFiles.length > 0;

    // Tampilkan tombol send jika ada text atau file
    sendButton.style.display = (hasText || hasFiles) ? 'flex' : 'none';
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
// FUNGSI HELPER UNTUK FILE UPLOAD
// -----------------------------------------------------------------

/** Format ukuran file (bytes ke KB/MB) */
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

/** Dapatkan icon berdasarkan tipe file */
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
    } else if (fileType.includes('word') || fileType.includes('document')) {
        return `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>`;
    } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
        return `<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
        </svg>`;
    } else {
        return `<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>`;
    }
}

/** Render preview file yang dipilih */
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
            // Preview untuk gambar
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
            // Preview untuk file non-gambar
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

    // Toggle tombol send
    updateSendButton();
}

/** Hapus file dari preview */
window.removeFile = function (index) {
    selectedFiles.splice(index, 1);
    renderFilePreview();
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


async function handleSendMessage(e) {
    e.preventDefault();

    if (isSending) {
        console.log('⚠️ Masih proses kirim sebelumnya, skip...');
        return;
    }

    const content = messageInput.value.trim();

    // Validasi: harus ada text atau file
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
    sendButton.disabled = true;

    // Buat FormData untuk kirim text + files
    const formData = new FormData();
    formData.append('conversation_id', currentConversationId);
    formData.append('content', content);

    // Tambahkan file ke FormData
    selectedFiles.forEach((file, index) => {
        formData.append('files[]', file);
        console.log(`📎 File ${index}:`, file.name, file.size, file.type);
    });

    // Optimistic UI: Tampilkan pesan sementara dengan preview file
    const tempMessageId = 'temp-' + Date.now();

    // Buat attachments untuk optimistic UI
    const tempAttachments = selectedFiles.map(file => {
        const isImage = file.type.startsWith('image/');

        return {
            file_name: file.name,
            file_size: file.size,
            file_type: file.type,
            uploading: true,
            // 🔥 TAMBAHKAN PREVIEW UNTUK GAMBAR
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

        // Hapus pesan sementara
        const tempMsgElement = document.getElementById(tempMessageId);
        if (tempMsgElement) {
            tempMsgElement.remove();
        }

        // Update sidebar
        updateSidebarOnNewMessage(result.data, false);

    } catch (error) {
        console.error('❌ Error sending message:', error);
        const tempMsgElement = document.getElementById(tempMessageId);
        if (tempMsgElement) {
            tempMsgElement.querySelector('p').textContent += ' (Gagal terkirim)';
            tempMsgElement.classList.add('opacity-50');
        }
    } finally {
        // 🔥 PASTIKAN SELALU RESET STATUS PENGIRIMAN
        isSending = false;
        sendButton.disabled = false;
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

    // Filter file yang valid (max 10MB per file)
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

/** BARU: Setup listener Laravel Echo dengan debugging lengkap */
function setupEchoListeners() {
    if (typeof Echo === 'undefined') {
        console.error('Laravel Echo not configured (Echo is undefined).');
        return;
    }

    console.log('🚀 Setting up Echo listeners...');
    console.log('📋 All conversations:', window.allConversations);

    // Hentikan listener lama jika ada
    window.allConversations.forEach(conversation => {
        Echo.leave(`conversation.${conversation.id}`);
    });

    // Dengarkan di setiap channel percakapan
    window.allConversations.forEach(conversation => {
        const channelName = `conversation.${conversation.id}`;

        console.log(`🔔 Listening to: private-${channelName}`);

        const channel = Echo.private(channelName);

        // Tambahkan subscription success handler
        channel.subscribed(() => {
            console.log(`✅ Successfully subscribed to: private-${channelName}`);
        });

        // 🔥 PERBAIKAN: Tambahkan titik sebelum nama event
        channel.listen('.NewMessage', (e) => {
            console.log('📨 RAW EVENT dari Pusher:', e);
            console.log('📨 Message object:', e.message);

            if (e.message) {
                handleNewMessage(e.message);
            } else {
                console.error('❌ Event tidak punya property "message":', e);
            }
        });

        channel.listen('.MessageDeleted', (e) => {
            console.log('🗑️ Message deleted event:', e);
            replaceMessageWithDeletedText(e.message_id, false); // false = bukan pesan sendiri
        });
    });

    console.log('✅ Echo listeners setup complete!');
    console.log('📡 Active channels:', Object.keys(window.Echo.connector.channels));
}

/** BARU: Meng-update sidebar (preview & unread) saat ada pesan baru */
function updateSidebarOnNewMessage(message, incrementUnread) {
    const conversationId = message.conversation_id;

    // 1. Update Teks Preview
    const previewText = document.getElementById(`preview-${conversationId}`);
    if (previewText) {
        let previewContent = '';

        // Cari data conversation untuk tahu type-nya
        const conversation = window.allConversations.find(c => c.id === conversationId);
        const isGroup = conversation && conversation.type === 'group';

        // UNTUK GROUP: Tampilkan "Nama: [pesan]"
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
                    const fileWord = fileCount > 1 ? 'file' : 'file';
                    previewContent = `${senderName}: Mengirim ${fileCount} ${fileWord}`;
                }
            } else {
                previewContent = `${senderName}: ${message.content}`;
            }
        }
        // UNTUK PRIVATE CHAT: Hanya tampilkan isi pesan (tanpa nama)
        else {
            const senderPrefix = message.sender_id === AUTH_USER_ID ? 'Anda: ' : '';

            if (message.attachments && message.attachments.length > 0) {
                if (message.content && message.content.trim() !== '') {
                    previewContent = senderPrefix + message.content;
                } else {
                    const fileCount = message.attachments.length;
                    const fileWord = fileCount > 1 ? 'file' : 'file';
                    previewContent = senderPrefix + `Mengirim ${fileCount} ${fileWord}`;
                }
            } else {
                previewContent = senderPrefix + message.content;
            }
        }

        previewText.textContent = previewContent;
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

    // 3. Pindahkan item ke atas
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

    // HAPUS: event listener untuk micButton
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

    // Drag & drop listeners tetap
    chatInputBar.addEventListener('dragenter', handleDragEnter);
    chatInputBar.addEventListener('dragover', handleDragOver);
    chatInputBar.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);
    dropZone.addEventListener('dragenter', handleDragEnter);
    dropZone.addEventListener('dragover', handleDragOver);
}

/** Tambahkan event listener untuk delete di setiap pesan */
function addDeleteListener(messageElement, messageId, senderId) {
    // Hanya tambahkan delete untuk pesan sendiri
    if (senderId === AUTH_USER_ID) {
        const deleteBtn = messageElement.querySelector('.delete-message-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => deleteMessage(messageId));
        }
    }
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

