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

            0%,
            100% {
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

    <div
        class="h-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex justify-center items-center py-4 px-4 sm:px-6">
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
                            <input type="password" name="current_password" placeholder="Kosongkan jika tidak ingin mengubah"
                                class="input-custom w-full border-2 border-gray-200 rounded-xl py-3.5 pl-12 pr-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                            <input type="password" name="new_password" placeholder="Minimal 6 karakter"
                                class="input-custom w-full border-2 border-gray-200 rounded-xl py-3.5 pl-12 pr-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

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
