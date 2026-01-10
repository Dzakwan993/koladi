document.addEventListener("DOMContentLoaded",function(){let I=document.getElementById("calendar"),T=null,c=[],D={};console.log("🚀 Initializing Dashboard Calendar...");function L(t){return t.trim().split(" ")[0]}T=new FullCalendar.Calendar(I,{initialView:"dayGridMonth",locale:"id",selectable:!0,fixedWeekCount:!1,height:"100%",dayHeaderFormat:{weekday:"short"},headerToolbar:{left:"prev",center:"title",right:"next"},displayEventTime:!1,displayEventEnd:!1,eventDisplay:"none",buttonText:{today:"Hari Ini"},dayHeaderContent:function(t){return["MIN","SEN","SEL","RAB","KAM","JUM","SAB"][t.dow]},events:async function(t,a,o){try{const r=t.start.toISOString().split("T")[0]+" 00:00:00",e=t.end.toISOString().split("T")[0]+" 23:59:59",n=`/dashboard/all-events?start=${encodeURIComponent(r)}&end=${encodeURIComponent(e)}`;console.log("📡 Fetching events from:",n);const s=await fetch(n,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}});if(!s.ok)throw new Error(`Server error: ${s.status}`);const i=await s.json();c=Array.isArray(i)?i:[],console.log("✅ Loaded events:",c.length,c),D=P(c),a(c);const l=new Date().toISOString().split("T")[0];k(l),setTimeout(()=>{$(D),E()},150)}catch(r){console.error("❌ Error loading events:",r),o(r),M(r.message)}},dateClick:function(t){document.querySelectorAll(".fc-day-selected").forEach(a=>a.classList.remove("fc-day-selected")),t.dayEl.classList.add("fc-day-selected"),k(t.dateStr)},eventClick:function(t){t.jsEvent.preventDefault();const a=t.event;a.extendedProps?.schedule_type==="workspace"&&a.extendedProps?.workspace_id?window.location.href=`/workspace/${a.extendedProps.workspace_id}/jadwal/${a.id}`:window.location.href=`/jadwal-umum/${a.id}`},datesSet:function(t){setTimeout(()=>{$(D),E()},150)}}),T.render();function P(t){const a={};return t.forEach(o=>{try{const r=o.extendedProps?.start_date||o.start.split("T")[0],e=o.extendedProps?.end_date||o.end.split("T")[0];let n=new Date(r);const s=new Date(e);for(;n<=s;){const i=n.toISOString().split("T")[0];a[i]=(a[i]||0)+1,n.setDate(n.getDate()+1)}}catch(r){console.error("Error calculating event date:",r)}}),a}function $(t){document.querySelectorAll(".day-marker").forEach(o=>o.remove()),document.querySelectorAll(".fc-daygrid-day").forEach(o=>{const r=o.getAttribute("data-date");if(!r)return;const e=t[r];if(e&&e>0){const n=o.querySelector(".fc-daygrid-day-frame");if(n&&!n.querySelector(".day-marker")){const s=document.createElement("div");s.classList.add("day-marker"),s.textContent=e,n.style.position="relative",n.appendChild(s)}}})}function E(){document.querySelectorAll(".fc-daygrid-day").forEach(a=>{const o=a.getAttribute("data-date");if(!o)return;const r=c.filter(e=>{const n=e.extendedProps?.start_date||e.start.split("T")[0],s=e.extendedProps?.end_date||e.end.split("T")[0];return o>=n&&o<=s});r.length!==0&&(a.addEventListener("mouseenter",function(e){const n=document.querySelector(".calendar-tooltip");n&&n.remove();const s=document.createElement("div");s.classList.add("calendar-tooltip");let i=`
                    <div style="font-weight: 800; font-size: 13px; margin-bottom: 10px; color: #1E1E1E; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">
                        ${new Date(o).toLocaleDateString("id-ID",{day:"numeric",month:"long",year:"numeric"})}
                    </div>
                `;r.forEach((d,g)=>{const x=new Date(d.start),y=new Date(d.end),h=d.extendedProps?.start_date||x.toISOString().split("T")[0],v=d.extendedProps?.end_date||y.toISOString().split("T")[0],w=h!==v;let m="";if(w)m=`${x.toLocaleDateString("id-ID",{day:"numeric",month:"short"})} - ${y.toLocaleDateString("id-ID",{day:"numeric",month:"short"})}`;else{const O=x.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"}),H=y.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"});m=`${O} - ${H}`}const b=d.extendedProps?.is_online===!0||d.extendedProps?.is_online===1,A=(d.extendedProps?.schedule_type||"company")==="company"?'<span style="background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600;">UMUM</span>':'<span style="background: #e9d5ff; color: #7e22ce; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600;">WORKSPACE</span>',j=b?'<i class="fas fa-video" style="color: #3b82f6; margin-right: 4px;"></i>':"";i+=`
                        <div style="padding: 8px 0; ${g<r.length-1?"border-bottom: 1px solid #f3f4f6;":""}">
                            <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                ${j}
                                <span style="flex: 1;">${d.title||"Untitled"}</span>
                                ${A}
                            </div>
                            <div style="font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                                <i class="far fa-clock" style="color: #9ca3af;"></i>
                                ${m}
                            </div>
                            ${d.extendedProps?.location?`
                                <div style="font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                    <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i>
                                    ${d.extendedProps.location}
                                </div>
                            `:""}
                        </div>
                    `}),s.innerHTML=i,document.body.appendChild(s);const l=a.getBoundingClientRect(),p=s.getBoundingClientRect();let u=l.left+l.width/2-p.width/2,f=l.bottom+10;u<10&&(u=10),u+p.width>window.innerWidth-10&&(u=window.innerWidth-p.width-10),f+p.height>window.innerHeight-10&&(f=l.top-p.height-10),s.style.left=`${u}px`,s.style.top=`${f}px`}),a.addEventListener("mouseleave",function(){const e=document.querySelector(".calendar-tooltip");e&&setTimeout(()=>e.remove(),100)}))})}function k(t){const a=c.filter(n=>{const s=n.extendedProps?.start_date||n.start.split("T")[0],i=n.extendedProps?.end_date||n.end.split("T")[0];return t>=s&&t<=i});console.log("📅 Schedules for",t,":",a);const o=new Date(t),r=new Date().toISOString().split("T")[0],e=document.getElementById("scheduleTitle");t===r?e.innerHTML='<i class="fas fa-list-check text-blue-600 mr-2"></i>Jadwal Hari Ini':e.innerHTML=`<i class="fas fa-calendar-day text-purple-600 mr-2"></i>${o.toLocaleDateString("id-ID",{weekday:"long",day:"numeric",month:"long",year:"numeric"})}`,_(a)}function _(t,a){const o=document.querySelector(".schedule-cards-container");if(!o)return;if(!t||t.length===0){o.innerHTML=`
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-calendar-times text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 mb-1">Tidak Ada Jadwal</p>
                    <p class="text-xs text-gray-500">Belum ada jadwal untuk tanggal ini</p>
                </div>
            `;return}let r="";t.forEach(e=>{try{const n=new Date(e.start),s=new Date(e.end),i=C(n,s,e),l=e.extendedProps?.is_creator?"from-[#bbcff9] to-[#a8bef5]":"from-[#E9EFFD] to-[#dce6fc]",p=e.extendedProps?.creator_avatar||"/images/default-avatar.png",u=e.extendedProps?.creator_name||"User",f=L(u),d=e.extendedProps?.is_online===!0||e.extendedProps?.is_online===1,g=e.extendedProps?.meeting_link,x=g&&g.trim()!==""&&g!=="null",y=d&&x?'<i class="fas fa-video text-blue-600 mr-1.5"></i>':"",h=e.extendedProps?.comments_count||0,v=e.extendedProps?.schedule_type||"company",w=e.extendedProps?.workspace_id;let m=`/jadwal-umum/${e.id}`,b="Jadwal Umum",S="bg-blue-100 text-blue-700";v==="workspace"&&w&&(m=`/workspace/${w}/jadwal/${e.id}`,b="Ruang Kerja",S="bg-purple-100 text-purple-700"),r+=`
                    <a href="${m}"
                        class="group bg-gradient-to-br ${l} rounded-xl shadow-md p-4 hover:shadow-2xl transition-all duration-300 cursor-pointer block border border-blue-100 hover:border-blue-400">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex items-start space-x-3 flex-1 min-w-0">
                                <div class="relative">
                                    <img src="${p}"
                                         alt="${f}"
                                         class="rounded-full w-11 h-11 object-cover border-3 border-white shadow-lg bg-gray-100 flex-shrink-0 ring-2 ring-blue-200">
                                    ${y?'<div class="absolute -bottom-1 -right-1 bg-blue-600 rounded-full w-5 h-5 flex items-center justify-center shadow-md"><i class="fas fa-video text-white text-[10px]"></i></div>':""}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start gap-2 mb-2">
                                        <span class="font-bold text-[#090909] text-sm leading-tight flex-1 group-hover:text-blue-700 transition-colors line-clamp-2">
                                            ${e.title||"Untitled"}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full ${S} font-medium">
                                            ${b}
                                        </span>
                                    </div>

                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                            <i class="far fa-clock text-blue-500"></i>
                                            <span class="font-medium">${i}</span>
                                        </div>
                                        ${e.extendedProps?.location?`
                                            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                <i class="fas fa-map-marker-alt text-red-500"></i>
                                                <span class="truncate">${e.extendedProps.location}</span>
                                            </div>
                                        `:""}
                                    </div>
                                </div>
                            </div>

                            ${h>0?`
                                <div class="flex-shrink-0">
                                    <div class="relative">
                                        <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 text-gray-800 text-xs font-bold rounded-lg w-8 h-8 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                            ${h}
                                        </div>
                                        <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                            `:""}
                        </div>
                    </a>
                `}catch(n){console.error("Error rendering schedule card:",n)}}),o.innerHTML=r}function C(t,a,o){const r=o.extendedProps?.start_date||t.toISOString().split("T")[0],e=o.extendedProps?.end_date||a.toISOString().split("T")[0];if(r!==e)return`${t.toLocaleDateString("id-ID",{day:"numeric",month:"short"})} - ${a.toLocaleDateString("id-ID",{day:"numeric",month:"short",year:"numeric"})}`;{const s=t.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"}),i=a.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"});return`${s} - ${i} WIB`}}function M(t){const a=document.querySelector(".schedule-cards-container");a&&(a.innerHTML=`
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 mb-1">Gagal Memuat Jadwal</p>
                    <p class="text-xs text-gray-500">${t}</p>
                </div>
            `)}});
