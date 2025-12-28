@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <style>
        .input-custom {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            background-color: #F9FAFB !important;
            transition: all 0.3s ease;
        }

        .input-custom:focus {
            background-color: white !important;
            transform: translateY(-1px);
        }

        .input-custom::placeholder {
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            color: #9CA3AF;
        }

        .avatar-upload {
            position: relative;
            cursor: pointer;
        }

        .avatar-upload input[type="file"] {
            display: none;
        }

        .avatar-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
            border-radius: 9999px;
            padding: 10px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease;
            border: 3px solid white;
        }

        .avatar-overlay:hover {
            background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.5);
        }

        .avatar-container {
            position: relative;
            transition: all 0.3s ease;
        }

        .avatar-container:hover .avatar-img {
            transform: scale(1.05);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .avatar-img {
            transition: all 0.3s ease;
            border: 4px solid white;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .form-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .preview-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        .label-custom {
            color: #1E293B;
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.01em;
        }

        @media (max-width: 640px) {
            .form-card {
                border-radius: 16px;
                padding: 1.25rem !important;
            }

            .input-custom {
                font-size: 14px;
            }
        }
    </style>

    <div class="h-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex justify-center items-center py-4 px-4 sm:px-6">
        <div class="w-full max-w-md lg:max-w-lg">
            <div class="form-card p-5 sm:p-6 md:p-8">

                <!-- Avatar + Email Section -->
                <div class="flex flex-col items-center mb-6">
                    <div class="avatar-container mb-3">
                        <div class="relative">
                            <img id="avatarPreview" src="{{ $avatar }}" alt="{{ $user->name }}"
                                class="avatar-img w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-full object-cover">

                            <span id="previewBadge" class="preview-badge hidden">Preview</span>

                            <div class="avatar-overlay">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>

                        <input type="file" id="avatarInput" name="avatar" accept="image/*" class="hidden">
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 text-center break-all px-2">
                        {{ Auth::user()->email }}
                    </p>
                </div>

                <!-- Form Profil -->
                <form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-4 sm:space-y-5">
                    @csrf
                    @method('PUT')

                    <!-- Hidden input for avatar -->
                    <input type="file" id="avatarFileInput" name="avatar" class="hidden">

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="label-custom block mb-2">Nama Lengkap</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" name="full_name" value="{{ old('full_name', Auth::user()->full_name) }}"
                                placeholder="Masukkan nama lengkap" required
                                class="input-custom w-full border-2 border-gray-200 rounded-xl py-3.5 pl-12 pr-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    @if (!Auth::user()->google_id)
                        <!-- Kata Sandi Lama -->
                        <div>
                            <label class="label-custom block mb-2">Kata Sandi Lama <span
                                    class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="currentPassword" type="password" name="current_password"
                                    placeholder="Kosongkan jika tidak ingin mengubah"
                                    class="input-custom w-full border-2 border-gray-200 rounded-xl py-3.5 pl-12 pr-12 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                                <!-- Toggle Password Button -->
                                <button type="button" id="toggleCurrentPassword"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <!-- Eye Open -->
                                    <svg id="eyeOpenCurrent" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <!-- Eye Closed -->
                                    <svg id="eyeClosedCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.957 9.957 0 012.125-3.368m2.59-2.591A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-2.318 3.74M15 12a3 3 0 00-4.243-4.243M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Kata Sandi Baru -->
                        <div>
                            <label class="label-custom block mb-2">Kata Sandi Baru <span
                                    class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <input id="newPassword" type="password" name="new_password" placeholder="Minimal 6 karakter"
                                    class="input-custom w-full border-2 border-gray-200 rounded-xl py-3.5 pl-12 pr-12 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                                <!-- Toggle Password Button -->
                                <button type="button" id="toggleNewPassword"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <!-- Eye Open -->
                                    <svg id="eyeOpenNew" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <!-- Eye Closed -->
                                    <svg id="eyeClosedNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.957 9.957 0 012.125-3.368m2.59-2.591A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-2.318 3.74M15 12a3 3 0 00-4.243-4.243M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Info untuk user Google -->
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-blue-900 mb-1">Login dengan Google</p>
                                    <p class="text-xs text-blue-700">Anda login menggunakan akun Google. Kata sandi dikelola
                                        oleh Google dan tidak dapat diubah di sini.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Buttons -->
                    <div class="flex flex-col-reverse sm:flex-row justify-center sm:justify-end gap-3 pt-4 sm:pt-6">
                        <a href="{{ url()->previous() }}"
                            class="btn-secondary w-full sm:w-auto text-center border-2 border-blue-600 text-blue-600 hover:bg-blue-50 px-6 sm:px-8 py-3 rounded-xl font-semibold text-sm sm:text-base">
                            Batal
                        </a>
                        <button type="button" id="btnSave"
                            class="btn-primary w-full sm:w-auto text-white px-6 sm:px-8 py-3 rounded-xl font-semibold text-sm sm:text-base">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Avatar preview functionality
        const avatarContainer = document.querySelector('.avatar-overlay');
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarFileInput = document.getElementById('avatarFileInput');
        const previewBadge = document.getElementById('previewBadge');

        avatarContainer.addEventListener('click', function() {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                if (!file.type.match('image.*')) {
                    Swal.fire('Oops!', 'Harap pilih file gambar (JPEG, PNG, JPG)', 'error');
                    return;
                }

                if (file.size > 2048000) {
                    Swal.fire('Ukuran Terlalu Besar!', 'Ukuran file maksimal 2MB', 'error');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    avatarPreview.src = event.target.result;
                    previewBadge.classList.remove('hidden');
                };
                reader.readAsDataURL(file);

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                avatarFileInput.files = dataTransfer.files;
            }
        });

        // Toggle Password Visibility - Current Password
        const currentPasswordInput = document.getElementById('currentPassword');
        const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
        const eyeOpenCurrent = document.getElementById('eyeOpenCurrent');
        const eyeClosedCurrent = document.getElementById('eyeClosedCurrent');

        if (toggleCurrentPassword && currentPasswordInput) {
            toggleCurrentPassword.addEventListener('click', function() {
                const isPassword = currentPasswordInput.type === 'password';
                currentPasswordInput.type = isPassword ? 'text' : 'password';
                eyeOpenCurrent.classList.toggle('hidden', !isPassword);
                eyeClosedCurrent.classList.toggle('hidden', isPassword);
            });
        }

        // Toggle Password Visibility - New Password
        const newPasswordInput = document.getElementById('newPassword');
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const eyeOpenNew = document.getElementById('eyeOpenNew');
        const eyeClosedNew = document.getElementById('eyeClosedNew');

        if (toggleNewPassword && newPasswordInput) {
            toggleNewPassword.addEventListener('click', function() {
                const isPassword = newPasswordInput.type === 'password';
                newPasswordInput.type = isPassword ? 'text' : 'password';
                eyeOpenNew.classList.toggle('hidden', !isPassword);
                eyeClosedNew.classList.toggle('hidden', isPassword);
            });
        }

        // Konfirmasi sebelum simpan
        document.getElementById('btnSave').addEventListener('click', function(e) {
            Swal.fire({
                title: 'Simpan perubahan?',
                text: 'Perubahan profil akan disimpan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#6B7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('profileForm').submit();
                }
            });
        });
    </script>

    <!-- SweetAlert for Session Messages -->
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#2563EB',
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#EF4444',
            });
        </script>
    @endif
@endsection
