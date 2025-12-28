@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
    <div class="min-h-screen bg-gray-50 py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            {{-- ❌ Alert jika paket expired --}}
            @if ($company->status === 'expired' || ($trialStatus === 'expired' && !$hasActiveSubscription))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm animate-pulse">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-bold text-red-800 mb-1">⚠️ Paket Berakhir</h3>
                                <p class="text-sm text-red-700 mb-2">
                                    Paket perusahaan <span class="font-bold">{{ $company->name }}</span> telah berakhir.
                                </p>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <button onclick="openModal()"
                                        class="bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700 font-semibold">
                                        💳 Pilih Paket
                                    </button>
                                    <span class="text-red-600">atau</span>
                                    <a href="{{ route('company.switch', $companies->where('status', '!=', 'expired')->first()->id ?? '#') }}"
                                        class="bg-white text-red-600 border-2 border-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 font-semibold">
                                        🔄 Pindah ke Perusahaan Aktif
                                    </a>
                                </div>
                            </div>
                            <span
                                class="ml-auto flex-shrink-0 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-600 text-white">
                                Berakhir
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6">
                <!-- Kolom Kiri: Daftar Perusahaan (Mobile: Full Width, Desktop: 3 Cols) -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl shadow-md p-4 lg:p-5 border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Perusahaan
                            </h3>
                            <a href="{{ route('buat-perusahaan.create') }}"
                                class="text-blue-600 hover:text-blue-800 transition-colors" title="Buat Perusahaan Baru">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </a>
                        </div>

                        <div class="space-y-2 mb-3">
                            @foreach ($companies as $comp)
                                @php
                                    // 🔥 CEK STATUS USER DI COMPANY INI
                                    $userCompanyStatus =
                                        $comp->users()->where('users.id', Auth::id())->first()?->pivot
                                            ?->status_active ?? true;

                                    // Check status subscription untuk company
                                    $compTrialActive = false;
                                    if ($comp->status === 'trial' && $comp->trial_end) {
                                        $compTrialActive = \Carbon\Carbon::parse($comp->trial_end)->isFuture();
                                    }
                                    $compSubActive =
                                        $comp->subscription &&
                                        $comp->subscription->status === 'active' &&
                                        \Carbon\Carbon::parse($comp->subscription->end_date)->isFuture();

                                    // Company expired jika tidak trial aktif dan tidak ada subscription aktif
                                    $isCompanyExpired = !$compTrialActive && !$compSubActive;

                                    // User nonaktif di company ini
                                    $isUserInactive = !$userCompanyStatus;
                                @endphp

                                {{-- Jika user nonaktif, tampilkan dengan style berbeda dan disabled --}}
                                @if ($isUserInactive)
                                    <div
                                        class="flex items-center gap-3 p-3 rounded-lg border-2 bg-gray-100 border-gray-300 opacity-60 cursor-not-allowed">
                                        <div
                                            class="w-10 h-10 bg-gray-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <span
                                                class="text-white font-bold text-lg">{{ substr($comp->name, 0, 1) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-700 truncate">
                                                {{ $comp->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                </svg>
                                                {{ $comp->users->count() }} Anggota
                                            </p>
                                        </div>
                                        <span class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full font-semibold">
                                            ❌ Nonaktif
                                        </span>
                                    </div>
                                @else
                                    {{-- User aktif, tampilkan normal --}}
                                    <a href="{{ route('company.switch', $comp->id) }}"
                                        class="flex items-center gap-3 p-3 rounded-lg border-2 transition-all 
                {{ $comp->id === $company->id
                    ? 'bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200'
                    : ($isCompanyExpired
                        ? 'bg-red-50 border-red-200 hover:border-red-300'
                        : 'bg-gray-50 border-gray-200 hover:border-blue-300 hover:bg-blue-50') }}">

                                        <div
                                            class="w-10 h-10 {{ $comp->id === $company->id ? 'bg-blue-600' : ($isCompanyExpired ? 'bg-red-400' : 'bg-gray-400') }} rounded-lg flex items-center justify-center flex-shrink-0">
                                            <span
                                                class="text-white font-bold text-lg">{{ substr($comp->name, 0, 1) }}</span>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate flex items-center gap-1">
                                                {{ $comp->name }}
                                                @if ($comp->id === $company->id)
                                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-600 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                </svg>
                                                {{ $comp->users->count() }} Anggota
                                            </p>
                                        </div>

                                        <div class="flex-shrink-0">
                                            @if ($compTrialActive)
                                                <span
                                                    class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-semibold">Trial</span>
                                            @elseif($compSubActive)
                                                <span
                                                    class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-semibold">Aktif</span>
                                            @else
                                                <span
                                                    class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-semibold">Expired</span>
                                            @endif
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Button Buat Perusahaan Baru --}}
                        <a href="{{ route('buat-perusahaan.create') }}"
                            class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-bold py-3 px-4 rounded-lg hover:from-green-600 hover:to-green-700 transition-all shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Buat Perusahaan Baru
                        </a>
                    </div>
                </div>

                <!-- Kolom Kanan (Mobile: Full Width, Desktop: 9 Cols) -->
                <div class="lg:col-span-9 space-y-4 lg:space-y-6">
                    <!-- Card Paket Langganan dan Pemakaian -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Paket Langganan -->
                        <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    Paket Langganan
                                </h3>
                                @if ($trialStatus === 'active')
                                    <span
                                        class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap shadow-sm">
                                        Trial Aktif
                                    </span>
                                @elseif($hasActiveSubscription)
                                    <span
                                        class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap shadow-sm">
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap shadow-sm">
                                        Berakhir
                                    </span>
                                @endif
                            </div>

                            @if ($trialStatus === 'active')
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">Trial Gratis 7 Hari</h2>
                                <p class="text-xs text-gray-600 mb-4">
                                    Berakhir {{ \Carbon\Carbon::parse($company->trial_end)->format('d M Y') }}
                                    <span class="font-semibold text-orange-600">({{ $company->trial_days_remaining }} hari
                                        tersisa)</span>
                                </p>
                            @elseif($hasActiveSubscription)
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                                    {{ $company->subscription->plan->plan_name }}</h2>
                                <p class="text-xs text-gray-600 mb-4">
                                    Berlaku hingga
                                    {{ \Carbon\Carbon::parse($company->subscription->end_date)->format('d M Y') }}
                                </p>
                            @else
                                <h2 class="text-xl sm:text-2xl font-bold text-red-600 mb-1">Tidak Ada Paket Aktif</h2>
                                <p class="text-xs text-gray-600 mb-4">Silakan pilih paket untuk melanjutkan</p>
                            @endif

                            <button onclick="openModal()"
                                class="w-full bg-gradient-to-r from-[#5FD0AB] to-[#4dbf9a] hover:from-[#4dbf9a] hover:to-[#3aae87] text-white text-sm font-bold py-3 px-5 rounded-lg transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                {{ $hasActiveSubscription ? '🔄 Ubah Paket' : '🚀 Pilih Paket' }}
                            </button>
                        </div>

                        <!-- Pemakaian Anggota dengan Toggle Status -->
                        <div class="bg-white rounded-xl shadow-md p-5 border border-gray-200">
                            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                </svg>
                                Pemakaian Anggota
                            </h3>

                            {{-- 🔥 PERUBAHAN: Gunakan active_users_count --}}
                            <div class="flex items-end gap-2 mb-3">
                                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900">
                                    {{ $company->active_users_count }}</h2>
                                <span
                                    class="text-2xl sm:text-3xl font-semibold text-gray-400 mb-1">/{{ $company->subscription->total_user_limit ?? '∞' }}</span>
                            </div>

                            @php
                                $remainingSlots =
                                    ($company->subscription->total_user_limit ?? 999) - $company->active_users_count;
                            @endphp

                            {{-- 🔥 PERUBAHAN: Tampilkan jumlah aktif & nonaktif --}}
                            <div class="flex items-center gap-2 text-xs mb-4">
                                @if ($remainingSlots > 0)
                                    <span class="bg-green-100 text-green-700 font-semibold px-3 py-1 rounded-full">
                                        ✅ Aktif: {{ $company->active_users_count }} | Nonaktif:
                                        {{ $company->inactive_users_count }}
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-700 font-semibold px-3 py-1 rounded-full">
                                        ⚠️ Batas maksimal tercapai
                                    </span>
                                @endif
                            </div>

                            {{-- 🔥 TAMBAHAN BARU: Tombol Kelola Status User (Hanya SuperAdmin) --}}
                            @if (isset($currentUserRole) && ($currentUserRole === 'SuperAdmin' || $currentUserRole === 'Super Admin'))
                                <button onclick="openUserStatusModal()"
                                    class="w-full bg-blue-600 text-white text-sm font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition shadow-md">
                                    ⚙️ Kelola Status Anggota
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Tabel Kuitansi -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="p-4 sm:p-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Kuitansi Perpanjangan Paket
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[800px]">
                                <thead class="bg-gray-100">
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-4 text-xs font-bold text-gray-700 uppercase">No
                                            Invoice</th>
                                        <th class="text-left py-3 px-4 text-xs font-bold text-gray-700 uppercase">Status
                                        </th>
                                        <th class="text-left py-3 px-4 text-xs font-bold text-gray-700 uppercase">Tanggal
                                        </th>
                                        <th class="text-left py-3 px-4 text-xs font-bold text-gray-700 uppercase">Paket
                                        </th>
                                        <th class="text-left py-3 px-4 text-xs font-bold text-gray-700 uppercase">Total
                                        </th>
                                        <th class="text-center py-3 px-4 text-xs font-bold text-gray-700 uppercase">Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($invoices as $invoice)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="py-3 px-4 text-sm text-gray-800 font-medium">
                                                {{ $invoice->external_id ?? 'INV-' . $invoice->id }}
                                            </td>
                                            <td class="py-3 px-4">
                                                @if ($invoice->status === 'paid')
                                                    <span
                                                        class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">✅
                                                        Lunas</span>
                                                @elseif($invoice->status === 'failed')
                                                    <span
                                                        class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">❌
                                                        Ditolak</span>
                                                @elseif($invoice->status === 'pending' && $invoice->proof_of_payment)
                                                    <span
                                                        class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">⏳
                                                        Verifikasi Admin</span>
                                                @else
                                                    {!! $invoice->status_badge !!}
                                                @endif
                                            </td>

                                            <td class="py-3 px-4 text-sm text-gray-700">
                                                {{ $invoice->created_at->format('d M Y') }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-800">
                                                <span class="font-semibold">{{ $invoice->purchased_plan_name }}</span>
                                                @if ($invoice->purchased_addon_count > 0)
                                                    <br><span class="text-xs text-gray-500">+
                                                        {{ $invoice->purchased_addon_count }} user addon</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-sm font-bold text-gray-900">
                                                Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                            </td>

                                            <td class="py-3 px-4 text-center">
                                                @if ($invoice->status === 'failed')
                                                    {{-- Tombol Lihat Alasan jika Ditolak --}}
                                                    <button
                                                        onclick="showRejectionReason('{{ $invoice->admin_notes ?? 'Tidak ada alasan spesifik dari admin.' }}')"
                                                        class="bg-gray-800 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-gray-900 transition shadow-md whitespace-nowrap">
                                                        🔍 Lihat Alasan
                                                    </button>
                                                @elseif($invoice->status === 'pending')
                                                    @if ($invoice->payment_method === 'manual' && !$invoice->proof_of_payment)
                                                        {{-- Tombol untuk buka modal upload ulang jika belum upload bukti --}}
                                                        <button
                                                            onclick="openUploadModal('{{ $invoice->external_id }}', {{ $invoice->amount }})"
                                                            class="bg-orange-500 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-orange-600 transition shadow-md">
                                                            📤 Upload Bukti
                                                        </button>
                                                    @elseif($invoice->payment_url)
                                                        {{-- Tombol bayar untuk Midtrans --}}
                                                        <a href="{{ $invoice->payment_url }}" target="_blank"
                                                            class="inline-flex items-center gap-1 bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold px-4 py-2 rounded-lg hover:from-green-600 hover:to-green-700 transition shadow-md">
                                                            💰 Bayar Sekarang
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-gray-500 italic">⏳ Menunggu
                                                            Verifikasi</span>
                                                    @endif
                                                @elseif($invoice->status === 'paid')
                                                    <span class="text-xs text-green-600 font-semibold">✓ Selesai</span>
                                                @else
                                                    <button onclick="openModal()"
                                                        class="bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-blue-600">
                                                        🔄 Buat Baru
                                                    </button>
                                                @endif
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-12 text-center">
                                                <div class="flex flex-col items-center gap-3">
                                                    <svg class="w-16 h-16 text-gray-300" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <p class="text-gray-500 font-medium">Belum ada riwayat pembayaran</p>
                                                    <button onclick="openModal()"
                                                        class="inline-flex items-center gap-2 text-[#5FD0AB] hover:text-[#4dbf9a] font-semibold text-sm transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Pilih paket untuk memulai
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilih Paket -->
    @include('components.pilihan-paket')


    {{-- 🔥 MODAL KELOLA STATUS USER --}}
    <div id="modalUserStatus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6">

            {{-- Header Modal --}}
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">⚙️ Kelola Status Anggota</h3>
                <button onclick="closeUserStatusModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Info Jumlah User --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <strong>Total User:</strong> {{ $company->users->count() }} |
                    <strong class="text-green-700">Aktif:</strong> {{ $company->active_users_count }} |
                    <strong class="text-red-700">Nonaktif:</strong> {{ $company->inactive_users_count }}
                </p>
            </div>

            {{-- User List Container (diisi via JavaScript) --}}
            <div id="userStatusList" class="space-y-3">
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Memuat data anggota...</p>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timestamp = new Date().getTime();
            fetch('/api/plans?_=' + timestamp)
                .then(res => res.json())
                .then(data => {
                    plans = data.plans;
                    addon = data.addon;
                    plansLoaded = true;
                    console.log('✅ Plans preloaded:', plans.length);
                })
                .catch(err => console.error('❌ Preload plans failed:', err));
        });

        function openModal() {
            document.getElementById('modalPilihPaket').classList.remove('hidden');
            document.getElementById('modalPilihPaket').classList.add('flex');
            if (typeof loadPlans === 'function') loadPlans();
        }

        function closeModal() {
            document.getElementById('modalPilihPaket').classList.add('hidden');
            document.getElementById('modalPilihPaket').classList.remove('flex');
        }

        function showRejectionReason(reason) {
            Swal.fire({
                title: 'Alasan Penolakan',
                text: reason,
                icon: 'info',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#1e40af'
            });
        }



        async function openUserStatusModal() {
            const modal = document.getElementById('modalUserStatus');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            try {
                const response = await fetch(`/api/company/{{ $company->id }}/users-status`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Gagal memuat data');
                }

                const container = document.getElementById('userStatusList');

                if (data.users.length === 0) {
                    container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <p>Tidak ada anggota lain yang dapat dikelola</p>
                </div>
            `;
                    return;
                }

                container.innerHTML = data.users.map(user => `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-2 border-gray-200 hover:border-blue-300 transition">
                
                {{-- Info User --}}
                <div class="flex items-center gap-3 flex-1">
                    <img src="${user.avatar}" 
                         class="w-12 h-12 rounded-full border-2 border-white shadow-md" 
                         alt="${user.full_name}"
                         onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(user.full_name)}'">
                    <div>
                        <p class="font-bold text-gray-900">${user.full_name}</p>
                        <p class="text-xs text-gray-600">${user.email}</p>
                        <span class="inline-block mt-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-semibold">
                            ${user.role_name}
                        </span>
                    </div>
                </div>
                
                {{-- Toggle Switch --}}
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" 
                           ${user.status_active ? 'checked' : ''}
                           onchange="toggleUserStatus('${user.user_company_id}', this.checked)"
                           class="sr-only peer">
                    
                    {{-- Toggle Switch UI --}}
                    <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer 
                         peer-checked:after:translate-x-full peer-checked:after:border-white 
                         after:content-[''] after:absolute after:top-0.5 after:left-[4px] 
                         after:bg-white after:border-gray-300 after:border after:rounded-full 
                         after:h-6 after:w-6 after:transition-all 
                         peer-checked:bg-green-500"></div>
                    
                    {{-- Label Status --}}
                    <span class="ml-3 text-sm font-bold whitespace-nowrap ${user.status_active ? 'text-green-600' : 'text-red-600'}">
                        ${user.status_active ? '✅ Aktif' : '❌ Nonaktif'}
                    </span>
                </label>
                
            </div>
        `).join('');

            } catch (error) {
                console.error('Error loading users:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: error.message || 'Terjadi kesalahan saat memuat data anggota',
                    confirmButtonColor: '#dc2626'
                });
                closeUserStatusModal();
            }
        }

        /**
         * Tutup modal kelola status user
         */
        function closeUserStatusModal() {
            const modal = document.getElementById('modalUserStatus');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        /**
         * Toggle status aktif/nonaktif user
         */
        async function toggleUserStatus(userCompanyId, isActive) {
            const statusText = isActive ? 'mengaktifkan' : 'menonaktifkan';

            // Konfirmasi dulu
            const result = await Swal.fire({
                title: 'Konfirmasi',
                text: `Anda yakin ingin ${statusText} user ini?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: isActive ? '#16a34a' : '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) {
                // Revert toggle jika user cancel
                event.target.checked = !isActive;
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch('/subscription/toggle-user-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        user_company_id: userCompanyId,
                        status_active: isActive
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal mengubah status user');
                }

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Refresh halaman
                setTimeout(() => window.location.reload(), 2000);

            } catch (error) {
                console.error('Error:', error);

                await Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message || 'Terjadi kesalahan saat mengubah status user',
                    confirmButtonColor: '#dc2626'
                });

                // Revert toggle jika error
                event.target.checked = !isActive;
            }
        }

        // Close modal dengan ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('modalUserStatus');
                if (!modal.classList.contains('hidden')) {
                    closeUserStatusModal();
                }
            }
        });
    </script>
@endpush
