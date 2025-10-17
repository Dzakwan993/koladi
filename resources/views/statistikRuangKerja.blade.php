@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <div class="bg-[#e9effd] min-h-screen">
        @include('components.workspace-nav')

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Inter', sans-serif;
            }

            .dashboard-container {
                font-family: 'Inter', sans-serif;
                padding: 20px;
                min-height: 100vh;
            }

            .container {
                margin: 0 auto;
                display: grid;
                grid-template-columns: 320px 320px 1fr;
                gap: 30px;
            }

            .card {
                background: white;
                width: 320px;
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }

            .profile-card {
                display: flex;
                align-items: center;
                gap: 16px;
            }

            /* Avatar bulat */
            .avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                overflow: hidden;
                flex-shrink: 0;
            }

            .avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 50%;
            }

            /* Info profil */
            .profile-info h3 {
                margin: 0;
                font-size: 20px;
                font-weight: 600;
                color: #111827;
            }

            .profile-info p {
                margin: 4px 0 0;
                color: #6b7280;
                font-size: 16px;
            }

            .period {
                font-size: 14px;
                color: #6B7280;
                margin-top: 12px;
                margin-bottom: 16px;
            }

            .period strong {
                color: #111827;
            }

            .bagus-button {
                width: 100%;
                padding: 12px;
                background: white;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                font-size: 14px;
                font-weight: 500;
                color: #111827;
            }

            .stars {
                display: flex;
                gap: 9px;
                size: 20px;
                color: #d1d5db;
                font-size: 16px;
            }

            .chart-card {
                position: relative;
            }

            .chart-header {
                font-size: 16px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 20px;
            }

            .chart-container {
                width: 200px;
                height: 200px;
                margin: 0 auto;
                position: relative;
            }

            .donut-chart {
                width: 100%;
                height: 100%;
            }

            .chart-legend {
                position: absolute;
                right: 24px;
                top: 80px;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
                color: #6B7280;
            }

            .legend-color {
                width: 12px;
                height: 12px;
                border-radius: 2px;
            }

            .tasks-card {
                grid-column: 1 / 3;
                grid-row: 2;
                width: 850px;
            }

            .tasks-header {
                background: #facc15;
                color: white;
                border-radius: 8px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 600;
                font-size: 14px;
            }

            .filter-icon {
                cursor: pointer;
            }

            .task-date {
                font-size: 12px;
                color: #6B7280;
                margin-bottom: 16px;
            }

            .task-item {
                background: #e9effd;
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 12px;
                position: relative;
            }

            .task-item::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 4px;
                background: #2563eb;
                border-radius: 12px 0 0 12px;
            }

            .task-title {
                font-size: 14px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 4px;
            }

            .task-progress {
                font-size: 12px;
                color: #6B7280;
                margin-bottom: 12px;
            }

            .task-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .task-avatars {
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .task-avatar {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                border: 2px solid white;
            }

            .task-avatar:nth-child(1) {
                background: #2563eb;
            }

            .task-avatar:nth-child(2) {
                background: #facc15;
            }

            .task-avatar:nth-child(3) {
                background: #40c79a;
            }

            .task-assignee {
                font-size: 12px;
                color: #6B7280;
            }

            .task-count {
                font-size: 13px;
                color: #6B7280;
                font-weight: 500;
                position: absolute;
                right: 16px;
                top: 16px;
            }

            .sidebar-card {
                grid-column: 3 / 4;
                grid-row: 1 / 3;
            }

            .dropdown {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 14px;
                color: #9ca3af;
                cursor: pointer;
                margin-bottom: 1px;
                position: relative;
                background: white;
            }

            .section-title {
                font-size: 14px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 12px;
            }

            .workspace-list,
            .member-list {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-bottom: 24px;
            }

            .workspace-item,
            .member-item {
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 12px;
                cursor: pointer;
                transition: all 0.2s;
                background: white;
            }

            .workspace-item:hover,
            .member-item:hover {
                border-color: #d1d5db;
                background: #f9fafb;
            }

            .workspace-icon {
                width: 20px;
                height: 20px;
                color: #2563eb;
            }

            .workspace-name,
            .member-name {
                font-size: 13px;
                color: #6B7280;
            }

            .search-box-wrapper {
                position: relative;
                margin-bottom: 12px;
            }

            .search-box {
                width: 100%;
                padding: 12px 40px 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 14px;
                outline: none;
            }

            .search-icon {
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                opacity: 0.5;
                pointer-events: none;
            }

            .member-avatar {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: #6B7280;
            }

            @media (max-width: 1200px) {
                .container {
                    grid-template-columns: 1fr 1fr;
                }

                .tasks-card {
                    grid-column: 1 / 3;
                    grid-row: auto;
                }

                .sidebar-card {
                    grid-column: 1 / 3;
                    grid-row: auto;
                }
            }

            @media (max-width: 768px) {
                .container {
                    grid-template-columns: 1fr;
                }

                .tasks-card {
                    grid-column: 1;
                    grid-row: auto;
                }

                .sidebar-card {
                    grid-column: 1;
                    grid-row: auto;
                }
            }
        </style>

        <style>
            @keyframes pulse-glow {

                0%,
                100% {
                    transform: scale(1);
                    filter: drop-shadow(0 0 4px rgba(59, 130, 246, 0.4));
                }

                50% {
                    transform: scale(1.1);
                    filter: drop-shadow(0 0 12px rgba(59, 130, 246, 0.9));
                }
            }

            .star-outline {
                transition: all 0.3s ease;
                color: #333;
                font-size: 36px;
                cursor: pointer;
            }

            .star-outline:hover {
                color: #333;
                transform: scale(1.2);
                animation: pulse-glow 0.6s ease-in-out;
            }

            .donut-chart circle {
                transition: stroke-dasharray 1s ease, stroke-dashoffset 1s ease;
            }
        </style>

        <div class="px-5">
            <div
                class="bg-white rounded-2xl shadow-lg w-full mx-auto mt-6 mb-1 p-5 flex flex-col justify-center font-inter space-y-2 border border-gray-200">
                <!-- Judul -->
                <h2 class="text-lg font-semibold flex items-center gap-2 text-blue-600">
                    💡 Saran
                </h2>

                <!-- Isi -->
                <p class="text-gray-800 text-sm leading-relaxed">
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
                <div class="card profile-card-wrapper" style="width: 420px;">
                    <div class="project-card flex items-center gap-4">
                        {{-- <!-- Icon Proyek -->
    <img src="/images/icons/BlueProyek.svg" alt="Project Icon"> --}}

                        <!-- Info Proyek -->
                        <div class="project-info">
                            <h3 class="text-lg font-semibold">Nama Proyek ABC</h3>
                            <p class="text-sm text-gray-500">Status/Deskripsi singkat</p>
                        </div>
                    </div>


                    <div class="period">
                        Periode: <strong>1 Sep - 28 Sep</strong>
                    </div>

                    <div class="flex justify-center mt-6">
                        <div
                            class="border-2 border-blue-500 rounded-xl px-6 py-4 bg-white shadow-sm w-[280px] flex flex-col items-center">
                            <p class="text-lg font-bold text-black ">Bagus</p>
                            <div class="w-40 h-[2px] bg-blue-500 mb-2"></div>

                            <div class="flex justify-center items-center space-x-2">
                                @php
                                    $rating = 3;
                                @endphp

                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $rating)
                                        <span>
                                            <img src="images/icons/Star.svg" alt="Bintang Kuning"
                                                style="width:30px; height:30px;">
                                        </span>
                                    @else
                                        <span>
                                            <img src="images/icons/Star1.svg" alt="Bintang Abu"
                                                style="width:30px; height:30px;">
                                        </span>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Card -->
                <div
                    style="background:#fff; border-radius:16px; padding:20px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:420px; margin-left:80px;">
                    <div style="font-size:18px; font-weight:700; color:black; margin-bottom:16px; text-align:left;">
                        Rekap Kinerja
                    </div>

                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding:30px;">
                        <div style="flex-shrink:0;">
                            <svg viewBox="0 0 200 200" style="width:140px; height:140px;">
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#e5e7eb"
                                    stroke-width="35" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#2563eb"
                                    stroke-width="35" stroke-dasharray="80 402.4" stroke-dashoffset="0"
                                    transform="rotate(-90 100 100)" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#6B7280"
                                    stroke-width="35" stroke-dasharray="120 362.4" stroke-dashoffset="-80"
                                    transform="rotate(-90 100 100)" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#facc15"
                                    stroke-width="35" stroke-dasharray="60 422.4" stroke-dashoffset="-200"
                                    transform="rotate(-90 100 100)" />
                                <circle cx="100" cy="100" r="80" fill="none" stroke="#40c79a"
                                    stroke-width="35" stroke-dasharray="142.4 340" stroke-dashoffset="-260"
                                    transform="rotate(-90 100 100)" />
                                <text x="100" y="110" text-anchor="middle" font-size="20" fill="#374151"
                                    font-weight="bold">100%</text>
                            </svg>
                        </div>

                        <div
                            style="display:flex; flex-direction:column; gap:8px; font-size:14px; color:#374151; padding:10px; justify-content:center; align-items:flex-start; height:100%;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:14px; height:14px; background:#2563eb; border-radius:4px;"></div>
                                <span>Belum: 20%</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:14px; height:14px; background:#6B7280; border-radius:4px;"></div>
                                <span>Dikerjakan: 30%</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:14px; height:14px; background:#facc15; border-radius:4px;"></div>
                                <span>Terlambat: 14%</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:14px; height:14px; background:#40c79a; border-radius:4px;"></div>
                                <span>Selesai: 36%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Card -->
                <div class="card sidebar-card"
                    style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 4px 8px rgba(0,0,0,0.1); width:360px; margin-left:auto; margin-right:0;">
                    <p class="text-lg font-bold text-black font-inter" style="size:16px; padding:5px;">Rekap Kerja</p>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:14px;">
                        <input type="text" class="search-box" placeholder="Bulan ini"
                            style="width:100%; padding:6px 28px 6px 8px; border:1px solid #d1d5db; border-radius:8px;">
                        <span class="search-icon" style="position:absolute; right:8px; top:50%;"><img
                                src="images/icons/Arrow.svg" alt=""></span>
                    </div>

                    <div class="section-title" style="font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                        Cari workspace</div>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:14px;">
                        <input type="text" class="search-box" placeholder="Cari..."
                            style="width:100%; padding:6px 28px 6px 8px; border:1px solid #d1d5db; border-radius:8px;">
                        <span class="search-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%);"><img
                                src="images/icons/Search.svg" alt=""></span>
                    </div>

                    <div class="workspace-list" style="display:flex; flex-direction:column; gap:8px; margin-bottom:16px;">

                        <div class="workspace-item"
                            style="display:flex; align-items:center; gap:8px; padding:8px; border-radius:8px; cursor:pointer; background:#f9fafb; transition:0.2s; border:1px solid #e5e7eb;"
                            onclick="selectWorkspace(this)">
                            <img src="images/icons/BlueHQ .svg" alt="HQ Icon"
                                style="width:28px; height:28px; object-fit:contain;">
                            <span class="workspace-name" style="font-size:14px; color:#374151;">Ini HQ</span>
                        </div>

                        <div class="workspace-item"
                            style="display:flex; align-items:center; gap:8px; padding:8px; border-radius:8px; cursor:pointer; background:#f9fafb; transition:0.2s; border:1px solid #e5e7eb;"
                            onclick="selectWorkspace(this)">
                            <img src="images/icons/BlueTim.svg" alt="Tim Icon"
                                style="width:28px; height:28px; object-fit:contain;">
                            <span class="workspace-name" style="font-size:14px; color:#374151;">Ini Tim</span>
                        </div>

                        <div class="workspace-item"
                            style="display:flex; align-items:center; gap:8px; padding:8px; border-radius:8px; cursor:pointer; background:#f9fafb; transition:0.2s; border:1px solid #e5e7eb;"
                            onclick="selectWorkspace(this)">
                            <img src="images/icons/BlueProyek.svg" alt="Proyek Icon"
                                style="width:28px; height:28px; object-fit:contain;">
                            <span class="workspace-name" style="font-size:14px; color:#374151;">Ini Projek</span>
                        </div>

                    </div>

                    <script>
                        function selectWorkspace(el) {
                            // reset semua item
                            document.querySelectorAll('.workspace-item').forEach(item => {
                                item.style.background = '#f9fafb';
                                item.style.border = '1px solid #e5e7eb';
                            });
                            // item yang dipilih
                            el.style.background = '#e9effd';
                            el.style.border = '1px solid #2563eb';
                        }
                    </script>

                    <div class="section-title" style="font-size:14px; font-weight:600; color:#374151; margin-bottom:8px;">
                        Cari anggota</div>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:12px;">
                        <input type="text" class="search-box" placeholder="Cari..."
                            style="width:100%; padding:6px 28px 6px 8px; border:1px solid #d1d5db; border-radius:8px;">
                        <span class="search-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%);"><img
                                src="images/icons/Search.svg" alt=""></span>
                    </div>

                    <div class="member-list" style="display:flex; flex-direction:column; gap:8px;">
                        <div class="member-item"
                            style="display:flex; align-items:center; gap:8px; background:#f9fafb; border-radius:8px; padding:6px 8px; cursor:pointer; transition:0.2s; border:1px solid #e5e7eb;"
                            onclick="selectMember(this)">
                            <img src="https://i.pravatar.cc/40?img=1" alt="Jokowi"
                                style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                            <span class="member-name" style="font-size:14px; color:#374151;">Jokowi</span>
                        </div>

                        <div class="member-item"
                            style="display:flex; align-items:center; gap:8px; background:#f9fafb; border-radius:8px; padding:6px 8px; cursor:pointer; transition:0.2s; border:1px solid #e5e7eb;"
                            onclick="selectMember(this)">
                            <img src="https://i.pravatar.cc/40?img=2" alt="Prabowo"
                                style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                            <span class="member-name" style="font-size:14px; color:#374151;">Prabowo</span>
                        </div>

                        <div class="member-item"
                            style="display:flex; align-items:center; gap:8px; background:#f9fafb; border-radius:8px; padding:6px 8px; cursor:pointer; transition:0.2s; border:1px solid #e5e7eb;"
                            onclick="selectMember(this)">
                            <img src="https://i.pravatar.cc/40?img=3" alt="Megawati"
                                style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                            <span class="member-name" style="font-size:14px; color:#374151;">Megawati</span>
                        </div>
                    </div>

                    <script>
                        function selectMember(el) {
                            // Reset semua item ke default
                            document.querySelectorAll('.member-item').forEach(item => {
                                item.style.background = '#f9fafb';
                                item.style.border = '1px solid #e5e7eb';
                            });
                            // Yang dipilih jadi biru
                            el.style.background = '#e9effd';
                            el.style.border = '1px solid #2563eb';
                        }
                    </script>
                </div>

                <!-- Tasks Card -->
                <div
                    style="
  width: 850px;
  background: #FFFFFF;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  font-family: 'Inter', sans-serif;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
