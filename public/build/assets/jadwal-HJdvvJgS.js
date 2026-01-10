function D(e){const i=document.getElementById("calendar");if(!i){console.error("Calendar element not found!");return}if(!e){console.error("Workspace ID is required!");return}let n=null,o=[],a={};console.log("🚀 Initializing calendar for workspace:",e),n=new FullCalendar.Calendar(i,{initialView:"dayGridMonth",locale:"id",selectable:!0,fixedWeekCount:!1,headerToolbar:{left:"prev",center:"title",right:"next"},events:function(t,r,l){const c=t.start.toISOString().split("T")[0]+" 00:00:00",f=t.end.toISOString().split("T")[0]+" 23:59:59",d=`/workspace/${e}/calendar/events?start=${encodeURIComponent(c)}&end=${encodeURIComponent(f)}`;console.log("📡 Fetching events from:",d),fetch(d,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}}).then(s=>{if(!s.ok)throw new Error(`Server error: ${s.status}`);return s.json()}).then(s=>{console.log("✅ Events received:",s),Array.isArray(s)||(console.warn("⚠️ Data is not an array:",s),s=[]),o=s,a=w(s),console.log("📊 Event count by date:",a),r(s),y(s,e),setTimeout(()=>h(a),150)}).catch(s=>{console.error("❌ Error loading events:",s),l(s),v(s.message)})},dateClick:function(t){document.querySelectorAll(".fc-day-selected").forEach(c=>c.classList.remove("fc-day-selected")),t.dayEl.classList.add("fc-day-selected");const r=t.dateStr,l=E(o,r);console.log("📅 Date clicked:",r,"Events:",l.length),y(l,e)},eventClick:function(t){t.jsEvent.preventDefault();const r=`/workspace/${e}/jadwal/${t.event.id}`;console.log("🔗 Navigating to:",r),window.location.href=r},datesSet:function(t){setTimeout(()=>h(a),150)}}),n.render(),console.log("✅ Calendar rendered")}function w(e){const i={};return e.forEach(n=>{try{const o=n.extendedProps?.start_date||n.start.split("T")[0],a=n.extendedProps?.end_date||n.end.split("T")[0];let t=new Date(o);const r=new Date(a);for(;t<=r;){const l=t.toISOString().split("T")[0];i[l]=(i[l]||0)+1,t.setDate(t.getDate()+1)}}catch(o){console.error("Error calculating event date:",o)}}),i}function h(e){document.querySelectorAll(".day-marker").forEach(n=>n.remove()),document.querySelectorAll(".fc-daygrid-day").forEach(n=>{const o=n.getAttribute("data-date");if(!o)return;const a=e[o];if(a&&a>0){const t=n.querySelector(".fc-daygrid-day-frame");if(t&&!t.querySelector(".day-marker")){const r=document.createElement("div");r.classList.add("day-marker"),r.textContent=a,t.style.position="relative",t.appendChild(r)}}})}function E(e,i){return e.filter(n=>{const o=n.extendedProps?.start_date||n.start.split("T")[0],a=n.extendedProps?.end_date||n.end.split("T")[0];return i>=o&&i<=a})}function y(e,i){const n=document.getElementById("scheduleList"),o=document.getElementById("loadingSchedule");if(o&&o.remove(),console.log("📋 Rendering",e.length,"events"),!e||e.length===0){n.innerHTML=`
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-calendar-times text-4xl mb-2"></i>
                <p>Tidak ada jadwal untuk ditampilkan</p>
            </div>
        `;return}const a={};e.forEach(r=>{try{const c=new Date(r.start).toLocaleDateString("id-ID",{weekday:"long",year:"numeric",month:"long",day:"numeric"});a[c]||(a[c]=[]),a[c].push(r)}catch(l){console.error("Error grouping event:",r,l)}});let t="";Object.keys(a).forEach((r,l)=>{l>0&&(t+='<hr class="border-t border-gray-300 my-4">'),a[r].forEach(c=>{t+=b(c,i,r)})}),n.innerHTML=t}function b(e,i,n){try{const o=new Date(e.start),a=new Date(e.end),t=e.extendedProps?.start_date||o.toISOString().split("T")[0],r=e.extendedProps?.end_date||a.toISOString().split("T")[0],l=t!==r;let c="";if(l){const u=o.toLocaleDateString("id-ID",{day:"numeric",month:"short",year:"numeric"})+" "+o.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"}),m=a.toLocaleDateString("id-ID",{day:"numeric",month:"short",year:"numeric"})+" "+a.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"});c=`${u} - ${m}`}else{const u=o.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"}),m=a.toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"});c=`${u} - ${m}`}const d=e.extendedProps?.is_online===!0?'<i class="fas fa-video text-gray-700 mr-2"></i>':"",s=e.extendedProps?.is_creator?"bg-[#bbcff9]":"bg-[#E9EFFD]",x=e.extendedProps?.creator_avatar||"/images/default-avatar.png",g=e.extendedProps?.creator_name||"Unknown",p=e.extendedProps?.comments_count||0;return`
            <a href="/workspace/${i}/jadwal/${e.id}"
                class="${s} rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item mb-3">

                <div class="flex flex-col items-start w-[140px] date-section">
                    <span class="font-semibold text-[14px] mx-auto">${n.split(",")[0]}</span>
                    <span class="font-semibold text-[14px]">${n.split(",")[1]?.trim()||""}</span>
                </div>

                <div class="flex flex-col flex-1 px-4 content-section">
                    <div class="flex items-center gap-2 mb-2">
                        ${d}
                        <span class="font-semibold text-[#090909] text-base">${e.title||"Untitled"}</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <img src="${x}"
                             alt="${g}"
                             title="${g}"
                             class="w-6 h-6 rounded-full border-2 border-white object-cover">
                        <span class="text-sm font-semibold text-[#102A63]">${c}</span>
                    </div>
                </div>

                <!-- ✅ Badge Komentar (Buletan Kuning) - Hanya muncul jika ada komentar -->
                ${p>0?`
                <div class="badge-section">
                    <span class="bg-yellow-400 text-gray-700 text-xs font-bold rounded-full w-7 h-7 flex items-center justify-center shadow-sm">
                        ${p}
                    </span>
                </div>
                `:""}
            </a>
        `}catch(o){return console.error("Error rendering schedule item:",e,o),""}}function v(e){const i=document.getElementById("scheduleList");i&&(i.innerHTML=`
            <div class="text-center text-red-500 py-8">
                <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                <p class="font-semibold">Gagal memuat jadwal</p>
                <p class="text-sm mt-2">${e}</p>
            </div>
        `)}window.initJadwalCalendar=D;
