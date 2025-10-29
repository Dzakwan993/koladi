<!-- Modal Hapus (Universal untuk Member & Invitation) -->
<div id="deleteModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition font-[Inter,sans-serif]"
    onclick="closeDeleteModal(event)">
    <div id="deleteModalContent"
        class="bg-white rounded-xl shadow-lg border border-gray-100 w-[90%] max-w-sm p-6 animate-scaleIn relative"
        onclick="event.stopPropagation()">

        <!-- Tombol Close -->
        <button onclick="closeDeleteModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Isi Modal -->
        <div class="text-center mt-2">
            <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Delete"
                class="w-10 h-10 mx-auto mb-3 opacity-70" />
            <h2 id="deleteModalTitle" class="text-lg font-bold text-gray-900 mb-2">Hapus Anggota?</h2>
            <p id="deleteModalDescription" class="text-gray-600 text-sm mb-5 leading-relaxed">
                Anggota ini tidak akan punya akses lagi ke perusahaan ini.
            </p>

            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full bg-[#E26767] hover:bg-red-500 text-white py-3 rounded-lg text-base font-semibold transition flex items-center justify-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus"
                        class="w-5 h-5 brightness-0 invert" />
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes scaleIn {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .animate-scaleIn {
        animation: scaleIn 0.2s ease-out forwards;
    }
</style>
<script>
    function openDeleteModal(event, id, type) {
        event.preventDefault();
        event.stopPropagation();

        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const title = document.getElementById('deleteModalTitle');
        const description = document.getElementById('deleteModalDescription');

        if (type === 'member') {
            form.action = `/members/${id}/delete`;
            title.textContent = 'Hapus Anggota?';
            description.textContent = 'Anggota ini tidak akan punya akses lagi ke perusahaan ini.';
        } else if (type === 'invite') {
            form.action = `/invitation/${id}/delete`;
            title.textContent = 'Hapus Undangan?';
            description.textContent = 'Undangan ini akan dibatalkan dan tidak bisa digunakan lagi.';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Saat tombol submit diklik → tampilkan loading
        form.addEventListener('submit', function(e) {
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }, {
            once: true
        });
    }

    function closeDeleteModal(event) {
        if (event && event.target.id !== 'deleteModal' && event.target.id !== 'deleteModalContent') {
            if (!event.target.closest('#deleteModalContent')) {
                const modal = document.getElementById('deleteModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            return;
        }

        if (!event || event.target.id === 'deleteModal') {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>

@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#3085d6',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#d33',
        });
    </script>
@endif