">

                    <!-- Header ini-->
                    <div
                        style="
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #bbcff9;
  padding: 14px 18px;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  font-family: 'Inter', sans-serif;
  position: relative;
">
                        <!-- Kiri -->
                        <span id="pageTitle" style="font-size: 18px; font-weight: 700; color: #1e293b;">
                            Perencanaan
                        </span>

                        <!-- Kanan -->
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <!-- Search -->
                            <div style="position: relative;">
                                <input type="text" placeholder="Cari tugas..."
                                    style="
          font-size: 13px;
          padding: 8px 32px 8px 12px;
          border-radius: 8px;
          border: 1px solid #e5e7eb;
          outline: none;
          width: 170px;
          background: #ffffff;
          transition: all 0.2s ease;
        "
                                    onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'
                                    stroke='#94a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'
                                    style='width: 16px; height: 16px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);'>
                                    <circle cx='11' cy='11' r='8'></circle>
                                    <line x1='21' y1='21' x2='16.65' y2='16.65'></line>
                                </svg>
                            </div>

                            <!-- Tombol Filter -->
                            <div style="position: relative;">
                                <button id="filterBtn"
                                    style="
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 12px;
        cursor: pointer;
        transition: all 0.25s ease;
      "
                                    onmouseover="this.style.background='#f1f5f9'"
                                    onmouseout="this.style.background='#ffffff'">
                                    <img src="images/icons/Filter.svg" alt="Filter" style="width: 15px; height: 15px;">
                                    <span>Filter</span>
                                </button>

                                <!-- Popup Pilihan -->
                                <div id="filterMenu"
                                    style="
        display: none;
        position: absolute;
        top: 110%;
        right: 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        padding: 6px 0;
        min-width: 160px;
        animation: fadeIn 0.2s ease;
        z-index: 10;
      ">
                                    <div class="filter-option">Semua</div>
                                    <div class="filter-option">Perencanaan</div>
                                    <div class="filter-option">Proses</div>
                                    <div class="filter-option">Hampir Selesai</div>
                                    <div class="filter-option">Selesai</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Style -->
                    <style>
                        .filter-option {
                            font-size: 14px;
                            color: #334155;
                            padding: 8px 14px;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }

                        .filter-option:hover {
                            background: #2563eb;
                            color: white;
                        }

                        @keyframes fadeIn {
                            from {
                                opacity: 0;
                                transform: translateY(-5px);
                            }

                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                    </style>

                    <!-- Script -->
                    <script>
                        const btn = document.getElementById("filterBtn");
                        const menu = document.getElementById("filterMenu");
                        const pageTitle = document.getElementById("pageTitle");

                        btn.addEventListener("click", () => {
                            menu.style.display = menu.style.display === "block" ? "none" : "block";
                        });

                        document.addEventListener("click", (e) => {
                            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                                menu.style.display = "none";
                            }
                        });

                        document.querySelectorAll(".filter-option").forEach(opt => {
                            opt.addEventListener("click", () => {
                                pageTitle.textContent = opt.textContent; // 🔥 Ganti teks utama sesuai pilihan
                                menu.style.display = "none";
                            });
                        });
                    </script>
                    {{-- END nya --}}


                    <!-- Task group -->
                    <div
                        style="
  background: #bbcff9;
  border-radius: 12px;
  padding: 16px;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
