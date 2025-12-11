@extends('layouts.app')

@section('title', 'Laporan Kinerja')

@section('content')
    <div class="bg-[#e9effd] min-h-screen" x-data="{
        // STATE (yang sudah ada)
        selectedMember: null,
        filterMenuOpen: false,
        selectedFilter: 'Todo List',
        selectedWorkspace: '{{ $defaultWorkspace->id }}',
        selectedPeriod: '{{ $defaultPeriod['value'] }}',
        periodDisplay: '{{ $defaultPeriod['display'] }}',
        tasks: {{ Js::from($tasks) }},
        workspaces: {{ Js::from($workspaces->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'type' => $w->type])) }},
        members: {{ Js::from($members->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'avatar' => $m->avatar ?? 'https://i.pravatar.cc/40?u=' . $m->id])) }},
        searchWorkspace: '',
        searchMember: '',
    
        // STATE LOADING (BARU)
        isLoadingProfile: false,
        isLoadingChart: false,
        isLoadingTasks: false,
        periodOptions: {{ Js::from($periodOptions) }},
        workspaceData: null,
        rekapKinerja: {{ Js::from([
            'belum' => 0,
            'dikerjakan' => 0,
            'selesai' => 0,
            'terlambat' => 0,
            'total' => 0,
            'completed_on_time' => '0 dari 0',
            'performance' => [
                'score' => 0,
                'label' => 'Bagus',
                'stars' => 3,
                'color' => '#3b82f6',
            ],
        ]) }},
    
        // INIT
        init() {
            this.$nextTick(() => {
                if (typeof window.initProgressCircles === 'function') {
                    window.initProgressCircles();
                }
            });
    
            this.$watch('selectedPeriod', (newVal) => {
                this.onPeriodChange(newVal);
            });
        },
    
        get filteredWorkspaces() {
            if (!this.searchWorkspace) return this.workspaces;
            return this.workspaces.filter(w =>
                w.name.toLowerCase().includes(this.searchWorkspace.toLowerCase())
            );
        },
    
        get filteredMembers() {
            if (!this.searchMember) return this.members;
            return this.members.filter(m =>
                m.name.toLowerCase().includes(this.searchMember.toLowerCase())
            );
        },
    
    
    
        // METHOD: Ganti Workspace
        async changeWorkspace(workspaceId) {
            if (this.selectedWorkspace === workspaceId) return;
    
            // Set loading state
            this.isLoadingProfile = true;
            this.isLoadingChart = true;
            this.isLoadingTasks = true;
    
            this.selectedWorkspace = workspaceId;
            this.selectedMember = null;
    
            try {
                // ✅ KIRIM PERIODE JUGA!
                const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                const data = await window.fetchWorkspaceData(
                    workspaceId,
                    this.selectedFilter,
                    period ? period.start : null, // ✅ Tambah parameter
                    period ? period.end : null // ✅ Tambah parameter
                );
    
                if (data.success) {
                    this.members = data.data.members;
                    this.tasks = data.data.tasks;
                    this.rekapKinerja = data.data.rekap_kinerja;
                    this.workspaceData = data.data.workspace;
    
                    this.$nextTick(() => {
                        if (typeof window.initProgressCircles === 'function') {
                            window.initProgressCircles();
                        }
                    });
                }
            } catch (error) {
                console.error('Error changing workspace:', error);
            } finally {
                // Hide skeleton setelah data loaded
                this.isLoadingProfile = false;
                this.isLoadingChart = false;
                this.isLoadingTasks = false;
            }
        },
    
        // METHOD: Klik Member
        async selectMember(memberId) {
            if (this.selectedMember === memberId) {
                this.selectedMember = null;
                await this.changeWorkspace(this.selectedWorkspace);
                return;
            }
    
            // Loading hanya untuk chart dan tasks
            this.isLoadingChart = true;
            this.isLoadingTasks = true;
    
            this.selectedMember = memberId;
    
            try {
                // ✅ KIRIM PERIODE JUGA
                const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                const data = await window.fetchMemberData(
                    this.selectedWorkspace,
                    memberId,
                    this.selectedFilter,
                    period ? period.start : null, // ✅ Tambah
                    period ? period.end : null // ✅ Tambah
                );
    
                if (data.success) {
                    this.tasks = data.data.tasks;
                    this.rekapKinerja = data.data.rekap_kinerja;
    
                    this.$nextTick(() => {
                        if (typeof window.initProgressCircles === 'function') {
                            window.initProgressCircles();
                        }
                    });
                }
            } catch (error) {
                console.error('Error selecting member:', error);
            } finally {
                this.isLoadingChart = false;
                this.isLoadingTasks = false;
            }
        },
    
        // METHOD: Ganti Filter Status
        async changeFilter(filter) {
            if (this.selectedFilter === filter) return;
    
            // Loading hanya tasks
            this.isLoadingTasks = true;
    
            this.selectedFilter = filter;
            this.filterMenuOpen = false;
    
            try {
                // ✅ KIRIM PERIODE JUGA!
                const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                const data = await window.fetchTasksByFilter(
                    this.selectedWorkspace,
                    filter,
                    this.selectedMember,
                    period ? period.start : null, // ✅ Tambah parameter
                    period ? period.end : null // ✅ Tambah parameter
                );
    
                if (data.success) {
                    this.tasks = data.data.tasks;
    
                    this.$nextTick(() => {
                        if (typeof window.initProgressCircles === 'function') {
                            window.initProgressCircles();
                        }
                    });
                }
            } catch (error) {
                console.error('Error changing filter:', error);
            } finally {
                this.isLoadingTasks = false;
            }
        },
    
        // METHOD: Ganti Periode
        async onPeriodChange(periodValue) {
            const period = this.periodOptions.find(p => p.value === periodValue);
            if (!period) return;
    
            // Loading chart dan tasks
            this.isLoadingChart = true;
            this.isLoadingTasks = true;
    
            this.periodDisplay = period.display;
    
            try {
                const data = await window.fetchPeriodeData(
                    this.selectedWorkspace,
                    period.start,
                    period.end,
                    this.selectedFilter,
                    this.selectedMember
                );
    
                if (data.success) {
                    this.tasks = data.data.tasks;
                    this.rekapKinerja = data.data.rekap_kinerja;
    
                    this.$nextTick(() => {
                        if (typeof window.initProgressCircles === 'function') {
                            window.initProgressCircles();
                        }
                    });
                }
            } catch (error) {
                console.error('Error changing period:', error);
            } finally {
                this.isLoadingChart = false;
                this.isLoadingTasks = false;
            }
        }
    }">
        @include('components.workspace-nav')

        @vite(['resources/css/statistik.css', 'resources/js/statistik.js'])

        <!-- Saran Box -->
        <div class="px-12" x-show="selectedMember === null" x-transition>
            <div
                class="bg-white rounded-xl shadow-lg w-full mx-auto mt-2 mb-1 p-4 flex flex-col justify-center font-inter space-y-1 border border-gray-200">
                <h2 class="text-base font-semibold flex items-center gap-2 text-blue-600">
                    💡 Saran
                </h2>
                <p class="text-gray-800 text-xs leading-relaxed">
                    Terlihat sepertinya <span
                        class="font-medium text-blue-600">{{ $members->first()->name ?? 'Anggota' }}</span>
                    kurang maksimal kerjanya, coba evaluasi dia ya!
                    Dorong tim untuk kolaborasi lebih aktif dengan reminder harian atau to-do list otomatis.
                </p>
            </div>
        </div>

        <!-- Dashboard Container -->
        <div class="dashboard-container">

            <div class="container">

                <!-- Profile Card -->
                <div class="card profile-card-wrapper" style="position: relative;">
                    <!-- Skeleton Overlay -->
                    <div x-show="isLoadingProfile" class="skeleton-profile-card"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 10; background: white;">
                        <div class="skeleton-profile-header">
                            <div class="skeleton-base skeleton-profile-icon"></div>
                            <div class="skeleton-profile-text">
                                <div class="skeleton-base skeleton-profile-title"></div>
                                <div class="skeleton-base skeleton-profile-subtitle"></div>
                            </div>
                        </div>
                        <div class="skeleton-base skeleton-profile-period"></div>
                        <div class="skeleton-base skeleton-profile-rating" style="margin-top: 16px;"></div>
                    </div>

                    <!-- Content -->
                    <div :class="{ 'loading-hidden': isLoadingProfile }">
                        <!-- Workspace Info dengan Gradient Background -->
                        <div style="padding: 4px 0; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <!-- Icon Workspace -->
                                <div
                                    style="width: 40px; 
                   height: 40px; 
                   background: #f3f4f6;
                   border-radius: 10px;
                   display: flex;
                   align-items: center;
                   justify-content: center;
                   flex-shrink: 0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2">
                                        </rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                </div>

                                <!-- Workspace Name & Description -->
                                <div style="flex: 1; min-width: 0;">
                                    <h3 style="font-size: 16px; 
                      font-weight: 700; 
                      color: #111827; 
                      margin: 0 0 2px 0;"
                                        x-text="workspaceData ? workspaceData.name : '{{ $defaultWorkspace->name }}'">
                                    </h3>
                                    <p style="font-size: 12px; 
                     color: #6b7280; 
                     margin: 0;
                     overflow: hidden;
                     text-overflow: ellipsis;
                     white-space: nowrap;"
                                        x-text="workspaceData ? workspaceData.description : '{{ $defaultWorkspace->description ?? 'Workspace aktif' }}'">
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Periode Info dengan Icon -->
                        <div
                            style="display: flex; 
                    align-items: center; 
                    gap: 8px; 
                    padding: 10px 12px;
                    background: #f3f4f6;
                    border-radius: 8px;
                    margin-bottom: 16px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span style="font-size: 13px; color: #6b7280;">Periode:</span>
                            <strong style="font-size: 13px; color: #111827; font-weight: 600;"
                                x-text="periodDisplay"></strong>
                        </div>

                        <!-- Performance Rating Card -->
                        <div
                            :style="{
                                'background': 'linear-gradient(135deg, ' + (rekapKinerja?.performance?.color ||
                                        '#3b82f6') + '15 0%, ' + (rekapKinerja?.performance?.color || '#3b82f6') +
                                    '10 100%)',
                                'border': '2px solid ' + (rekapKinerja?.performance?.color || '#3b82f6'),
                                'border-radius': '12px',
                                'padding': '16px',
                                'text-align': 'center',
                                'box-shadow': '0 2px 8px rgba(59, 130, 246, 0.1)'
                            }">
                            <!-- Rating Label -->
                            <div
                                style="display: inline-flex;
                                      align-items: center;
                                      gap: 6px;
                                      background: white;
                                      padding: 6px 16px;
                                      border-radius: 20px;
                                      margin-bottom: 12px;
                                      box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    :fill="rekapKinerja?.performance?.color || '#3b82f6'" stroke="none">
                                    <path
                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                </svg>
                                <span style="font-size: 15px; font-weight: 700;"
                                    :style="{ color: rekapKinerja?.performance?.color || '#1e40af' }"
                                    x-text="rekapKinerja?.performance?.label || 'Bagus'">
                                </span>
                            </div>

                            <!-- Divider -->
                            <div style="width: 80px; 
                                          height: 2px; 
                                          margin: 0 auto 12px;"
                                :style="{
                                    background: 'linear-gradient(90deg, transparent, ' + (rekapKinerja?.performance
                                        ?.color || '#3b82f6') + ', transparent)'
                                }">
                            </div>

                            <!-- Star Rating - Dynamic -->
                            <div style="display: flex; justify-content: center; align-items: center; gap: 6px;">
                                <template x-for="i in 5" :key="i">
                                    <div style="position: relative;
                                              width: 28px;
                                              height: 28px;
                                              transition: transform 0.2s ease;"
                                        :style="{ opacity: i <= (rekapKinerja?.performance?.stars || 3) ? '1' : '0.3' }">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                            viewBox="0 0 24 24"
                                            :fill="i <= (rekapKinerja?.performance?.stars || 3) ? '#fbbf24' : '#d1d5db'"
                                            stroke="none" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Rating Score -->
                            <div style="margin-top: 12px; font-size: 12px; color: #6b7280;">
                                <span style="font-weight: 600;"
                                    :style="{ color: rekapKinerja?.performance?.color || '#3b82f6' }"
                                    x-text="rekapKinerja?.performance?.stars || 3">
                                </span> dari 5 bintang
                                <span style="display: block; margin-top: 4px; font-size: 11px;">
                                    (Score: <span x-text="rekapKinerja?.performance?.score || 0"></span>/100)
                                </span>
                            </div>
                        </div>
                    </div> <!-- ✅ TUTUP Profile Card di sini -->
                </div> <!-- ✅ TUTUP card profile-card-wrapper -->

                <!-- Chart Card -->
                <div class="card chart-card" style="position: relative;">
                    <!-- Skeleton Overlay -->
                    <div x-show="isLoadingChart" class="skeleton-chart-card"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 10; background: white;">
                        <div class="skeleton-base skeleton-chart-title"></div>
                        <div class="skeleton-base skeleton-chart-body"></div>
                    </div>

                    <!-- Content -->
                    <div :class="{ 'loading-hidden': isLoadingChart }">
                        <div style="font-size:15px; font-weight:700; color:black; margin-bottom:16px;">
                            Rekap Kinerja
                        </div>

                        <!-- Chart Container -->
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                            <!-- Donut Chart -->
                            <div style="position: relative; width: 160px; height: 160px; flex-shrink: 0;">
                                <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                    <!-- Background circle -->
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#f3f4f6"
                                        stroke-width="3.2" />

                                    <!-- Belum Dikerjakan (Biru) -->
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#3b82f6"
                                        stroke-width="3.2" :stroke-dasharray="(rekapKinerja?.belum || 0) + ' 100'"
                                        stroke-dashoffset="0" stroke-linecap="round"
                                        style="transition: stroke-dasharray 0.6s ease" />

                                    <!-- Dikerjakan (Abu) -->
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#6b7280"
                                        stroke-width="3.2" :stroke-dasharray="(rekapKinerja?.dikerjakan || 0) + ' 100'"
                                        :stroke-dashoffset="-(rekapKinerja?.belum || 0)" stroke-linecap="round"
                                        style="transition: stroke-dasharray 0.6s ease, stroke-dashoffset 0.6s ease" />

                                    <!-- Selesai (Hijau) -->
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#10b981"
                                        stroke-width="3.2" :stroke-dasharray="(rekapKinerja?.selesai || 0) + ' 100'"
                                        :stroke-dashoffset="-((rekapKinerja?.belum || 0) + (rekapKinerja?.dikerjakan || 0))"
                                        stroke-linecap="round"
                                        style="transition: stroke-dasharray 0.6s ease, stroke-dashoffset 0.6s ease" />

                                    <!-- Terlambat (Kuning) -->
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#fbbf24"
                                        stroke-width="3.2" :stroke-dasharray="(rekapKinerja?.terlambat || 0) + ' 100'"
                                        :stroke-dashoffset="-((rekapKinerja?.belum || 0) + (rekapKinerja?.dikerjakan || 0) + (
                                            rekapKinerja
                                            ?.selesai || 0))"
                                        stroke-linecap="round"
                                        style="transition: stroke-dasharray 0.6s ease, stroke-dashoffset 0.6s ease" />
                                </svg>

                                <!-- Center Text -->
                                <div
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                    <div style="font-size: 24px; font-weight: 700; color: #111827;"
                                        x-text="rekapKinerja?.total || 0"></div>
                                    <div style="font-size: 11px; color: #6b7280;">Total Tugas</div>
                                </div>
                            </div>

                            <!-- Legend -->
                            <div style="display: flex; flex-direction: column; gap: 12px; flex: 1;">
                                <!-- Belum Dikerjakan -->
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 3px;">
                                        </div>
                                        <span style="font-size: 12px; color: #374151;">Todo list</span>
                                    </div>
                                    <span style="font-size: 13px; font-weight: 600; color: #111827;"
                                        x-text="(rekapKinerja?.belum || 0) + '%'"></span>
                                </div>

                                <!-- Dikerjakan -->
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 12px; height: 12px; background: #6b7280; border-radius: 3px;">
                                        </div>
                                        <span style="font-size: 12px; color: #374151;">Dikerjakan</span>
                                    </div>
                                    <span style="font-size: 13px; font-weight: 600; color: #111827;"
                                        x-text="(rekapKinerja?.dikerjakan || 0) + '%'"></span>
                                </div>

                                <!-- Selesai -->
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 12px; height: 12px; background: #10b981; border-radius: 3px;">
                                        </div>
                                        <span style="font-size: 12px; color: #374151;">Selesai</span>
                                    </div>
                                    <span style="font-size: 13px; font-weight: 600; color: #111827;"
                                        x-text="(rekapKinerja?.selesai || 0) + '%'"></span>
                                </div>

                                <!-- Terlambat -->
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 12px; height: 12px; background: #fbbf24; border-radius: 3px;">
                                        </div>
                                        <span style="font-size: 12px; color: #374151;">Terlambat</span>
                                    </div>
                                    <span style="font-size: 13px; font-weight: 600; color: #111827;"
                                        x-text="(rekapKinerja?.terlambat || 0) + '%'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Stats -->
                        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                            <div
                                style="display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; color: #6b7280;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span x-text="rekapKinerja?.completed_on_time || '0 dari 0'"></span>
                                <span>tugas selesai tepat waktu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Card -->
                <div class="card sidebar-card">
                    <p class="text-base font-bold text-black font-inter" style="padding:4px; margin-bottom:8px;">
                        Rekap
                        Kerja
                    </p>

                    <!-- Dropdown Periode -->
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:10px;">
                        <select x-model="selectedPeriod" class="search-box"
                            style="width:100%; padding:6px 24px 6px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; appearance:none; cursor:pointer; background:white;">
                            @foreach ($periodOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['display'] }}</option>
                            @endforeach
                        </select>
                        <span class="search-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:14px; height:14px; pointer-events:none;">
                            <img src="{{ asset('images/icons/Arrow.svg') }}" alt=""
                                style="width:100%; height:100%;">
                        </span>
                    </div>

                    <!-- Search Workspace -->
                    <div class="section-title" style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">
                        Cari workspace
                    </div>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:10px;">
                        <input type="text" x-model="searchWorkspace" class="search-box"
                            placeholder="Cari workspace..."
                            style="width:100%; padding:6px 24px 6px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:12px;">
                        <span class="search-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:14px; height:14px;">
                            <img src="{{ asset('images/icons/Search.svg') }}" alt=""
                                style="width:100%; height:100%;">
                        </span>
                    </div>

                    <!-- Workspace List -->
                    <div class="workspace-list" style="display:flex; flex-direction:column; gap:6px; margin-bottom:12px;">
                        <template x-for="workspace in filteredWorkspaces" :key="workspace.id">
                            <div class="workspace-item"
                                :style="selectedWorkspace === workspace.id ?
                                    'background:#e9effd; border:1px solid #2563eb;' :
                                    'background:#f9fafb; border:1px solid #e5e7eb;'"
                                @click="changeWorkspace(workspace.id)"
                                style="display:flex; align-items:center; gap:6px; padding:6px 8px; border-radius:6px; cursor:pointer; transition:0.2s;">
                                <img :src="workspace.type === 'Tim' ?
                                    '{{ asset('images/icons/Tim.svg') }}' :
                                    '{{ asset('images/icons/Proyek.svg') }}'"
                                    alt="Icon" style="width:22px; height:22px; object-fit:contain;">
                                <span class="workspace-name" style="font-size:12px; color:#374151;"
                                    x-text="workspace.name"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Search Member -->
                    <div class="section-title" style="font-size:12px; font-weight:600; color:#374151; margin-bottom:6px;">
                        Cari anggota
                    </div>
                    <div class="search-box-wrapper" style="position:relative; margin-bottom:10px;">
                        <input type="text" x-model="searchMember" class="search-box" placeholder="Cari anggota..."
                            style="width:100%; padding:6px 24px 6px 8px; border:1px solid #d1d5db; border-radius:6px; font-size:12px;">
                        <span class="search-icon"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:14px; height:14px;">
                            <img src="{{ asset('images/icons/Search.svg') }}" alt=""
                                style="width:100%; height:100%;">
                        </span>
                    </div>

                    <!-- Member List -->
                    <div class="member-list" style="display:flex; flex-direction:column; gap:6px;">
                        <template x-for="member in filteredMembers" :key="member.id">
                            <div class="member-item"
                                :style="selectedMember === member.id ? 'background:#e9effd; border:1px solid #2563eb;' :
                                    'background:#f9fafb; border:1px solid #e5e7eb;'"
                                @click="selectMember(member.id)"
                                style="display:flex; align-items:center; gap:6px; border-radius:6px; padding:6px 8px; cursor:pointer; transition:0.2s;">
                                <img :src="member.avatar" :alt="member.name"
                                    style="width:24px; height:24px; border-radius:50%; object-fit:cover;">
                                <span class="member-name" style="font-size:12px; color:#374151;"
                                    x-text="member.name"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tasks Card -->
                <div class="card tasks-card">
                    <!-- Header tetap terlihat -->
                    <div
                        style="display: flex; align-items: center; gap: 10px; font-family: 'Inter', sans-serif; margin-bottom: 10px;">
                        <div
                            :style="{
                                'flex': '1',
                                'display': 'flex',
                                'align-items': 'center',
                                'justify-content': 'center',
                                'padding': '12px 16px',
                                'border-radius': '10px',
                                'box-shadow': '0 2px 4px rgba(0,0,0,0.05)',
                                'font-size': '15px',
                                'font-weight': '700',
                                'color': 'white',
                                'background': selectedFilter === 'Todo List' ? '#2563eb' :
                                    selectedFilter === 'Dikerjakan' ? '#6B7280' : selectedFilter === 'Selesai' ?
                                    '#40c79a' : selectedFilter === 'Terlambat' ? '#facc15' : '#2563eb'
                            }">
                            <span x-text="selectedFilter"></span>
                        </div>

                        <div style="position: relative;">
                            <button @click="filterMenuOpen = !filterMenuOpen"
                                style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #FFFFFF; border: none; border-radius: 10px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                <img src="{{ asset('images/icons/Filter.svg') }}" alt="Filter"
                                    style="width: 18px; height: 18px;">
                            </button>

                            <div x-show="filterMenuOpen" @click.away="filterMenuOpen = false" x-transition
                                style="position: absolute; top: 110%; right: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.08); padding: 4px 0; min-width: 140px; z-index: 10; font-size: 12px;">
                                <div @click="changeFilter('Todo List')" style="padding: 6px 12px; cursor: pointer;"
                                    onmouseover="this.style.background='#f3f4f6'"
                                    onmouseout="this.style.background='white'">
                                    Todo List
                                </div>
                                <div @click="changeFilter('Dikerjakan')" style="padding: 6px 12px; cursor: pointer;"
                                    onmouseover="this.style.background='#f3f4f6'"
                                    onmouseout="this.style.background='white'">
                                    Dikerjakan
                                </div>
                                <div @click="changeFilter('Selesai')" style="padding: 6px 12px; cursor: pointer;"
                                    onmouseover="this.style.background='#f3f4f6'"
                                    onmouseout="this.style.background='white'">
                                    Selesai
                                </div>
                                <div @click="changeFilter('Terlambat')" style="padding: 6px 12px; cursor: pointer;"
                                    onmouseover="this.style.background='#f3f4f6'"
                                    onmouseout="this.style.background='white'">
                                    Terlambat
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task Grid dengan Skeleton -->
                    <div  class="tasks-container"
                        style="background: #bbcff9; border-radius: 10px; padding: 10px; position: relative; max-height: 260px; overflow-y: scroll;">
                        <div x-show="isLoadingTasks"
                            style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <!-- Skeleton Task Item 1 -->
                            <div class="skeleton-task-item mb-2">
                                <div class="skeleton-task-left">
                                    <div class="skeleton-base skeleton-task-badge"></div>
                                    <div class="skeleton-base skeleton-task-title"></div>
                                </div>
                                <div class="skeleton-base skeleton-task-right"></div>
                            </div>
                            <div class="skeleton-task-item">
                                <div class="skeleton-task-left">
                                    <div class="skeleton-base skeleton-task-badge"></div>
                                    <div class="skeleton-base skeleton-task-title"></div>
                                </div>
                                <div class="skeleton-base skeleton-task-right"></div>
                            </div>
                        </div>

                        <!-- Real Tasks -->
                        <div x-show="!isLoadingTasks"
                            style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <template x-for="task in tasks" :key="task.id">
                                <div
                                    style="width: 90%; margin: 0 auto; background: white; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 10px;">
                                    <div style="flex: 1;">
                                        <div
                                            :style="{
                                                'font-weight': '600',
                                                'color': 'white',
                                                'font-size': '11px',
                                                'background': task.is_completed_late ?
                                                    'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' :
                                                    // Kuning = Selesai Telat
                                                    (task.is_overdue ?
                                                        'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' :
                                                        // Merah = Telat
                                                        (task.days_until_due !== null && task.days_until_due <=
                                                            3 ?
                                                            'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)' :
                                                            // Kuning = Hampir deadline (≤3 hari)
                                                            'linear-gradient(135deg, #10b981 0%, #059669 100%)'
                                                        ) // Hijau = Aman (>3 hari)
                                                    ),
                                                'padding': '4px 8px',
                                                'border-radius': '4px',
                                                'display': 'inline-flex',
                                                'align-items': 'center',
                                                'gap': '6px'
                                            }">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                            <span
                                                x-text="new Date(task.due_datetime).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'})"></span>
                                        </div>

                                        <div style="font-size: 16px; font-weight: bold; color: #000000; margin-top: 3px; max-width: 240px; line-height: 1.3;"
                                            x-text="task.title"></div>
                                    </div>

                                    <div style="text-align: center;">
                                        <div
                                            style="position: relative; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <svg viewBox="0 0 36 36" style="position: absolute; top: 0; left: 0;">
                                                <path
                                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                    fill="none" stroke="#e6f0fb" stroke-width="3.5" />
                                                <defs>
                                                    <linearGradient id="gradBlue" x1="0%" y1="0%"
                                                        x2="0%" y2="100%">
                                                        <stop offset="0%" stop-color="#2563eb" />
                                                        <stop offset="100%" stop-color="#102a63" />
                                                    </linearGradient>
                                                </defs>
                                                <path
                                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                    fill="none" stroke="url(#gradBlue)" stroke-width="3.5"
                                                    stroke-linecap="round" :stroke-dasharray="task.progress + ',100'" />
                                            </svg>
                                            <span style="font-weight: 700; color: #111827; font-size: 12px;"
                                                x-text="task.progress + '%'"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty state -->
                            <template x-if="tasks.length === 0">
                                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280;">
                                    Tidak ada task untuk filter ini
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
