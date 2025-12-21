{{-- resources/views/components/onboarding-member.blade.php --}}

{{-- 🎯 ONBOARDING UNTUK MEMBER (Simplified) --}}
{{-- ✅ Z-INDEX = 10500 (lebih tinggi dari onboarding full yang pakai 10001) --}}
<div id="onboarding-member-welcome" class="hidden fixed inset-0 z-[10500] flex items-center justify-center">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    <!-- Welcome Modal -->
    {{-- ✅ Z-INDEX = 10501 (di atas backdrop) --}}
    <div class="relative z-[10501] bg-white rounded-3xl shadow-2xl w-[600px] max-w-[90vw] mx-4 transform transition-all duration-500" id="member-welcome-modal">

        <!-- Celebration Effects -->
        <div class="absolute -top-10 -left-10 w-24 h-24 bg-blue-400 rounded-full blur-3xl opacity-50 animate-pulse"></div>
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-purple-400 rounded-full blur-3xl opacity-50 animate-pulse"></div>

        <!-- Content -->
        <div class="relative p-8">
            <!-- Icon Header -->
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-xl">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Welcome Message -->
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-3">
                Selamat Datang di {{ $company->name }}! 🎉
            </h2>

            <p class="text-center text-gray-600 mb-8 text-base">
                Anda telah bergabung sebagai <strong class="text-blue-600">Member</strong>.<br>
                Mari kenali fitur yang bisa Anda gunakan!
            </p>

            <!-- Features Grid (4 boxes) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <!-- Feature 1: Lihat Pengumuman -->
                <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm mb-1">Lihat Pengumuman</h3>
                            <p class="text-xs text-gray-600">Update penting dari tim</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 2: Chat & Kolaborasi -->
                <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm mb-1">Chat & Kolaborasi</h3>
                            <p class="text-xs text-gray-600">Komunikasi dengan tim</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 3: Lihat Tugas -->
                <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm mb-1">Lihat Tugas</h3>
                            <p class="text-xs text-gray-600">Tugas yang diberikan</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 4: Lihat Jadwal -->
                <div class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm mb-1">Lihat Jadwal</h3>
                            <p class="text-xs text-gray-600">Event & meeting</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700 leading-relaxed">
                            <strong class="text-blue-700">Catatan:</strong> Beberapa fitur seperti menambah anggota, membuat workspace, atau mengelola perusahaan hanya bisa diakses oleh Admin/Manager.
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <button onclick="completeMemberOnboarding()"
                class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3.5 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mengerti, Mari Mulai!
            </button>

            <!-- Help Text -->
            <p class="text-center text-xs text-gray-500 mt-4">
                Jika ada pertanyaan, hubungi Admin Anda 💬
            </p>
        </div>
    </div>
</div>

<script>
function completeMemberOnboarding() {
    const modal = document.getElementById('onboarding-member-welcome');

    // Hide modal with animation
    if (modal) {
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Mark as complete
    fetch('{{ route("complete-onboarding") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Member onboarding completed:', data);

        // Show success toast if Swal is available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Selamat datang! Nikmati fitur yang tersedia 🎉',
                showConfirmButton: false,
                timer: 3000
            });
        }
    })
    .catch(err => {
        console.error('❌ Error:', err);
    });
}

// Auto-show when page loads
document.addEventListener('DOMContentLoaded', function() {
    const shouldShow = {{ isset($showOnboarding) && $showOnboarding && $onboardingType === 'member' ? 'true' : 'false' }};

    if (shouldShow) {
        setTimeout(() => {
            const modal = document.getElementById('onboarding-member-welcome');
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                }, 100);
            }
        }, 500);
    }
});
</script>

<style>
#onboarding-member-welcome {
    transition: opacity 0.3s ease;
}
</style>
