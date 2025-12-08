<!-- Modal Undang Orang -->
<div id="inviteModal" class="fixed inset-0 z-50 hidden font-[Inter,sans-serif]" onclick="closeInviteModal(event)">
    <!-- Modal Container -->
    <div id="inviteModalContent"
        class="absolute bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.25)] border border-gray-100"
        style="width: 90%; max-width: 350px; left: 50%; transform: translateX(-50%); top: 100px;"
        onclick="event.stopPropagation()">

        <!-- Content -->
        <div class="p-4 sm:p-6">
            <!-- Title -->
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4 text-center">Undang Orang</h2>

            <!-- Description dan Input Area -->
            <div class="bg-gray-100 rounded-lg p-3 sm:p-4 mb-4 sm:mb-5">
                <p class="text-gray-500 text-xs sm:text-sm mb-2 sm:mb-3">
                    Silahkan tuliskan email yang ingin diundang. 1 baris per email ya...
                </p>

                <!-- Textarea -->
                <textarea id="emailInput" rows="5"
                    class="w-full bg-white border border-gray-200 rounded-lg p-2.5 sm:p-3 text-xs sm:text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="email1@example.com&#10;email2@example.com&#10;email3@example.com"></textarea>
            </div>

            <!-- Button Undang -->
            <button onclick="sendInvite()"
                class="w-full bg-[#102A63] hover:bg-blue-900 text-white py-2.5 sm:py-3 rounded-lg text-sm sm:text-base font-semibold transition">
                Undang
            </button>
        </div>

    </div>
</div>

<script>
    function closeInviteModal(event) {
        if (event && event.target.id !== 'inviteModal') return;
        document.getElementById('inviteModal').classList.add('hidden');
    }

    function openInviteModal(event) {
        const modal = document.getElementById('inviteModal');
        const modalContent = document.getElementById('inviteModalContent');
        const button = event.currentTarget;
        const rect = button.getBoundingClientRect();

        const modalWidth = 350;
        const rightSpace = window.innerWidth - rect.right;

        modalContent.style.top = (rect.bottom + 10) + 'px';
        if (rightSpace >= modalWidth) {
            modalContent.style.left = (rect.right - modalWidth) + 'px';
        } else {
            modalContent.style.left = (window.innerWidth - modalWidth - 16) + 'px';
        }
        modalContent.style.transform = 'none';

        modal.classList.remove('hidden');
    }

    function sendInvite() {
        const emails = document.getElementById('emailInput').value.trim();
        if (!emails) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Silakan masukkan minimal satu email.',
                confirmButtonColor: '#225AD6',
            });
            return;
        }

        const emailList = emails.split('\n').map(e => e.trim()).filter(e => e);

        // 🔄 Tampilkan animasi loading sebelum kirim
        Swal.fire({
            title: 'Mengirim undangan...',
            text: 'Harap tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        Promise.all(emailList.map(email =>
                fetch("{{ route('invite.send') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        email_target: email
                    })
                }).then(res => res.ok ? res.text() : Promise.reject(res))
            ))
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Undangan berhasil dikirim 🎉',
                    confirmButtonColor: '#225AD6',
                    timer: 2000,
                    showConfirmButton: false
                });
                document.getElementById('emailInput').value = "";
                closeInviteModal();
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat mengirim undangan.',
                    confirmButtonColor: '#E26767',
                });
            });
    }
</script>
