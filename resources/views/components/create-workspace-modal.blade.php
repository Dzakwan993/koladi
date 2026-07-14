{{--
    Modal "Buat Workspace" — reusable di semua halaman.
    Cara trigger dari tombol manapun:
        @click="$dispatch('open-create-workspace', { type: 'Tim' })"
    (type bisa 'Tim' atau 'Proyek')
--}}
<div x-data="createWorkspaceModal()" @open-create-workspace.window="open($event.detail.type)">
    <div x-show="show" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md" @click.outside="show = false">
            <form @submit.prevent="createWorkspace">
                <div class="p-6">
                    <h2 class="text-center text-xl font-semibold text-gray-900 mb-4">Buat Workspace</h2>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Workspace</label>
                        <input type="text" x-model="data.name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama workspace...">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea x-model="data.description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan deskripsi workspace..."></textarea>
                    </div>

                    <div class="mb-6">
                        <p class="block text-sm font-medium text-gray-700 mb-3">Untuk apakah workspace ini?</p>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="radio" value="Tim" class="h-4 w-4 text-blue-600 border-gray-300" x-model="data.type">
                                <span class="ml-2 text-gray-700">Tim</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" value="Proyek" class="h-4 w-4 text-blue-600 border-gray-300" x-model="data.type">
                                <span class="ml-2 text-gray-700">Proyek</span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 flex justify-end">
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 mr-3"
                            @click="show = false">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                            :disabled="submitting">
                            <span x-show="!submitting">Buat</span>
                            <span x-show="submitting">Membuat...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('createWorkspaceModal', () => ({
        show: false,
        submitting: false,
        data: { name: '', description: '', type: 'Tim' },

        open(type = 'Tim') {
            this.data = { name: '', description: '', type };
            this.show = true;
        },

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        async createWorkspace() {
            this.submitting = true;
            try {
                const res = await fetch('/workspace', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.data)
                });
                const result = await res.json();

                if (result.success) {
                    this.show = false;
                    if (result.show_onboarding && result.workspace_name && typeof showOnboardingStep5Modal === 'function') {
                        setTimeout(() => showOnboardingStep5Modal(result.workspace_name), 500);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success', title: 'Berhasil!', text: result.message,
                            confirmButtonColor: '#2563EB'
                        }).then(() => location.reload());
                    } else {
                        location.reload();
                    }
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: result.message, confirmButtonColor: '#2563EB' });
                } else {
                    alert(result.message || 'Gagal membuat workspace');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat membuat workspace');
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>
@endonce