">

                        <!-- Task 1 -->
                        <div
                            style="
    background: white;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  ">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: black;">Div. Marketing</div>
                                <div
                                    style="font-size: 13px; color: #475569; margin-top: 4px; max-width: 280px; line-height: 1.4;">
                                    Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                                    <div style="display: flex;">
                                        <img src="https://i.pravatar.cc/24?img=1"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white;">
                                        <img src="https://i.pravatar.cc/24?img=2"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; margin-left: -6px;">
                                        <img src="https://i.pravatar.cc/24?img=3"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; margin-left: -6px;">
                                    </div>
                                    <span style="font-size: 12px; color: #64748b;">Sahroni dan 3 lainnya</span>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <span style="font-size: 12px; color: #64748b;">Persentase</span>
                                <!-- Progress Circle 80% -->
                                <div class="progress-circle" data-progress="80"
                                    style="
  position: relative;
  width: 71px;
  height: 71px;
  display: flex;
  align-items: center;
  justify-content: center;
">
                                    <svg viewBox="0 0 36 36"
                                        style="
    position: absolute;
    top: 0;
    left: 0;
    transform: rotate(0deg); /* mulai dari jam 12 */
  ">
                                        <!-- Background lingkaran -->
                                        <path d="M18 2.0845
             a 15.9155 15.9155 0 0 1 0 31.831
             a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6f0fb" stroke-width="3.5" />

                                        <!-- Gradasi biru -->
                                        <defs>
                                            <linearGradient id="gradBlue" x1="0%" y1="0%" x2="0%"
                                                y2="100%">
                                                <stop offset="0%" stop-color="#2563eb" />
                                                <stop offset="100%" stop-color="#102a63" />
                                            </linearGradient>
                                        </defs>

                                        <!-- Progress -->
                                        <path class="progress-bar" d="M18 2.0845
             a 15.9155 15.9155 0 0 1 0 31.831
             a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="url(#gradBlue)" stroke-width="3.5"
                                            stroke-linecap="round" stroke-dasharray="0,100" />
                                    </svg>

                                    <!-- Angka tengah -->
                                    <span class="progress-text"
                                        style="
    font-weight: 700;
    color: #111827;
    font-size: 16px;
  ">80%</span>
                                </div>

                                <script>
                                    document.querySelectorAll('.progress-circle').forEach(circle => {
                                        const progress = parseInt(circle.getAttribute('data-progress')) || 0;
                                        const path = circle.querySelector('.progress-bar');
                                        const text = circle.querySelector('.progress-text');
                                        const value = Math.min(progress, 100);

                                        const radius = 15.9155;
                                        const circumference = 2 * Math.PI * radius;

                                        path.style.strokeDasharray = `${circumference}`;
                                        path.style.strokeDashoffset = `${circumference - (value / 100) * circumference}`;

                                        text.textContent = value + '%';
                                    });
                                </script>


                            </div>
                        </div>

                        <!-- Task 2 -->
                        <div
                            style="
    background: white;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  ">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: black;">Div. Marketing</div>
                                <div
                                    style="font-size: 13px; color: #475569; margin-top: 4px; max-width: 280px; line-height: 1.4;">
                                    Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                                    <div style="display: flex;">
                                        <img src="https://i.pravatar.cc/24?img=4"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white;">
                                        <img src="https://i.pravatar.cc/24?img=5"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; margin-left: -6px;">
                                        <img src="https://i.pravatar.cc/24?img=6"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; margin-left: -6px;">
                                    </div>
                                    <span style="font-size: 12px; color: #64748b;">Sahroni dan 3 lainnya</span>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <span style="font-size: 12px; color: #64748b;">Persentase</span>
                                <!-- Progress Circle 70% -->
                                <div class="progress-circle" data-progress="70"
                                    style="
  position: relative;
  width: 71px;
  height: 71px;
  display: flex;
  align-items: center;
  justify-content: center;
