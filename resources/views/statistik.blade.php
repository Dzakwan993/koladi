@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <div class="bg-[#e9effd] min-h-screen" x-data="{
                selectedMember: null,
                filterMenuOpen: false,
                selectedFilter: 'Perencanaan',
                selectedWorkspace: 0
            }" x-init="selectedWorkspace = 0">
        @include('components.workspace-nav')

        @vite(['resources/css/statistik.css', 'resources/js/statistik.js'])

        <!-- Saran Box (hanya muncul jika belum pilih member) -->
        <div class="px-12" x-show="selectedMember === null" x-transition>
            <div
                class="bg-white rounded-xl shadow-lg w-full mx-auto mt-2 mb-1 p-4 flex flex-col justify-center font-inter space-y-1 border border-gray-200">
                <h2 class="text-base font-semibold flex items-center gap-2 text-blue-600">
                    💡 Saran
                </h2>
                <p class="text-gray-800 text-xs leading-relaxed">
                    Terlihat sepertinya <span class="font-medium text-blue-600">Jokowi</span> kurang maksimal kerjanya, coba
                    evaluasi dia ya!
                    Dorong tim untuk kolaborasi lebih aktif dengan reminder harian atau to-do list otomatis.
                </p>
            </div>
        </div>

        <!-- Mulai konten utama dashboard -->
        <div class="dashboard-container">
            <div class="container">
                <!-- Profile Card -->
                <div class="card profile-card-wrapper">
                    <div class="project-card flex items-center gap-3">
                        <div class="project-info">
                            <h3 class="text-base font-semibold">Nama Proyek ABC</h3>
                            <p class="text-xs text-gray-500">Status/Deskripsi singkat</p>
                        </div>
                    </div>

                    <div class="period">
                        Periode: <strong>1 Sep - 28 Sep</strong>
                    </div>

                    <div class="flex justify-center mt-4">
                        <div
                            class="border-2 border-blue-500 rounded-xl px-4 py-3 bg-white shadow-sm w-[240px] flex flex-col items-center">
                            <p class="text-base font-bold text-black">Bagus</p>
                            <div class="w-32 h-[2px] bg-blue-500 mb-2"></div>

                            <div class="flex justify-center items-center space-x-2">
                                @php
                                    $rating = 3;
                                @endphp

                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $rating)
                                        <span>
                                            <img src="images/icons/Star.svg" alt="Bintang Kuning" style="width:24px; height:24px;">
                                        </span>
                                    @else
                                        <span>
                                            <img src="images/icons/Star1.svg" alt="Bintang Abu" style="width:24px; height:24px;">
                                        </span>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Card -->
                <div class="card chart-card">
                    <div style="font-size:15px; font-weight:700; color:black; margin-bottom:12px; text-align:left;">
                        Rekap Kinerja
                    </div>

                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:8px;">
                        <div style="flex-shrink:0;">
                            <svg viewBox="0 0 200 200" style="width:100px; height:100px;">
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#e5e7eb" stroke-width="35" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#2563eb" stroke-width="35"
                                    stroke-dasharray="80 402.4" stroke-dashoffset="0" transform="rotate(-90 100 100)" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#6B7280" stroke-width="35"
                                    stroke-dasharray="120 362.4" stroke-dashoffset="-80" transform="rotate(-90 100 100)" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#facc15" stroke-width="35"
                                    stroke-dasharray="60 422.4" stroke-dashoffset="-200" transform="rotate(-90 100 100)" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#40c79a" stroke-width="35"
                                    stroke-dasharray="142.4 340" stroke-dashoffset="-260" transform="rotate(-90 100 100)" />
                                <text x="100" y="110" text-anchor="middle" font-size="18" fill="#374151"
                                    font-weight="bold">100%</text>
                            </svg>
                        </div>

                        <div
                            style="display:flex; flex-direction:column; gap:6px; font-size:12px; color:#374151; padding:8px; justify-content:center; align-items:flex-start; height:100%;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div style="width:12px; height:12px; background:#2563eb; border-radius:3px;"></div>
                                <span>Belum: 20%</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div style="width:12px; height:12px; background:#6B7280; border-radius:3px;"></div>
                                <span>Dikerjakan: 30%</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div style="width:12px; height:12px; background:#facc15; border-radius:3px;"></div>
                                <span>Terlambat: 14%</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div style="width:12px; height:12px; background:#40c79a; border-radius:3px;"></div>
                                <span>Selesai: 36%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Card -->
                <div class="card sidebar-card">
                    <p class="text-base font-bold text-black font-inter" style="padding:4px; margin-bottom:8px;">Rekap Kerja</p>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:10px;">
                        <input type="text" class="search-box" placeholder="Bulan ini"
                            style="width:100%; padding:6px 24px 6px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:12px;">
                        <span class="search-icon" style="position:absolute; right:8px; top:50%; width:14px; height:14px;"><img
                                src="images/icons/Arrow.svg" alt="" style="width:100%; height:100%;"></span>
                    </div>

                    <div class="section-title" style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">
                        Cari workspace</div>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:10px;">
                        <input type="text" class="search-box" placeholder="Cari..."
                            style="width:100%; padding:6px 24px 6px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:12px;">
                        <span class="search-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:14px; height:14px;"><img
                                src="images/icons/Search.svg" alt="" style="width:100%; height:100%;"></span>
                    </div>

                    <div class="workspace-list" style="display:flex; flex-direction:column; gap:6px; margin-bottom:12px;">
                        <div class="workspace-item"
                            :style="selectedWorkspace === 0 ? 'background:#e9effd; border:1px solid #2563eb;' : 'background:#f9fafb; border:1px solid #e5e7eb;'"
                            @click="selectedWorkspace = 0"
                            style="display:flex; align-items:center; gap:6px; padding:6px 8px; border-radius:6px; cursor:pointer; transition:0.2s;">
                            <img src="images/icons/BlueHQ .svg" alt="HQ Icon"
                                style="width:22px; height:22px; object-fit:contain;">
                            <span class="workspace-name" style="font-size:12px; color:#374151;">Ini HQ</span>
                        </div>

                        <div class="workspace-item"
                            :style="selectedWorkspace === 1 ? 'background:#e9effd; border:1px solid #2563eb;' : 'background:#f9fafb; border:1px solid #e5e7eb;'"
                            @click="selectedWorkspace = 1"
                            style="display:flex; align-items:center; gap:6px; padding:6px 8px; border-radius:6px; cursor:pointer; transition:0.2s;">
                            <img src="images/icons/BlueTim.svg" alt="Tim Icon"
                                style="width:22px; height:22px; object-fit:contain;">
                            <span class="workspace-name" style="font-size:12px; color:#374151;">Ini Tim</span>
                        </div>

                        <div class="workspace-item"
                            :style="selectedWorkspace === 2 ? 'background:#e9effd; border:1px solid #2563eb;' : 'background:#f9fafb; border:1px solid #e5e7eb;'"
                            @click="selectedWorkspace = 2"
                            style="display:flex; align-items:center; gap:6px; padding:6px 8px; border-radius:6px; cursor:pointer; transition:0.2s;">
                            <img src="images/icons/BlueProyek.svg" alt="Proyek Icon"
                                style="width:22px; height:22px; object-fit:contain;">
                            <span class="workspace-name" style="font-size:12px; color:#374151;">Ini Projek</span>
                        </div>
                    </div>

                    <div class="section-title" style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">
                        Cari anggota</div>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:10px;">
                        <input type="text" class="search-box" placeholder="Cari..."
                            style="width:100%; padding:6px 24px 6px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:12px;">
                        <span class="search-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:14px; height:14px;"><img
                                src="images/icons/Search.svg" alt="" style="width:100%; height:100%;"></span>
                    </div>

                    <div class="member-list" style="display:flex; flex-direction:column; gap:6px;">
                        <div class="member-item"
                            :style="selectedMember === 0 ? 'background:#e9effd; border:1px solid #2563eb;' : 'background:#f9fafb; border:1px solid #e5e7eb;'"
                            @click="selectedMember = selectedMember === 0 ? null : 0"
                            style="display:flex; align-items:center; gap:6px; border-radius:6px; padding:6px 8px; cursor:pointer; transition:0.2s;">
                            <img src="https://i.pravatar.cc/40?img=1" alt="Jokowi"
                                style="width:24px; height:24px; border-radius:50%; object-fit:cover;">
                            <span class="member-name" style="font-size:12px; color:#374151;">Jokowi</span>
                        </div>

                        <div class="member-item"
                            :style="selectedMember === 1 ? 'background:#e9effd; border:1px solid #2563eb;' : 'background:#f9fafb; border:1px solid #e5e7eb;'"
                            @click="selectedMember = selectedMember === 1 ? null : 1"
                            style="display:flex; align-items:center; gap:6px; border-radius:6px; padding:6px 8px; cursor:pointer; transition:0.2s;">
                            <img src="https://i.pravatar.cc/40?img=2" alt="Prabowo"
                                style="width:24px; height:24px; border-radius:50%; object-fit:cover;">
                            <span class="member-name" style="font-size:12px; color:#374151;">Prabowo</span>
                        </div>

                        <div class="member-item"
                            :style="selectedMember === 2 ? 'background:#e9effd; border:1px solid #2563eb;' : 'background:#f9fafb; border:1px solid #e5e7eb;'"
                            @click="selectedMember = selectedMember === 2 ? null : 2"
                            style="display:flex; align-items:center; gap:6px; border-radius:6px; padding:6px 8px; cursor:pointer; transition:0.2s;">
                            <img src="https://i.pravatar.cc/40?img=3" alt="Megawati"
                                style="width:24px; height:24px; border-radius:50%; object-fit:cover;">
                            <span class="member-name" style="font-size:12px; color:#374151;">Megawati</span>
                        </div>
                    </div>
                </div>

                <!-- Tasks Card -->
                <div class="card tasks-card">

                    <!-- Header -->
                    <div style="
                      display: flex;
                      align-items: center;
                      justify-content: space-between;
                      background: #bbcff9;
                      padding: 8px 12px;
                      border-radius: 10px;
                      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                      font-family: 'Inter', sans-serif;
                      position: relative;
                      margin-bottom: 10px;
                    ">
                        <span x-text="selectedFilter" style="font-size: 15px; font-weight: 700; color: #1e293b;"></span>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <!-- Search -->
                            <div style="position: relative;">
                                <input type="text" placeholder="Cari tugas..." style="
                              font-size: 12px;
                              padding: 6px 28px 6px 10px;
                              border-radius: 6px;
                              border: 1px solid #e5e7eb;
                              outline: none;
                              width: 150px;
                              background: #ffffff;
                              transition: all 0.2s ease;
                            " onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='#94a3b8'
                                    stroke-width='2' stroke-linecap='round' stroke-linejoin='round'
                                    style='width: 14px; height: 14px; position: absolute; right: 8px; top: 50%; transform: translateY(-50%);'>
                                    <circle cx='11' cy='11' r='8'></circle>
                                    <line x1='21' y1='21' x2='16.65' y2='16.65'></line>
                                </svg>
                            </div>

                            <!-- Filter Button -->
                            <div style="position: relative;">
                                <button @click="filterMenuOpen = !filterMenuOpen" style="
                            display: flex;
                            align-items: center;
                            gap: 4px;
                            font-size: 12px;
                            font-weight: 500;
                            color: #1e293b;
                            background: #ffffff;
                            border: 1px solid #d1d5db;
                            border-radius: 6px;
                            padding: 6px 10px;
                            cursor: pointer;
                            transition: all 0.25s ease;
                          " onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">
                                    <img src="images/icons/Filter.svg" alt="Filter" style="width: 13px; height: 13px;">
                                    <span>Filter</span>
                                </button>

                                <!-- Filter Menu -->
                                <div x-show="filterMenuOpen" @click.away="filterMenuOpen = false" x-transition style="
                            position: absolute;
                            top: 110%;
                            right: 0;
                            background: #ffffff;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
                            padding: 4px 0;
                            min-width: 140px;
                            z-index: 10;
                            font-size: 12px;
                          ">
                                    <div class="filter-option" @click="selectedFilter = 'Semua'; filterMenuOpen = false" style="padding: 6px 12px; cursor: pointer;">
                                        Semua</div>
                                    <div class="filter-option"
                                        @click="selectedFilter = 'Perencanaan'; filterMenuOpen = false" style="padding: 6px 12px; cursor: pointer;">Perencanaan</div>
                                    <div class="filter-option" @click="selectedFilter = 'Proses'; filterMenuOpen = false" style="padding: 6px 12px; cursor: pointer;">
                                        Proses</div>
                                    <div class="filter-option"
                                        @click="selectedFilter = 'Hampir Selesai'; filterMenuOpen = false" style="padding: 6px 12px; cursor: pointer;">Hampir Selesai
                                    </div>
                                    <div class="filter-option" @click="selectedFilter = 'Selesai'; filterMenuOpen = false" style="padding: 6px 12px; cursor: pointer;">
                                        Selesai</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task group -->
                    <div style="
                      background: #bbcff9;
                      border-radius: 10px;
                      padding: 10px;
                      display: grid;
                      grid-template-columns: repeat(2, 1fr);
                      gap: 10px;
                    ">

                        <!-- Task 1 -->
                        <div style="
                        background: white;
                        border-radius: 10px;
                        padding: 10px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                      ">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: black; font-size: 13px;">Div. Marketing</div>
                                <div
                                    style="font-size: 11px; color: #475569; margin-top: 3px; max-width: 240px; line-height: 1.3;">
                                    Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan
                                </div>

                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 8px;">
                                    <div style="display: flex;">
                                        <img src="https://i.pravatar.cc/24?img=1"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;">
                                        <img src="https://i.pravatar.cc/24?img=2"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; margin-left: -5px;">
                                        <img src="https://i.pravatar.cc/24?img=3"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; margin-left: -5px;">
                                    </div>
                                    <span style="font-size: 10px; color: #64748b;">Sahroni dan 3 lainnya</span>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <span style="font-size: 10px; color: #64748b;">Persentase</span>
                                <div class="progress-circle" data-progress="80" style="
                      position: relative;
                      width: 50px;
                      height: 50px;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                    ">
                                    <svg viewBox="0 0 36 36" style="
                        position: absolute;
                        top: 0;
                        left: 0;
                        transform: rotate(0deg);
                      ">
                                        <path d="M18 2.0845
                                 a 15.9155 15.9155 0 0 1 0 31.831
                                 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6f0fb" stroke-width="3.5" />

                                        <defs>
                                            <linearGradient id="gradBlue" x1="0%" y1="0%" x2="0%" y2="100%">
                                                <stop offset="0%" stop-color="#2563eb" />
                                                <stop offset="100%" stop-color="#102a63" />
                                            </linearGradient>
                                        </defs>

                                        <path class="progress-bar" d="M18 2.0845
                                 a 15.9155 15.9155 0 0 1 0 31.831
                                 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="url(#gradBlue)" stroke-width="3.5"
                                            stroke-linecap="round" stroke-dasharray="0,100" />
                                    </svg>

                                    <span class="progress-text" style="
                        font-weight: 700;
                        color: #111827;
                        font-size: 14px;
                      ">80%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Task 2 -->
                        <div style="
                        background: white;
                        border-radius: 10px;
                        padding: 10px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                      ">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: black; font-size: 13px;">Div. Marketing</div>
                                <div
                                    style="font-size: 11px; color: #475569; margin-top: 3px; max-width: 240px; line-height: 1.3;">
                                    Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan
                                </div>

                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 8px;">
                                    <div style="display: flex;">
                                        <img src="https://i.pravatar.cc/24?img=4"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;">
                                        <img src="https://i.pravatar.cc/24?img=5"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; margin-left: -5px;">
                                        <img src="https://i.pravatar.cc/24?img=6"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; margin-left: -5px;">
                                    </div>
                                    <span style="font-size: 10px; color: #64748b;">Sahroni dan 3 lainnya</span>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <span style="font-size: 10px; color: #64748b;">Persentase</span>
                                <div class="progress-circle" data-progress="70" style="
                      position: relative;
                      width: 50px;
                      height: 50px;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                    ">
                                    <svg viewBox="0 0 36 36" style="
                        position: absolute;
                        top: 0;
                        left: 0;
                        transform: rotate(0deg);
                      ">
                                        <path d="M18 2.0845
                                 a 15.9155 15.9155 0 0 1 0 31.831
                                 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6f0fb" stroke-width="3.5" />

                                        <defs>
                                            <linearGradient id="gradBlue2" x1="0%" y1="0%" x2="0%" y2="100%">
                                                <stop offset="0%" stop-color="#2563eb" />
                                                <stop offset="100%" stop-color="#102a63" />
                                            </linearGradient>
                                        </defs>

                                        <path class="progress-bar" d="M18 2.0845
                                 a 15.9155 15.9155 0 0 1 0 31.831
                                 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="url(#gradBlue2)" stroke-width="3.5"
                                            stroke-linecap="round" stroke-dasharray="0,100" />
                                    </svg>

                                    <span class="progress-text" style="
                        font-weight: 700;
                        color: #111827;
                        font-size: 14px;
                      ">70%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Task 3 -->
                        <div style="
                        background: white;
                        border-radius: 10px;
                        padding: 10px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                      ">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 13px;">Div. Marketing</div>
                                <div
                                    style="font-size: 11px; color: #475569; margin-top: 3px; max-width: 240px; line-height: 1.3;">
                                    Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 8px;">
                                    <div style="display: flex;">
                                        <img src="https://i.pravatar.cc/24?img=7"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;">
                                        <img src="https://i.pravatar.cc/24?img=8"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; margin-left: -5px;">
                                        <img src="https://i.pravatar.cc/24?img=9"
                                            style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; margin-left: -5px;">
                                    </div>
                                    <span style="font-size: 10px; color: #64748b;">Sahroni dan 3 lainnya</span>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <span style="font-size: 10px; color: #64748b;">Persentase</span>
                                <div class="progress-circle" data-progress="90" style="
                      position: relative;
                      width: 50px;
                      height: 50px;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                    ">
                                    <svg viewBox="0 0 36 36" style="
                     position: absolute;
                     top: 0;
                     left: 0;
                     transform: rotate(0deg);
                   ">
                                        <path d="M18 2.0845
a 15.9155 15.9155 0 0 1 0 31.831
a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6f0fb" stroke-width="3.5" />
<defs>
<linearGradient id="gradBlue3" x1="0%" y1="0%" x2="0%" y2="100%">
<stop offset="0%" stop-color="#2563eb" />
<stop offset="100%" stop-color="#102a63" />
</linearGradient>
</defs>
                                    <path class="progress-bar" d="M18 2.0845
                         a 15.9155 15.9155 0 0 1 0 31.831
                         a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="url(#gradBlue3)" stroke-width="3.5"
                                        stroke-linecap="round" stroke-dasharray="0,100" />
                                </svg>

                                <span class="progress-text" style="
                font-weight: 700;
                color: #111827;
                font-size: 14px;
              ">90%</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection