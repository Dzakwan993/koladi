let S,f,b,C,ee,y=null;window.allConversations=[];window.pendingDeletes=new Set;window.pendingMessages=new Set;let w=[],q=!1,B=null,R=null,z=new Map,te,N=!1,L=new Set;document.addEventListener("DOMContentLoaded",async function(){const e=document.getElementById("chat-page-container");e&&(S=e.dataset.workspaceId,f=e.dataset.authUserId,b=e.dataset.apiUrl,C=e.dataset.csrfToken,ee="workspace",console.log("Workspace Chat Initialized:",{WORKSPACE_ID:S,AUTH_USER_ID:f,CHAT_SCOPE:ee}),$e(),We(),await j(),ve(),me())});async function j(){const e=document.getElementById("chatListContainer");e.innerHTML='<div class="p-4 text-center text-gray-500">Memuat percakapan workspace...</div>';try{const t=await fetch(`${b}/api/workspace/${S}/chat-data`);if(!t.ok)throw new Error("Gagal memuat percakapan workspace");const n=await t.json();window.allConversations=[n.main_group,...n.conversations||[]].filter(Boolean),we(n)}catch(t){console.error("Error loading workspace conversations:",t),e.innerHTML='<div class="p-6 text-center text-red-500">Gagal memuat percakapan workspace.</div>'}}function we(e){const t=document.getElementById("chatListContainer");let n="";if(e.main_group&&(n+=`
            <div class="px-6 pt-4 pb-2">
                <span class="text-xs font-semibold text-gray-500 uppercase">Ruang Kerja</span>
            </div>
            ${Te(e.main_group)}
        `),e.members&&e.members.length>0&&(n+=`
            <div class="px-6 pt-4 pb-2 mt-2">
                <span class="text-xs font-semibold text-gray-500 uppercase">Anggota Tim</span>
            </div>
        `,e.members.forEach(i=>{n+=fe(i)})),n===""){t.innerHTML='<div class="p-6 text-center text-gray-500">Belum ada data percakapan.</div>';return}t.innerHTML=n}async function K(e){if(!e)return;y=e;const t=document.getElementById("messageList");t.innerHTML='<div class="p-6 text-center text-gray-500">Memuat pesan...</div>';const n=document.getElementById("chatInputBar");n.style.display="block";try{const i=await fetch(`${b}/api/chat/${e}/messages`);if(!i.ok)throw new Error("Gagal memuat pesan");const r=await i.json();r.forEach(s=>{s.reply_to&&(s.replyTo=s.reply_to)}),r.forEach(s=>{z.set(s.id,s)}),r.length===0?t.innerHTML='<div class="p-6 text-center text-gray-500">Belum ada pesan di percakapan ini.</div>':Be(r);const o=window.allConversations.find(s=>s.id===e);if(o){let s=o.name;if(o.type==="private"){const a=o.participants.find(d=>d.user_id!==f);s=a?a.user.full_name:"Unknown"}document.getElementById("chatHeaderTitle").textContent=s}ye(),await ge(e),h.scrollTop=h.scrollHeight,E.style.display="none"}catch(i){console.error("Error loading messages:",i),t.innerHTML='<div class="p-6 text-center text-red-500">Gagal memuat pesan.</div>'}}window.startChatWithUser=async function(e,t){const n=document.getElementById("chatHeaderTitle");n.textContent=`Membuka chat dengan ${t}...`;const i=document.getElementById("messageList");i.innerHTML='<div class="p-6 text-center text-gray-500">Memuat...</div>';const r=document.getElementById("chatInputBar");r.style.display="none";try{const o=window.allConversations.find(l=>l.type==="private"&&l.participants.some(c=>c.user_id===e));if(o){await K(o.id);return}const s=await fetch(`${b}/api/chat/create`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":C,Accept:"application/json"},body:JSON.stringify({workspace_id:S,type:"private",participants:[e]})});if(!s.ok)throw new Error("Gagal membuat percakapan");const d=(await s.json()).conversation.id;await j(),await K(d),ve()}catch(o){console.error(o),n.textContent="Gagal",i.innerHTML='<div class="p-6 text-center text-red-500">Gagal membuat percakapan.</div>'}};async function be(e){if(e.preventDefault(),q)return;const t=document.getElementById("messageInput"),n=t.value.trim();if(!n&&w.length===0||!y)return;q=!0;const i=document.getElementById("sendButton"),r=i.innerHTML;i.innerHTML='<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>',i.disabled=!0,t.disabled=!0;const o=document.getElementById("uploadButton");o.disabled=!0;const s=new FormData;s.append("conversation_id",y),s.append("content",n),B&&s.append("reply_to_message_id",B),w.forEach(a=>{s.append("files[]",a,a.name)}),t.value="",w=[],U(),B&&cancelReply(),D();try{const a=await fetch(`${b}/api/chat/send`,{method:"POST",headers:{"X-CSRF-TOKEN":C,Accept:"application/json"},body:s});if(!a.ok)throw new Error(`Gagal mengirim pesan: ${a.status}`);const d=await a.json();_(d.data,!1),setTimeout(()=>{Y()},200)}catch(a){console.error("❌ Error sending message:",a),await Swal.fire({title:"Gagal Mengirim",text:"Pesan gagal terkirim. Silakan coba lagi.",icon:"error",confirmButtonText:"OK"})}finally{q=!1,i.innerHTML=r,i.disabled=!1,t.disabled=!1,o.disabled=!1}}function Y(){const e=h.scrollHeight,t=h.clientHeight,n=h.scrollTop;e-n-t<200?(h.scrollTo({top:e,behavior:"smooth"}),setTimeout(()=>{E.style.display="none"},300)):E.style.display="flex"}let h,k,oe,P,W,x,E,re,le,F,V,X,T;function $e(){document.getElementById("chat-page-container"),h=document.getElementById("chatContainer"),k=document.getElementById("messageList"),oe=document.getElementById("chatListContainer"),document.getElementById("chatHeaderTitle"),P=document.getElementById("chatInputBar"),W=document.getElementById("sendMessageForm"),x=document.getElementById("messageInput"),E=document.getElementById("scrollToBottom"),re=document.getElementById("sendButton"),le=document.getElementById("uploadButton"),F=document.getElementById("fileInput"),V=document.getElementById("filePreviewContainer"),X=document.getElementById("filePreviewList"),T=document.getElementById("dropZone")}function I(e){if(!e)return"??";const t=e.split(" ");return t.length===1?e.substring(0,2).toUpperCase():(t[0][0]+t[t.length-1][0]).toUpperCase()}function ke(e){return e?new Date(e).toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit",hour12:!1}):""}function J(e){return e&&e.avatar?e.avatar.startsWith("http")?e.avatar:e.avatar.startsWith("avatars/")?`${b}/storage/${e.avatar}`:`${b}/storage/avatars/${e.avatar}`:null}function ne(e){if(!e)return"";const t=window.location.origin;return e.startsWith("chat_files/")?`${t}/storage/${e}`:e.startsWith("storage/")?`${t}/${e}`:e}function de(e){return e>=1073741824?(e/1073741824).toFixed(2)+" GB":e>=1048576?(e/1048576).toFixed(2)+" MB":e>=1024?(e/1024).toFixed(2)+" KB":e+" B"}function ce(e){return!e||typeof e!="string"?`<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>`:e.startsWith("image/")?`<svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>`:e.startsWith("video/")?`<svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>`:e==="application/pdf"?`<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>`:`<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>`}function ue(e){return e.is_read?`
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <svg class="w-3.5 h-3.5 text-blue-500 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            `:`
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <svg class="w-3.5 h-3.5 text-gray-400 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            `}function pe(e){return`
    <div class="flex justify-center items-center my-4">
        <span class="bg-white border border-gray-200 rounded-full px-4 py-1 text-xs text-gray-500 shadow-sm">
            ${e}
        </span>
    </div>
    `}function Ee(e){if(!e)return"";const t=/(https?:\/\/[^\s<]+[^<.,:;"')\]\s])/g;return e.replace(t,function(n){let i=n;const r=[".",",","!","?",";",":",")","]","}"];for(;r.includes(i.slice(-1));)i=i.slice(0,-1);try{const o=new URL(i);let s=o.hostname.replace("www.","");return o.pathname!=="/"&&s.length+o.pathname.length<30&&(s+=o.pathname),`<a href="${i}" target="_blank" rel="noopener noreferrer"
                        class="text-blue-500 hover:text-blue-600 hover:underline break-words"
                        title="${i}">${s}</a>`}catch{return n}})}function D(){const e=x.value.trim().length>0,t=w.length>0;re.style.display=e||t?"flex":"none"}function Me(e,t){let n;return function(...r){const o=()=>{clearTimeout(n),e(...r)};clearTimeout(n),n=setTimeout(o,t)}}function me(){h.scrollTop=h.scrollHeight,setTimeout(()=>{E.style.display="none"},300)}function Te(e){let t=e.name||"Unnamed",n=null,i=I(e.name),r="bg-blue-200 text-blue-800";if(e.type==="private"){const m=e.participants?.find(u=>u.user_id!==f);m&&(t=m.user?.full_name||"Unknown",i=I(m.user?.full_name),n=J(m.user),r="bg-indigo-100 text-indigo-800")}const o=n?`<img src="${n}" alt="${t}" class="w-10 h-10 rounded-full object-cover border border-gray-200">`:`<div class="w-10 h-10 rounded-full ${r} flex items-center justify-center font-bold text-sm">${i}</div>`,s=e.last_message;let a="Belum ada pesan";if(s)if(s.message_type==="deleted"||s.deleted_at!==null&&s.deleted_at!==void 0||s.content===null&&(!s.attachments||s.attachments.length===0))s.sender_id===f?a="Kamu telah menghapus pesan ini":a=`${s.sender?.full_name?.split(" ")[0]||"User"}: Pesan telah dihapus`;else if(e.type==="group"){let u="Anda";if(s.sender_id!==f&&(s.sender?.full_name?u=s.sender.full_name.split(" ")[0]:u="User"),s.attachments&&s.attachments.length>0)if(s.content&&s.content.trim()!=="")a=`${u}: ${s.content}`;else{const p=s.attachments.length,g=s.attachments[0]?.file_type||"file";g.startsWith("image/")?a=`${u}: 📷 Gambar`:g.startsWith("video/")?a=`${u}: 🎬 Video`:g==="application/pdf"?a=`${u}: 📄 PDF`:a=`${u}: 📎 ${p} file`}else a=`${u}: ${s.content||"Mengirim pesan"}`}else{const u=s.sender_id===f?"Anda: ":"";if(s.attachments&&s.attachments.length>0)if(s.content&&s.content.trim()!=="")a=u+s.content;else{const p=s.attachments.length,g=s.attachments[0]?.file_type||"file";g.startsWith("image/")?a=u+"📷 Gambar":g.startsWith("video/")?a=u+"🎬 Video":g==="application/pdf"?a=u+"📄 PDF":a=u+`📎 ${p} file`}else a=u+(s.content||"Mengirim pesan")}const d=e.unread_count||0,l=y===e.id;return`
    <div class="px-6 py-3 cursor-pointer ${l?"bg-blue-100 border-l-4 border-blue-500":"hover:bg-gray-50"} transition-all duration-200"
         data-conversation-id="${e.id}"
         onclick="selectConversation('${e.id}')">
        <div class="flex items-center">
            <div class="relative flex-shrink-0">
                ${o}
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <h4 class="text-sm font-semibold truncate ${l?"text-blue-700":"text-gray-800"}">${t}</h4>
                <p id="preview-${e.id}" class="text-xs truncate ${l?"text-blue-600":"text-gray-500"}">${a}</p>
            </div>
            <div id="unread-badge-${e.id}" class="ml-2 flex-shrink-0" style="${d>0?"display: block;":"display: none;"}">
                <div class="min-w-[18px] h-[18px] rounded-full bg-blue-500 flex items-center justify-center">
                    <span id="unread-count-${e.id}" class="text-[10px] font-semibold text-white px-1">${d}</span>
                </div>
            </div>
        </div>
    </div>`}function fe(e){const t=I(e.full_name),n=J(e),i=n?`<img src="${n}" alt="${e.full_name}" class="w-10 h-10 rounded-full object-cover border border-gray-200">`:`<div class="w-10 h-10 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center font-bold text-sm">${t}</div>`,r=window.allConversations.find(l=>l.type==="private"&&l.participants.some(c=>c.user_id==e.id));let o="Mulai percakapan",s=0;if(r&&r.last_message){const l=r.last_message;if(l.message_type==="deleted"||l.deleted_at!==null)o=l.sender_id==f?"Kamu telah menghapus pesan ini":"Pesan telah dihapus";else{const m=l.sender_id==f?"Anda: ":"";if(l.attachments&&l.attachments.length>0)if(l.content&&l.content.trim()!=="")o=m+l.content;else{const u=l.attachments[0].file_type;u.startsWith("image/")?o=m+"📷 Gambar":u.startsWith("video/")?o=m+"🎬 Video":u==="application/pdf"?o=m+"📄 PDF":o=m+`📎 ${l.attachments.length} file`}else l.content&&(o=m+l.content)}s=r.unread_count||0}const a=r&&y===r.id;return`
    <div class="px-6 py-3 cursor-pointer ${a?"bg-blue-100 border-l-4 border-blue-500":"hover:bg-gray-50"} transition-all duration-200"
         data-member-id="${e.id}"
         ${r?`data-conversation-id="${r.id}"`:""}
         onclick="startChatWithUser('${e.id}', '${e.full_name}')">
        <div class="flex items-center">
            <div class="relative flex-shrink-0">
                ${i}
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <h4 class="text-sm font-semibold truncate ${a?"text-blue-700":"text-gray-800"}">${e.full_name}</h4>
                <p id="preview-member-${e.id}" class="text-xs truncate ${a?"text-blue-600":"text-gray-500"}">${o}</p>
            </div>
            <div id="unread-badge-member-${e.id}" class="ml-2 flex-shrink-0" style="${s>0?"display: block;":"display: none;"}">
                <div class="min-w-[18px] h-[18px] rounded-full bg-blue-500 flex items-center justify-center">
                    <span id="unread-count-member-${e.id}" class="text-[10px] font-semibold text-white px-1">${s}</span>
                </div>
            </div>
        </div>
    </div>`}function Z(e){const t=e.sender_id===f,n=t?"Anda":e.sender?e.sender.full_name:"User",i=I(n),r=e.sender?J(e.sender):null,o=r?`<img src="${r}" alt="${n}" class="w-8 h-8 rounded-full object-cover border border-gray-200 flex-shrink-0">`:`<div class="w-8 h-8 rounded-full ${t?"bg-blue-200 text-blue-800":"bg-gray-200 text-gray-800"} flex items-center justify-center font-bold text-xs flex-shrink-0">${i}</div>`,s=ke(e.created_at),a=e.is_edited?'<span class="text-xs text-gray-400 ml-2">(diedit)</span>':"",d=e.message_type==="deleted"||e.deleted_at!==null&&e.deleted_at!==void 0||e.content===null&&(!e.attachments||e.attachments.length===0);if(d){const p=t?"Kamu telah menghapus pesan ini":"Pesan ini telah dihapus";return t?`
            <div id="${e.id}" class="flex items-start justify-end deleted-message mb-4">
                <div class="flex flex-col items-end max-w-[70%]">
                    <div class="flex items-center justify-end gap-2 mb-1">
                        <span class="text-xs text-gray-500">${s}</span>
                        <span class="font-semibold text-gray-700 text-sm">Anda</span>
                    </div>
                    <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-br-md px-4 py-3 shadow-sm italic">
                        <p class="text-sm">${p}</p>
                    </div>
                </div>
                <div class="flex-shrink-0 ml-3">
                    ${o}
                </div>
            </div>
        `:`
            <div id="${e.id}" class="flex items-start justify-start deleted-message mb-4">
                <div class="flex-shrink-0 mr-3">
                    ${o}
                </div>
                <div class="flex flex-col items-start max-w-[70%]">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-700 text-sm">${n}</span>
                        <span class="text-xs text-gray-500">${s}</span>
                    </div>
                    <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm italic">
                        <p class="text-sm">${p}</p>
                    </div>
                </div>
            </div>
        `}let l="";e.attachments&&e.attachments.length>0&&(l='<div class="mt-2 space-y-2">',e.attachments.forEach(p=>{const g=p.file_type||"",A=g&&g.startsWith("image/");if(g&&g.startsWith("video/"),p.uploading&&p.preview_url&&A)l+=`
                <div class="relative">
                    <img src="${p.preview_url}" alt="${p.file_name||"File"}"
                        class="max-w-xs rounded-xl shadow-md opacity-70">
                    <div class="absolute inset-0 bg-black bg-opacity-30 rounded-xl flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
                    </div>
                    <p class="text-xs text-gray-300 mt-1">Mengunggah...</p>
                </div>
            `;else if(p.uploading)l+=`
                <div class="flex items-center gap-2 bg-white bg-opacity-20 rounded-lg p-3">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    <span class="text-sm">Mengunggah ${p.file_name||"file"}...</span>
                </div>
            `;else if(A){const v=ne(p.file_url);l+=`
                <div class="relative group max-w-sm">
                    <img src="${v}"
                         alt="${p.file_name||"Image"}"
                         class="rounded-xl shadow-md cursor-pointer max-h-96 object-cover w-full"
                         onclick="openImageModal('${v}', '${p.file_name||"image"}')"
                         loading="lazy">
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="event.stopPropagation(); downloadImage('${v}', '${p.file_name||"image"}')"
                                class="bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition"
                                title="Download gambar">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `}else{const v=ne(p.file_url),$=ce(g),G=de(p.file_size||0);l+=`
                <div class="bg-white border border-gray-200 rounded-lg p-3 max-w-xs">
                    <a href="${v}" target="_blank" class="flex items-center gap-3 hover:bg-gray-50 rounded-lg p-2 transition">
                        <div class="flex-shrink-0">
                            ${$}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">${p.file_name||"File"}</p>
                            <p class="text-xs text-gray-500">${G}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            `}}),l+="</div>");let c="";if(e.reply_to_message_id)if(e.reply_to&&typeof e.reply_to=="object"){const p=e.reply_to,g=p.sender_id===f?"Anda":p.sender?.full_name||"User",A=p.message_type==="deleted"||p.deleted_at!==null&&p.deleted_at!==void 0;let v="",$="";if(A)v="Pesan telah dihapus";else if(p.attachments&&p.attachments.length>0){const xe=p.attachments.length,O=p.attachments[0]?.file_type||"";O.startsWith("image/")?(v="Gambar",$="🖼️"):O.startsWith("video/")?(v="Video",$="🎬"):O==="application/pdf"?(v="PDF",$="📄"):(v=`${xe} file`,$="📎"),p.content&&p.content.trim()!==""&&(v=p.content)}else v=p.content||"Pesan kosong";const G=v.length>50?v.substring(0,50)+"...":v;c=`
            <div class="reply-info mb-2 p-2 bg-blue-50 rounded-lg border-l-4 border-blue-400 cursor-pointer hover:bg-blue-100 transition-colors"
                 onclick="scrollToMessage('${e.reply_to_message_id}')">
                <div class="flex items-start gap-2">
                    <div class="text-blue-500 mt-0.5 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-blue-700 mb-1">
                            Membalas ${g}
                        </div>
                        <div class="text-xs text-blue-600 truncate flex items-center gap-1">
                            ${$?`<span>${$}</span>`:""}
                            <span class="truncate">${G}</span>
                        </div>
                    </div>
                </div>
            </div>
        `}else c=`
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
        `;let m="";d||(t?m=`
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button class="edit-message-btn text-gray-400 hover:text-blue-500 p-1 rounded"
                    title="Edit pesan"
                    onclick="startEditMessage('${e.id}')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <button class="reply-message-btn text-gray-400 hover:text-green-500 p-1 rounded"
                    title="Balas pesan"
                    onclick="startReplyMessage('${e.id}')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
            </button>
            <button class="delete-message-btn text-gray-400 hover:text-red-500 p-1 rounded"
                    title="Hapus pesan"
                    onclick="deleteMessage('${e.id}')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    `:m=`
                <button class="reply-message-btn opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-green-500 p-1 rounded"
                        title="Balas pesan"
                        onclick="startReplyMessage('${e.id}')">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </button>
            `);let u="";if(e.content&&e.content.trim()!=="")u=`<div class="message-content text-sm" style="word-break: break-word;">${Ee(e.content)}</div>`;else if(e.attachments&&e.attachments.length>0){const p=e.attachments.length;if(p===1){const g=e.attachments[0]?.file_type||"";g.startsWith("image/")?u='<div class="message-content text-sm italic">📷 Gambar</div>':g.startsWith("video/")?u='<div class="message-content text-sm italic">🎬 Video</div>':g==="application/pdf"?u='<div class="message-content text-sm italic">📄 PDF</div>':u='<div class="message-content text-sm italic">📎 File</div>'}else u=`<div class="message-content text-sm italic">📎 ${p} files</div>`}return t?`
            <div id="${e.id}" class="flex items-start justify-end group message-new mb-4">
                <div class="flex flex-col items-end max-w-[75%] min-w-0">
                    <div class="flex items-center justify-end gap-2 mb-1 w-full">
                        ${m}
                        <span class="text-xs text-gray-500 whitespace-nowrap">${s} ${a}</span>
                        <div class="flex items-center read-status">
                            ${ue(e)}
                        </div>
                        <span class="font-semibold text-gray-700 text-sm whitespace-nowrap">Anda</span>
                    </div>

                    <div class="bg-blue-100  rounded-2xl rounded-br-md px-4 py-3 shadow-sm w-auto min-w-0 max-w-full">
                        ${c}
                        ${u}
                        ${l}
                    </div>
                </div>
                <div class="flex-shrink-0 ml-3">
                    ${o}
                </div>
            </div>
        `:`
            <div id="${e.id}" class="flex items-start justify-start group message-new mb-4">
                <div class="flex-shrink-0 mr-3">
                    ${o}
                </div>
                <div class="flex flex-col items-start max-w-[75%] min-w-0">
                    <div class="flex items-center gap-2 mb-1 w-full">
                        <span class="font-semibold text-gray-700 text-sm whitespace-nowrap">${n}</span>
                        <span class="text-xs text-gray-500 whitespace-nowrap">${s} ${a}</span>
                        ${m}
                    </div>

                    <div class="bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm w-auto min-w-0 max-w-full">
                        ${c}
                        ${u}
                        ${l}
                    </div>
                </div>
            </div>
        `}function se(e){const t=k.querySelector(".flex.h-full.items-center.justify-center");t&&t.remove();const n=k.querySelector(".p-6.text-center.text-gray-500");n&&n.remove();const i=new Date().toDateString(),r=k.querySelectorAll("div.flex.justify-center.items-center span");let o=null;r.length>0&&(o=r[r.length-1].textContent.trim()),(!o||o!=="Hari ini")&&new Date(e.created_at).toDateString()===i&&k.insertAdjacentHTML("beforeend",pe("Hari ini"));const s=Z(e);k.insertAdjacentHTML("beforeend",s),setTimeout(()=>{Y()},100)}function U(){if(w.length===0){V.style.display="none";return}V.style.display="block",X.innerHTML="",w.forEach((e,t)=>{const n=e.type.startsWith("image/");let i="";if(n){const r=new FileReader;r.onload=o=>{const s=document.getElementById(`file-preview-${t}`);s&&(s.querySelector("img").src=o.target.result)},r.readAsDataURL(e),i=`
                    <div id="file-preview-${t}" class="relative bg-white rounded-lg border border-gray-200 p-2 w-24 h-24">
                        <img src="" alt="${e.name}" class="w-full h-16 object-cover rounded">
                        <button type="button" onclick="removeFile(${t})"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                            ×
                        </button>
                        <p class="text-xs text-gray-600 mt-1 truncate">${e.name}</p>
                    </div>
                `}else i=`
                    <div id="file-preview-${t}" class="relative bg-white rounded-lg border border-gray-200 p-3 flex items-center gap-2 max-w-xs">
                        <div class="flex-shrink-0">
                            ${ce(e.type)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">${e.name}</p>
                            <p class="text-xs text-gray-500">${de(e.size)}</p>
                        </div>
                        <button type="button" onclick="removeFile(${t})"
                            class="flex-shrink-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600">
                            ×
                        </button>
                    </div>
                `;X.insertAdjacentHTML("beforeend",i)}),D()}function Ce(e){if(z.has(e))return z.get(e);const t=document.getElementById(e);return t?{id:e,sender_id:t.classList.contains("justify-end")?f:"other",sender:{full_name:t.querySelector(".font-semibold")?.textContent||"User"},content:t.querySelector(".message-content")?.textContent||"",attachments:[]}:null}function Be(e){let t="",n=null;const i=new Date().toDateString(),r=new Date(Date.now()-864e5).toDateString();e.forEach(o=>{const s=new Date(o.created_at),a=s.toDateString();if(a!==n){let d=s.toLocaleDateString("id-ID",{day:"numeric",month:"long",year:"numeric"});a===i?d="Hari ini":a===r&&(d="Kemarin"),t+=pe(d),n=a}t+=Z(o)}),k.innerHTML=t}async function ge(e){try{if((await fetch(`${b}/api/chat/${e}/mark-as-read`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":C,Accept:"application/json"}})).ok){const n=document.getElementById(`unread-badge-${e}`),i=document.getElementById(`unread-count-${e}`);n&&i&&(n.style.display="none",i.textContent="0");const r=window.allConversations.find(o=>o.id===e);if(r&&r.type==="private"){const o=r.participants.find(s=>s.user_id!=f);if(o){const s=o.user_id,a=document.getElementById(`unread-badge-member-${s}`),d=document.getElementById(`unread-count-member-${s}`);a&&d&&(a.style.display="none",d.textContent="0")}}}}catch(t){console.error("Gagal menandai telah dibaca:",t)}}async function Le(e){if(!e.trim()){await j();return}try{const t=await fetch(`${b}/api/workspace/${S}/search-users?q=${encodeURIComponent(e)}`);if(!t.ok)throw new Error("Gagal mencari pengguna");const n=await t.json();_e(n)}catch(t){console.error("Search error:",t)}}function _e(e){let t="";e.length>0?(t+='<div class="px-6 pt-4 pb-2"><span class="text-xs font-semibold text-gray-500 uppercase">Hasil Pencarian</span></div>',e.forEach(n=>{t+=fe(n)})):t='<div class="p-6 text-center text-gray-500">Tidak ada hasil ditemukan</div>',oe.innerHTML=t}window.removeFile=function(e){w.splice(e,1),U()};window.downloadImage=async function(e,t){try{const n=Swal.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:2e3,timerProgressBar:!0,didOpen:a=>{a.addEventListener("mouseenter",Swal.stopTimer),a.addEventListener("mouseleave",Swal.resumeTimer)}});n.fire({icon:"info",title:"Mengunduh gambar..."});const r=await(await fetch(e)).blob(),o=window.URL.createObjectURL(r),s=document.createElement("a");s.href=o,s.download=t||"image.jpg",document.body.appendChild(s),s.click(),window.URL.revokeObjectURL(o),document.body.removeChild(s),n.fire({icon:"success",title:"Gambar berhasil diunduh!"})}catch(n){console.error("Error downloading image:",n),await Swal.fire({title:"Gagal",text:"Gagal mengunduh gambar",icon:"error",confirmButtonText:"OK"})}};window.openImageModal=function(e,t){const n=`
        <div id="imageModalOverlay" class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             style="background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px);"
             onclick="closeImageModal()">

            <div class="relative max-w-7xl max-h-[90vh] flex flex-col items-center"
                 onclick="event.stopPropagation()">

                <div class="relative bg-white rounded-lg shadow-2xl overflow-hidden max-h-[80vh]">
                    <img src="${e}"
                         alt="${t}"
                         class="max-w-full max-h-[80vh] w-auto h-auto object-contain"
                         style="display: block;">
                </div>

                <div class="mt-4 flex gap-3">
                    <button onclick="downloadImage('${e}', '${t}')"
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

                <p class="mt-3 text-sm text-white text-center max-w-md truncate">${t}</p>
            </div>
        </div>
    `;document.body.insertAdjacentHTML("beforeend",n),document.body.style.overflow="hidden";const i=r=>{r.key==="Escape"&&closeImageModal()};document.addEventListener("keydown",i),window.imageModalEscapeHandler=i};window.closeImageModal=function(){const e=document.getElementById("imageModalOverlay");e&&(e.style.transition="opacity 0.2s ease",e.style.opacity="0",setTimeout(()=>{e.remove(),document.body.style.overflow="",window.imageModalEscapeHandler&&(document.removeEventListener("keydown",window.imageModalEscapeHandler),delete window.imageModalEscapeHandler)},200))};window.startReplyMessage=function(e){if(!document.getElementById(e)){console.error("❌ Message element not found:",e);return}const n=Ce(e);if(!n){console.error("❌ Message data not found:",e);return}B=e;const i=document.getElementById("replyPreviewContainer"),r=document.getElementById("replySenderName"),o=document.getElementById("replyContent");if(i&&r&&o){const s=n.sender_id===f?"Anda":n.sender?n.sender.full_name:"User";let a=n.content||"";if(!a&&n.attachments&&n.attachments.length>0){const d=n.attachments[0].file_type;d.startsWith("image/")?a="Gambar":d.startsWith("video/")?a="Video":a="File"}r.textContent=`Membalas ${s}`,o.textContent=a.length>50?a.substring(0,50)+"...":a,i.style.display="block"}cancelEdit(),x&&(x.focus(),setTimeout(()=>{x.scrollIntoView({behavior:"smooth",block:"center"})},100))};window.cancelReply=function(){B=null;const e=document.getElementById("replyPreviewContainer");e&&(e.style.display="none")};window.startEditMessage=function(e){const t=document.getElementById(e);if(!t){console.error("❌ Message element not found:",e);return}R=e;let n="";const i=t.querySelector(".message-content");if(i)n=i.textContent||"";else{const s=t.querySelectorAll("p.text-sm");for(let a of s)if(!a.classList.contains("text-gray-500")&&!a.classList.contains("italic")){n=a.textContent||"";break}}cancelReply(),x.value=n.trim(),x.focus();const r=`
        <div id="editMode" class="mb-2 p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500 flex justify-between items-center">
            <div class="flex-1">
                <p class="text-xs font-medium text-yellow-700">Mengedit pesan</p>
                <p class="text-xs text-yellow-600 truncate">${n.substring(0,50)}${n.length>50?"...":""}</p>
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
    `,o=document.getElementById("editMode");o&&o.remove(),W.insertAdjacentHTML("beforebegin",r),D(),setTimeout(()=>{x.scrollIntoView({behavior:"smooth",block:"center"})},100)};window.saveEditMessage=async function(){if(!R)return;const e=x.value.trim();if(!e){await Swal.fire({title:"Error",text:"Pesan tidak boleh kosong",icon:"error",confirmButtonText:"OK"});return}try{const t=await fetch(`${b}/api/chat/message/${R}`,{method:"PUT",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":C,Accept:"application/json"},body:JSON.stringify({content:e})});if(!t.ok)throw new Error("Gagal mengedit pesan");(await t.json()).success&&(cancelEdit(),await Swal.fire({title:"Berhasil!",text:"Pesan berhasil diedit",icon:"success",timer:1500,showConfirmButton:!1}))}catch(t){console.error("Edit error:",t),await Swal.fire({title:"Gagal!",text:"Gagal mengedit pesan",icon:"error",confirmButtonText:"OK"})}};window.cancelEdit=function(){R=null,x.value="";const e=document.getElementById("editMode");e&&e.remove(),D()};window.scrollToMessage=function(e){const t=document.getElementById(e);if(!t){Swal.mixin({toast:!0,position:"top-end",showConfirmButton:!1,timer:2e3,timerProgressBar:!0}).fire({icon:"info",title:"Pesan tidak ditemukan atau belum di-load"});return}const n=h.getBoundingClientRect(),i=t.getBoundingClientRect(),r=h.scrollTop,s=i.top-n.top+r-h.clientHeight/2+i.height/2;h.scrollTo({top:s,behavior:"smooth"}),t.style.transition="all 0.5s cubic-bezier(0.4, 0, 0.2, 1)",t.style.backgroundColor="rgba(59, 130, 246, 0.2)",t.style.transform="scale(1.02)",t.style.boxShadow="0 0 0 3px rgba(59, 130, 246, 0.4)",setTimeout(()=>{t.style.backgroundColor="",t.style.transform="",t.style.boxShadow=""},2e3)};window.deleteMessage=async function(e){if(!e||e.startsWith("temp-")||(window.pendingDeletes||(window.pendingDeletes=new Set),window.pendingDeletes.has(e)))return;window.pendingDeletes.add(e);const t=document.body,n=window.scrollY,i=window.innerWidth-document.documentElement.clientWidth;t.style.overflow="hidden",t.style.paddingRight=i+"px",t.style.position="fixed",t.style.top=`-${n}px`,t.style.width="100%";const{value:r}=await Swal.fire({title:"Hapus Pesan?",text:"Pesan yang sudah dihapus tidak dapat dikembalikan",icon:"warning",showCancelButton:!0,confirmButtonColor:"#d33",cancelButtonColor:"#3085d6",confirmButtonText:"Ya, Hapus!",cancelButtonText:"Batal",reverseButtons:!0,customClass:{container:"swal-no-shift"}});if(t.style.overflow="",t.style.paddingRight="",t.style.position="",t.style.top="",t.style.width="",window.scrollTo(0,n),!r){window.pendingDeletes.delete(e);return}try{const o=document.getElementById(e),s=o&&o.classList.contains("justify-end");o&&o.parentNode&&document.body.contains(o)&&he(e,s);const a=await fetch(`${b}/api/chat/message/${e}`,{method:"DELETE",headers:{"X-CSRF-TOKEN":C,Accept:"application/json","Content-Type":"application/json"},credentials:"include"});if(a.status===404)throw new Error("Pesan tidak ditemukan (404)");if(a.status===403)throw new Error("Anda tidak memiliki akses untuk menghapus pesan ini");if(!a.ok){const l=await a.text();throw console.error("Server error:",l),new Error(`HTTP ${a.status}: ${l}`)}const d=await a.json();if(d.success)await Swal.fire({title:"Berhasil!",text:"Pesan berhasil dihapus",icon:"success",timer:1500,showConfirmButton:!1});else throw new Error(d.error||"Gagal menghapus pesan")}catch(o){console.error("❌ Delete error:",o),await Swal.fire({title:"Gagal!",text:o.message||"Terjadi kesalahan saat menghapus pesan",icon:"error",confirmButtonText:"OK"})}finally{window.pendingDeletes.delete(e)}};function M(e){const t=document.getElementById(`preview-${e}`);if(!t)return;const i=Array.from(document.querySelectorAll("#messageList > [id]")).filter(l=>{const c=l.id;return!c||c.includes("typing-indicator")?!1:!(l.classList.contains("deleted-message")||l.querySelector(".bg-gray-300.text-gray-600.italic"))});if(i.length===0){t.textContent="Belum ada pesan";return}const r=i[i.length-1];let o="";const s=r.querySelector(".message-content");s&&(o=s.textContent||"");const a=r.classList.contains("justify-end");if(o){const l=window.allConversations.find(m=>m.id===e);if(l&&l.type==="group"){const m=a?"Anda":r.querySelector(".font-semibold.text-gray-700")?.textContent||"User";t.textContent=`${m}: ${o}`}else{const m=a?"Anda: ":"";t.textContent=m+o}}else t.textContent="Mengirim file";const d=window.allConversations.find(l=>l.id===e);if(d&&d.type==="private"){const l=d.participants.find(c=>c.user_id!==f);if(l){const c=l.user_id,m=document.querySelector(`[data-member-id="${c}"]`);if(m){const u=m.querySelector("p.text-xs");u&&(u.textContent=t.textContent)}}}}function he(e,t=!0){const n=document.getElementById(e);if(!n){console.warn(`❌ Element dengan ID ${e} tidak ditemukan`),M(y);return}if(!n.parentNode||!document.body.contains(n)){console.warn(`❌ Element dengan ID ${e} tidak ada di DOM`),M(y);return}const i=t?"Kamu telah menghapus pesan ini":"Pesan ini telah dihapus",r=n.querySelector(".text-xs.text-gray-500"),o=r?r.textContent.split("(")[0].trim():"";let s="User";if(!t){const c=n.querySelector(".font-semibold.text-gray-700");c&&(s=c.textContent)}const a=n.querySelector("img[alt], .w-8.h-8.rounded-full");let d="";if(a&&a.tagName==="IMG")d=a.outerHTML;else if(a)d=a.outerHTML;else{const c=I(t?"Anda":s);d=`<div class="w-8 h-8 rounded-full ${t?"bg-blue-200 text-blue-800":"bg-gray-200 text-gray-800"} flex items-center justify-center font-bold text-xs">${c}</div>`}const l=t?`
        <div id="${e}" class="flex items-start justify-end deleted-message mb-4">
            <div class="flex flex-col items-end max-w-[70%]">
                <div class="flex items-center justify-end gap-2 mb-1">
                    <span class="text-xs text-gray-500">${o}</span>
                    <span class="font-semibold text-gray-700 text-sm">Anda</span>
                </div>
                <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-br-md px-4 py-3 shadow-sm italic">
                    <p class="text-sm">${i}</p>
                </div>
            </div>
            <div class="flex-shrink-0 ml-3">
                ${d}
            </div>
        </div>
    `:`
        <div id="${e}" class="flex items-start justify-start deleted-message mb-4">
            <div class="flex-shrink-0 mr-3">
                ${d}
            </div>
            <div class="flex flex-col items-start max-w-[70%]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-gray-700 text-sm">${s}</span>
                    <span class="text-xs text-gray-500">${o}</span>
                </div>
                <div class="bg-gray-300 text-gray-600 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm italic">
                    <p class="text-sm">${i}</p>
                </div>
            </div>
        </div>
    `;try{n.style.transition="opacity 0.3s ease",n.style.opacity="0",setTimeout(()=>{if(n.parentNode&&document.body.contains(n)){n.outerHTML=l;const c=document.getElementById(e);c&&(c.style.opacity="0",setTimeout(()=>{c.style.transition="opacity 0.3s ease",c.style.opacity="1"},50))}M(y)},300)}catch(c){console.error("❌ Error in replaceMessageWithDeletedText:",c),M(y)}}function Se(){document.querySelectorAll(".flex.items-start.justify-end").forEach(t=>{const n=t.querySelector(".read-status");n&&(n.innerHTML=`
                    <div class="flex items-center">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg class="w-3.5 h-3.5 text-blue-500 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                `)})}function _(e,t){const n=e.conversation_id,i=document.getElementById(`preview-${n}`);if(i){let s="";const a=window.allConversations.find(l=>l.id===n);if(a&&a.type==="group"){let l="Anda";if(e.sender_id!==f&&(e.sender&&e.sender.full_name?l=e.sender.full_name.split(" ")[0]:l="User"),e.attachments&&e.attachments.length>0)if(e.content&&e.content.trim()!=="")s=`${l}: ${e.content}`;else{const c=e.attachments.length;s=`${l}: Mengirim ${c} file`}else s=`${l}: ${e.content}`}else{const l=e.sender_id===f?"Anda: ":"";if(e.attachments&&e.attachments.length>0)if(e.content&&e.content.trim()!=="")s=l+e.content;else{const c=e.attachments.length;s=l+`Mengirim ${c} file`}else s=l+e.content}i.textContent=s}const r=window.allConversations.find(s=>s.id===n);if(r&&r.type==="private"){const s=r.participants.find(a=>a.user_id!==f);if(s){const a=s.user_id,d=document.querySelector(`[data-member-id="${a}"]`);if(d){const l=d.querySelector("p");if(l){let c="";const m=e.sender_id===f?"Anda: ":`${s.user.full_name.split(" ")[0]}: `;if(e.attachments&&e.attachments.length>0)if(e.content&&e.content.trim()!=="")c=m+e.content;else{const u=e.attachments[0].file_type;u.startsWith("image/")?c=m+"📷 Gambar":u.startsWith("video/")?c=m+"🎬 Video":c=m+`📎 ${e.attachments.length} file`}else c=m+e.content;l.textContent=c}}}}if(t){const s=document.getElementById(`unread-badge-${n}`),a=document.getElementById(`unread-count-${n}`);if(s&&a){let d=parseInt(a.textContent)||0;d++,a.textContent=d,s.style.display="block"}if(r&&r.type==="private"){const d=r.participants.find(l=>l.user_id!==f);if(d){const l=d.user_id,c=document.getElementById(`unread-badge-member-${l}`),m=document.getElementById(`unread-count-member-${l}`);if(c&&m){let u=parseInt(m.textContent)||0;u++,m.textContent=u,c.style.display="block"}else j()}}}const o=document.querySelector(`#chatListContainer div[data-conversation-id="${n}"]`);if(o){let s=o,a=null;for(;s.previousElementSibling;)if(s=s.previousElementSibling,s.classList.contains("px-6","pt-4","pb-2")){a=s;break}a?a.after(o):o.parentElement.prepend(o)}}let H=0;function ie(e){e.preventDefault(),e.stopPropagation(),H++,H===1&&(T.style.display="flex")}function ae(e){e.preventDefault(),e.stopPropagation()}function Ie(e){e.preventDefault(),e.stopPropagation(),H--,H===0&&(T.style.display="none")}function He(e){e.preventDefault(),e.stopPropagation(),H=0,T.style.display="none";const n=Array.from(e.dataTransfer.files).filter(i=>i.size>10*1024*1024?(alert(`File "${i.name}" terlalu besar. Maksimal 10MB per file.`),!1):!0);n.length>0&&(w=[...w,...n],U(),x.focus())}function je(e,t){Q();const n=`
            <div id="typing-indicator" class="flex items-center gap-2 text-gray-500 text-sm italic mb-4">
                <div class="flex gap-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
                <span>${t} sedang mengetik...</span>
            </div>
        `;k.insertAdjacentHTML("beforeend",n),Y()}function Q(){const e=document.getElementById("typing-indicator");e&&e.remove()}function De(e){e.user_id!==f&&(L.add(e.user_id),je(e.user_id,e.user_name),setTimeout(()=>{L.delete(e.user_id),L.size===0&&Q()},3e3))}function Ae(e){L.delete(e.user_id),L.size===0&&Q()}function Pe(e){e.reply_to&&(e.replyTo=e.reply_to);const t=document.getElementById(e.id);if(t){const n=Z(e);t.style.transition="opacity 0.3s ease",t.style.opacity="0",setTimeout(()=>{t.outerHTML=n;const i=document.getElementById(e.id);i&&(i.style.opacity="0",setTimeout(()=>{i.style.transition="opacity 0.3s ease",i.style.opacity="1"},50))},300)}_(e,!1)}function Fe(e){const t=e.message_id||e.id,n=e.sender_id,i=e.conversation_id;if(window.pendingDeletes&&window.pendingDeletes.has(t))return;const r=document.getElementById(t);if(!r){M(i);return}if(!r.parentNode){M(i);return}he(t,n===f),M(i)}function Re(e){if(e.reply_to?e.replyTo=e.reply_to:e.reply_to_message_id,e.sender_id===f){const n=document.getElementById(e.id);if(n){const i=n.querySelector(".read-status");i&&(i.innerHTML=ue(e));return}e.conversation_id===y&&se(e),_(e,!1);return}e.conversation_id===y?(se(e),ge(e.conversation_id),Se(),_(e,!1)):_(e,!0)}function We(){x.addEventListener("input",D),W.addEventListener("submit",be),E.addEventListener("click",function(){me(),setTimeout(()=>{E.style.display="none"},300)}),h.addEventListener("scroll",function(){const t=h.scrollTop,n=h.scrollHeight,i=h.clientHeight,o=n-t-i>100;E.style.display=o?"flex":"none"}),x.addEventListener("keypress",function(t){t.key==="Enter"&&!t.shiftKey&&(t.preventDefault(),W.dispatchEvent(new Event("submit",{cancelable:!0,bubbles:!0})))}),le.addEventListener("click",()=>{F.click()}),F.addEventListener("change",t=>{const i=Array.from(t.target.files).filter(r=>r.size>10*1024*1024?(alert(`File "${r.name}" terlalu besar. Maksimal 10MB per file.`),!1):!0);i.length>0&&(w=[...w,...i],U()),F.value=""});const e=document.querySelector("#searchInput");if(e){const t=Me(Le,300);e.addEventListener("input",n=>{t(n.target.value)}),e.addEventListener("blur",n=>{n.target.value.trim()||setTimeout(()=>j(),100)})}x.addEventListener("input",function(){y&&(N||(N=!0,window.Echo.private(`conversation.${y}`).whisper("typing",{user_id:f,user_name:"Anda"})),clearTimeout(te),te=setTimeout(()=>{N=!1,window.Echo.private(`conversation.${y}`).whisper("stop-typing",{user_id:f})},1e3))}),P.addEventListener("dragenter",ie),P.addEventListener("dragover",ae),P.addEventListener("dragleave",Ie),T.addEventListener("drop",He),T.addEventListener("dragenter",ie),T.addEventListener("dragover",ae)}function ve(){if(typeof Echo>"u"){console.error("❌ Laravel Echo not configured (Echo is undefined).");return}window.allConversations.forEach(e=>{const t=`conversation.${e.id}`;Echo.leave(t)}),window.allConversations.forEach(e=>{const t=`conversation.${e.id}`,n=Echo.private(t);n.subscribed(()=>{}),n.error(i=>{console.error(`❌ Error subscribing to ${t}:`,i)}),n.listen(".NewMessageSent",i=>{i.message?Re(i.message):console.error('❌ Event tidak punya property "message":',i)}),n.listen(".MessageDeleted",i=>{(i.message_id||i.id)&&Fe(i)}),n.listenForWhisper("typing",i=>{De(i)}),n.listenForWhisper("stop-typing",i=>{Ae(i)}),n.listen(".MessageEdited",i=>{i.message&&Pe(i.message)})})}function ye(){document.querySelectorAll("[data-conversation-id]").forEach(e=>{if(e.dataset.conversationId===y){e.classList.remove("hover:bg-gray-50"),e.classList.add("bg-blue-100","border-l-4","border-blue-500");const i=e.querySelector("h4"),r=e.querySelector("p");i&&(i.classList.remove("text-gray-800"),i.classList.add("text-blue-700")),r&&(r.classList.remove("text-gray-500"),r.classList.add("text-blue-600"))}else{e.classList.remove("bg-blue-100","border-l-4","border-blue-500"),e.classList.add("hover:bg-gray-50");const i=e.querySelector("h4"),r=e.querySelector("p");i&&(i.classList.remove("text-blue-700"),i.classList.add("text-gray-800")),r&&(r.classList.remove("text-blue-600"),r.classList.add("text-gray-500"))}})}window.selectConversation=function(e){K(e),ye()};