">
                                    <svg viewBox="0 0 36 36"
                                        style="
    position: absolute;
    top: 0;
    left: 0;
    transform: rotate(0deg); /* mulai dari jam 12 */
  ">
                                        <!-- Background lingkaran -->
                                        <path d="M18 2.0845
             a 15.9155 15.9155 0 0 1 0 31.831
             a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6f0fb" stroke-width="3.5" />

                                        <!-- Gradasi biru -->
                                        <defs>
                                            <linearGradient id="gradBlue" x1="0%" y1="0%" x2="0%"
                                                y2="100%">
                                                <stop offset="0%" stop-color="#2563eb" />
                                                <stop offset="100%" stop-color="#102a63" />
                                            </linearGradient>
                                        </defs>

                                        <!-- Progress -->
                                        <path class="progress-bar" d="M18 2.0845
             a 15.9155 15.9155 0 0 1 0 31.831
             a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="url(#gradBlue)" stroke-width="3.5"
                                            stroke-linecap="round" stroke-dasharray="0,100" />
                                    </svg>

                                    <!-- Angka tengah -->
                                    <span class="progress-text"
                                        style="
    font-weight: 700;
    color: #111827;
    font-size: 16px;
  ">70%</span>
                                </div>

                                <script>
                                    document.querySelectorAll('.progress-circle').forEach(circle => {
                                        const progress = parseInt(circle.getAttribute('data-progress')) || 0;
                                        const path = circle.querySelector('.progress-bar');
                                        const text = circle.querySelector('.progress-text');
                                        const value = Math.min(progress, 100);

                                        const radius = 15.9155;
                                        const circumference = 2 * Math.PI * radius;

                                        path.style.strokeDasharray = `${circumference}`;
                                        path.style.strokeDashoffset = `${circumference - (value / 100) * circumference}`;

                                        text.textContent = value + '%';
                                    });
                                </script>

                            </div>
                        </div>

                        <!-- Task 3 -->
                        <div
                            style="
    background: white;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  ">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1e293b;">Div. Marketing</div>
                                <div
                                    style="font-size: 13px; color: #475569; margin-top: 4px; max-width: 280px; line-height: 1.4;">
                                    Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                                    <div style="display: flex;">
                                        <img src="https://i.pravatar.cc/24?img=7"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white;">
                                        <img src="https://i.pravatar.cc/24?img=8"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; margin-left: -6px;">
                                        <img src="https://i.pravatar.cc/24?img=9"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; margin-left: -6px;">
                                    </div>
                                    <span style="font-size: 12px; color: #64748b;">Sahroni dan 3 lainnya</span>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <span style="font-size: 12px; color: #64748b;">Persentase</span>
                                <!-- Progress Circle 90% -->
                                <div class="progress-circle" data-progress="90"
                                    style="
  position: relative;
  width: 71px;
  height: 71px;
  display: flex;
  align-items: center;
  justify-content: center;
">
                                    <svg viewBox="0 0 36 36"
                                        style="
    position: absolute;
    top: 0;
    left: 0;
    transform: rotate(0deg); /* mulai dari jam 12 */
  ">
                                        <!-- Background lingkaran -->
                                        <path d="M18 2.0845
             a 15.9155 15.9155 0 0 1 0 31.831
             a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e6f0fb" stroke-width="3.5" />

                                        <!-- Gradasi biru -->
                                        <defs>
                                            <linearGradient id="gradBlue" x1="0%" y1="0%" x2="0%"
                                                y2="100%">
                                                <stop offset="0%" stop-color="#2563eb" />
                                                <stop offset="100%" stop-color="#102a63" />
                                            </linearGradient>
                                        </defs>

                                        <!-- Progress -->
                                        <path class="progress-bar" d="M18 2.0845
             a 15.9155 15.9155 0 0 1 0 31.831
             a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="url(#gradBlue)" stroke-width="3.5"
                                            stroke-linecap="round" stroke-dasharray="0,100" />
                                    </svg>

                                    <!-- Angka tengah -->
                                    <span class="progress-text"
                                        style="
    font-weight: 700;
    color: #111827;
    font-size: 16px;
  ">90%</span>
                                </div>

                                <script>
                                    document.querySelectorAll('.progress-circle').forEach(circle => {
                                        const progress = parseInt(circle.getAttribute('data-progress')) || 0;
                                        const path = circle.querySelector('.progress-bar');
                                        const text = circle.querySelector('.progress-text');
                                        const value = Math.min(progress, 100);

                                        const radius = 15.9155;
                                        const circumference = 2 * Math.PI * radius;

                                        path.style.strokeDasharray = `${circumference}`;
                                        path.style.strokeDashoffset = `${circumference - (value / 100) * circumference}`;

                                        text.textContent = value + '%';
                                    });
                                </script>

                            </div>
                        </div>

                    </div>


                </div>
            </div>
        </div>
    @endsection
