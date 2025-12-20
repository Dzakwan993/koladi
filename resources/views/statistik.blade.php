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
        // ✅ TAMBAHKAN INI (DSS DATA)
        loadingSuggestion: false,
        suggestionTimestamp: null,
        isCached: false,
        topSuggestion: null,
        memberAttendance: null,
        showDSSModal: false,
        loadingModalData: false,
        modalData: null,
        allSuggestions: null,
        dssMetrics: null,
        dssTrends: null,
        performanceData: null,
        workspaceData: null,
        rekapKinerja: {{ Js::from($rekapKinerja) }},
    
        // INIT
        init() {
            this.$nextTick(() => {
                if (typeof window.initProgressCircles === 'function') {
                    window.initProgressCircles();
                }
            });
    
            // ✅ TAMBAHKAN INI (DSS WATCHERS)
            this.$watch('selectedWorkspace', () => {
                if (this.selectedWorkspace) {
                    this.loadSuggestions();
                }
            });
    
            this.$watch('selectedPeriod', (newVal) => {
                this.onPeriodChange(newVal);
                // ✅ TAMBAHKAN INI
                this.loadSuggestions();
            });
    
            // ✅ LOAD INITIAL SUGGESTIONS
            this.loadSuggestions();
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
    
        // Dalam Alpine component, tambah helper method:
        showTrendWarning(trend) {
            return trend?.sample_size_warning === true;
        },
    
        formatTrend(trend) {
            if (this.showTrendWarning(trend)) {
                return `${trend.change_absolute > 0 ? '+' : ''}${trend.change_absolute} (sample kecil)`;
            }
            return trend.change_percent ? `${trend.change_percent}%` : '-';
        },
    
        // ✅ TAMBAHKAN METHOD INI (DSS METHODS)
        async loadSuggestions() {
            if (!this.selectedWorkspace || !this.selectedPeriod) return;
    
            this.loadingSuggestion = true;
    
            try {
                const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                const response = await fetch('/statistik/suggestions?' + new URLSearchParams({
                    workspace_id: this.selectedWorkspace,
                    start: period.start,
                    end: period.end
                }));
    
                const result = await response.json();
    
                if (result.success) {
                    this.topSuggestion = result.data.top_suggestion;
                    this.allSuggestions = result.data.all_suggestions;
                    this.dssMetrics = result.data.metrics;
                    this.dssTrends = result.data.trends;
                    this.performanceData = result.data.performance;
    
                    console.log('🔍 Raw Response:', {
                        updated_at: result.data.generated_at,
                        cached: result.data.cached,
                        timestamp_type: typeof result.data.generated_at
                    });
    
                    // ✅ Simpan timestamp & cached status
                    this.suggestionTimestamp = result.data.generated_at;
                    this.isCached = result.data.cached || false;
    
                    console.log('🔍 After Set:', {
                        suggestionTimestamp: this.suggestionTimestamp,
                        isCached: this.isCached,
                        formatted: this.formatTimestamp(this.suggestionTimestamp)
                    });
    
                    console.log('✅ DSS Loaded:', {
                        suggestion: this.topSuggestion,
                        timestamp: this.suggestionTimestamp,
                        cached: this.isCached
                    });
                }
            } catch (error) {
                console.error('❌ Error loading suggestions:', error);
            } finally {
                this.loadingSuggestion = false;
            }
        },
    
        async openSuggestionModal() {
            console.log('Opening DSS Modal...');
    
            this.showDSSModal = true;
            this.loadingModalData = true;
    
            try {
                const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                const response = await fetch('/api/statistik/modal-data?' + new URLSearchParams({
                    workspace_id: this.selectedWorkspace,
                    start: period.start,
                    end: period.end
                }));
    
                const result = await response.json();
    
                if (result.success) {
                    this.modalData = result.data;
                    console.log('✅ Modal data loaded:', this.modalData);
                } else {
                    this.showToast(result.message || 'Gagal memuat data', 'error');
                    this.showDSSModal = false;
                }
            } catch (error) {
                console.error('❌ Error loading modal data:', error);
                this.showToast('Terjadi kesalahan saat memuat data', 'error');
                this.showDSSModal = false;
            } finally {
                this.loadingModalData = false;
            }
        },
    
        // METHOD: Klik Member
        // METHOD: Klik Member
        async selectMember(memberId) {
            if (this.selectedMember === memberId) {
                // ✅ PERBAIKAN: Ketika deselect, reload workspace data
                this.selectedMember = null;
                this.memberAttendance = null; // ✅ Reset attendance
    
                this.isLoadingChart = true;
                this.isLoadingTasks = true;
    
                try {
                    const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                    const data = await window.fetchWorkspaceData(
                        this.selectedWorkspace,
                        this.selectedFilter,
                        period ? period.start : null,
                        period ? period.end : null
                    );
    
                    if (data.success) {
                        this.tasks = data.data.tasks;
                        this.rekapKinerja = data.data.rekap_kinerja;
                        this.memberAttendance = null; // ✅ PASTIKAN NULL saat deselect
    
                        this.$nextTick(() => {
                            if (typeof window.initProgressCircles === 'function') {
                                window.initProgressCircles();
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error deselecting member:', error);
                } finally {
                    this.isLoadingChart = false;
                    this.isLoadingTasks = false;
                }
    
                return;
            }
    
            // Loading hanya untuk chart dan tasks
            this.isLoadingChart = true;
            this.isLoadingTasks = true;
    
            this.selectedMember = memberId;
    
            try {
                const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                const data = await window.fetchMemberData(
                    this.selectedWorkspace,
                    memberId,
                    this.selectedFilter,
                    period ? period.start : null,
                    period ? period.end : null
                );
    
                if (data.success) {
                    this.tasks = data.data.tasks;
                    this.rekapKinerja = data.data.rekap_kinerja;
                    // ✅ FIX: SET memberAttendance dari API response
                    this.memberAttendance = data.data.attendance;
    
                    console.log('✅ Member Selected & Attendance Set:', {
                        member_id: memberId,
                        attendance: this.memberAttendance // ✅ Debug
                    });
    
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
    
        // Method untuk format timestamp
        formatTimestamp(timestamp) {
            if (!timestamp) return '-';
            const date = new Date(timestamp);
            const now = new Date();
            const diffMinutes = Math.floor((now - date) / 1000 / 60);
    
            if (diffMinutes < 1) return 'baru saja';
            if (diffMinutes < 60) return `${diffMinutes} menit lalu`;
    
            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) return `${diffHours} jam lalu`;
    
            return date.toLocaleString('id-ID', {
                day: 'numeric',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
    
        // 🟢 TAMBAHKAN DI SINI - Method refresh manual
        // 🟢 Method refresh manual
        // Method refresh manual
        async refreshSnapshot() {
            if (!this.selectedWorkspace || !this.selectedPeriod) return;
    
            this.loadingSuggestion = true;
    
            try {
                const period = this.periodOptions.find(p => p.value === this.selectedPeriod);
    
                const response = await fetch('/statistik/refresh-snapshot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        workspace_id: this.selectedWorkspace,
                        start: period.start,
                        end: period.end
                    })
                });
    
                const result = await response.json();
    
                if (result.success) {
                    // ✅ FIX: Update SEMUA data dari response refresh
                    this.topSuggestion = result.data.top_suggestion;
                    this.allSuggestions = result.data.all_suggestions;
                    this.dssMetrics = result.data.metrics;
                    this.dssTrends = result.data.trends;
                    this.performanceData = result.data.performance;
                    this.suggestionTimestamp = result.data.generated_at;
                    this.isCached = false; // Force ke Live
    
                    console.log('✅ Refresh Success:', {
                        topSuggestion: this.topSuggestion,
                        timestamp: this.suggestionTimestamp,
                        cached: this.isCached
                    });
    
                    this.showToast('✅ Data berhasil diperbarui!', 'success');
                } else {
                    this.showToast('❌ Gagal memperbarui data', 'error');
                }
            } catch (error) {
                console.error('❌ Error refreshing:', error);
                this.showToast('❌ Terjadi kesalahan', 'error');
            } finally {
                this.loadingSuggestion = false;
            }
        },
    
        // Toast helper
        showToast(message, type = 'info') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            } else {
                alert(message);
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

        @vite(['resources/css/statistik.css', 'resources/js/statistik.js'])

        <!-- ✅ TAMBAHKAN INI DI statistik.blade.php, SEBELUM TAG PENUTUP </div> ALPINE -->

        <!-- DSS Modal - Modern Design -->
        <div x-show="showDSSModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showDSSModal = false"></div>

            <!-- Modal Container -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-show="showDSSModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95" @click.stop
                    class="relative w-full max-w-6xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

                    <!-- Loading Overlay -->
                    <div x-show="loadingModalData"
                        class="absolute inset-0 bg-white/90 backdrop-blur-sm z-10 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-gray-700 font-medium">Memuat analisis lengkap...</p>
                        </div>
                    </div>

                    <!-- Header -->
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-blue-100 rounded-xl">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Analisis Workspace</h2>
                                <p class="text-sm text-gray-600 mt-0.5"
                                    x-text="`${modalData?.workspace?.name || 'Workspace'} • ${modalData?.period?.display || 'Periode'}`">
                                </p>
                            </div>
                        </div>
                        <button @click="showDSSModal = false" class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto px-6 py-6">

                        <!-- Performance Overview -->
                        <div class="mb-6">
                            <div class="grid grid-cols-3 gap-4">
                                <!-- Performance Score -->
                                <div
                                    class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white relative group">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium opacity-90">Performance</span>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>

                                    <!-- Tooltip -->
                                    <div
                                        class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10 shadow-xl">
                                        <div class="font-semibold mb-1">Skor Performa Keseluruhan</div>
                                        <div class="text-gray-300">Dihitung dari seberapa banyak tugas selesai tepat waktu,
                                            progress kerja tim, dan keterlambatan yang terjadi.</div>
                                        <div
                                            class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-8 border-r-8 border-b-8 border-transparent border-b-gray-900">
                                        </div>
                                    </div>

                                    <div class="flex items-end gap-2">
                                        <span class="text-4xl font-bold" x-text="modalData?.performance?.score || 0"></span>
                                        <span class="text-xl opacity-75 mb-1">/100</span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-1">
                                        <template x-for="i in 5" :key="i">
                                            <svg class="w-4 h-4"
                                                :class="i <= (modalData?.performance?.rating?.stars || 0) ? 'text-yellow-300' :
                                                    'text-white/30'"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </template>
                                        <span class="text-sm ml-2" x-text="modalData?.performance?.rating?.label"></span>
                                    </div>
                                </div>

                                <!-- Quality Score -->
                                <div
                                    class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-5 text-white relative group">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium opacity-90">Quality</span>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                        </svg>
                                    </div>

                                    <!-- Tooltip -->
                                    <div
                                        class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10 shadow-xl">
                                        <div class="font-semibold mb-1">Kualitas Penyelesaian</div>
                                        <div class="text-gray-300">Mengukur seberapa baik tim menyelesaikan tugas—apakah
                                            tepat waktu dan tidak banyak yang terlambat.</div>
                                        <div
                                            class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-8 border-r-8 border-b-8 border-transparent border-b-gray-900">
                                        </div>
                                    </div>

                                    <div class="flex items-end gap-2">
                                        <span class="text-4xl font-bold"
                                            x-text="modalData?.performance?.quality || 0"></span>
                                        <span class="text-xl opacity-75 mb-1">/100</span>
                                    </div>
                                    <div class="mt-2 text-sm">
                                        <span
                                            x-text="(modalData?.performance?.quality || 0) >= 80 ? 'Sangat Baik' : (modalData?.performance?.quality || 0) >= 60 ? 'Baik' : (modalData?.performance?.quality || 0) >= 40 ? 'Cukup' : 'Perlu Perbaikan'"></span>
                                    </div>
                                </div>

                                <!-- Risk Score -->
                                <div
                                    class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-5 text-white relative group">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium opacity-90">Risk Level</span>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                                        </svg>
                                    </div>

                                    <!-- Tooltip -->
                                    <div
                                        class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10 shadow-xl">
                                        <div class="font-semibold mb-1">Tingkat Risiko</div>
                                        <div class="text-gray-300"> Seberapa besar kemungkinan workspace gagal mencapai
                                            target.
                                            Dihitung dari tugas yang belum selesai: jumlah overdue, deadline mendesak,
                                            dan tugas yang menumpuk.</div>
                                        <div
                                            class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-8 border-r-8 border-b-8 border-transparent border-b-gray-900">
                                        </div>
                                    </div>

                                    <div class="flex items-end gap-2">
                                        <span class="text-4xl font-bold"
                                            x-text="modalData?.performance?.risk || 0"></span>
                                        <span class="text-xl opacity-75 mb-1">/100</span>
                                    </div>
                                    <div class="mt-2 text-sm">
                                        <span
                                            x-text="(modalData?.performance?.risk || 0) >= 80 ? '🔴 Sangat Tinggi' : (modalData?.performance?.risk || 0) >= 60 ? '🟠 Tinggi' : (modalData?.performance?.risk || 0) >= 40 ? '🟡 Sedang' : '🟢 Rendah'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Critical Issues with Actions -->
                        <div x-show="modalData?.suggestions?.critical && modalData.suggestions.critical.length > 0"
                            class="mb-6">
                            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-2xl">🔴</span>
                                    <h3 class="text-lg font-bold text-red-900">MASALAH YANG HARUS DIATASI SEGERA</h3>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="(issue, idx) in modalData.suggestions.critical"
                                        :key="idx">
                                        <div class="bg-white rounded-lg p-4 border border-red-200">
                                            <div class="flex items-start gap-3 mb-3">
                                                <div
                                                    class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                    <span class="text-red-700 font-bold text-sm"
                                                        x-text="`${idx + 1}`"></span>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 mb-1" x-text="issue.title"></h4>
                                                    <p class="text-sm text-gray-700 mb-2" x-text="issue.description"></p>

                                                    <!-- Value Badge -->
                                                    <div
                                                        class="inline-flex items-center gap-2 px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" />
                                                        </svg>
                                                        <span x-text="issue.value"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ✅ ACTIONS LANGSUNG DI SINI -->
                                            <div x-show="issue.actions && issue.actions.length > 0"
                                                class="mt-4 pl-11 border-l-4 border-blue-200">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-lg">💡</span>
                                                    <h5 class="text-sm font-bold text-gray-900">TINDAKAN:</h5>
                                                </div>
                                                <div class="space-y-2">
                                                    <template x-for="(action, aIdx) in issue.actions"
                                                        :key="aIdx">
                                                        <div class="flex items-start gap-2">
                                                            <input type="checkbox"
                                                                class="mt-1 w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                                            <span class="flex-1 text-sm text-gray-800"
                                                                x-text="action"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Warnings with Suggestions -->
                        <div x-show="modalData?.suggestions?.warning && modalData.suggestions.warning.length > 0"
                            class="mb-6">
                            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-2xl">🟡</span>
                                    <h3 class="text-lg font-bold text-yellow-900">HAL YANG PERLU DIPERHATIKAN</h3>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(warning, idx) in modalData.suggestions.warning"
                                        :key="idx">
                                        <div class="bg-white rounded-lg p-4 border border-yellow-200">
                                            <div class="flex items-start gap-3">
                                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-900 text-sm mb-1"
                                                        x-text="warning.title"></h4>
                                                    <p class="text-xs text-gray-600 mb-2" x-text="warning.description">
                                                    </p>

                                                    <!-- ✅ SUGGESTIONS LANGSUNG DI SINI -->
                                                    <div x-show="warning.suggestions && warning.suggestions.length > 0"
                                                        class="mt-3 pl-4 border-l-2 border-yellow-300">
                                                        <div class="flex items-center gap-1 mb-1">
                                                            <span class="text-sm">💡</span>
                                                            <h6 class="text-xs font-bold text-gray-700">SARAN:</h6>
                                                        </div>
                                                        <ul class="space-y-1">
                                                            <template x-for="(sug, sIdx) in warning.suggestions"
                                                                :key="sIdx">
                                                                <li class="text-xs text-gray-700 flex items-start gap-1">
                                                                    <span>•</span>
                                                                    <span x-text="sug"></span>
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-xs font-bold text-yellow-700 bg-yellow-100 px-2 py-1 rounded"
                                                    x-text="warning.value"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Urgent Tasks (Show 3, Expandable) -->
                        <div x-show="modalData?.urgent_tasks && modalData.urgent_tasks.length > 0" class="mb-6"
                            x-data="{ showAllUrgent: false }">
                            <div class="bg-white border border-gray-200 rounded-xl p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">📌</span>
                                        <h3 class="text-lg font-bold text-gray-900">TUGAS YANG HARUS DICEK HARI INI</h3>
                                    </div>
                                    <span class="text-sm text-gray-500"
                                        x-text="`${showAllUrgent ? modalData.urgent_tasks.length : Math.min(3, modalData.urgent_tasks.length)} dari ${modalData.urgent_tasks.length} tugas`">
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <!-- ✅ DEFAULT: Show 3 tasks -->
                                    <template
                                        x-for="(task, idx) in showAllUrgent ? modalData.urgent_tasks : modalData.urgent_tasks.slice(0, 3)"
                                        :key="task.id">
                                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer"
                                            @click="console.log('Open task:', task.id)">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-0.5 text-xs font-bold rounded"
                                                            :class="{
                                                                'bg-red-600 text-white animate-pulse': task
                                                                    .priority === 'overdue',
                                                                'bg-red-500 text-white': task.priority === 'urgent',
                                                                'bg-red-100 text-red-700': task
                                                                    .priority === 'high',
                                                                'bg-yellow-100 text-yellow-700': task
                                                                    .priority === 'medium',
                                                                'bg-gray-100 text-gray-700': task
                                                                    .priority === 'low'
                                                            }"
                                                            x-text="task.priority === 'overdue' ? 'TERLAMBAT' : task.priority?.toUpperCase() || 'MEDIUM'">
                                                        </span>
                                                        <span class="text-sm font-bold text-gray-900"
                                                            x-text="task.title"></span>
                                                    </div>

                                                    <div class="flex items-center gap-4 text-xs text-gray-600">
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" />
                                                            </svg>
                                                            <span
                                                                :class="{
                                                                    'text-red-600 font-bold': task.days_until_due < 0,
                                                                    'text-orange-600 font-semibold': task
                                                                        .days_until_due >= 0 && task.days_until_due <=
                                                                        1,
                                                                    'text-yellow-600': task.days_until_due > 1 && task
                                                                        .days_until_due <= 3
                                                                }"
                                                                x-text="task.days_until_due < 0 ? 
                                                                `Telat ${Math.floor(Math.abs(task.days_until_due))} hari ${Math.floor((Math.abs(task.days_until_due) % 1) * 24)} jam` : 
                                                                `H-${Math.floor(task.days_until_due)}`">
                                                            </span>
                                                        </span>
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                                <path fill-rule="evenodd"
                                                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                                            </svg>
                                                            <span x-text="`Progress: ${task.progress}%`"></span>
                                                        </span>
                                                    </div>

                                                    <!-- Assigned Users -->
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <span class="text-xs text-gray-500">Ditugaskan:</span>
                                                        <div class="flex -space-x-2">
                                                            <template x-for="user in task.assigned_users.slice(0, 3)"
                                                                :key="user.id">
                                                                <img :src="user.avatar" :alt="user.name"
                                                                    :title="user.name"
                                                                    class="w-8 h-8 rounded-full border-2 border-white">
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Tombol yang nanti ketikea dipencet membuak modal taks --}}
                                                {{-- <button
                                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1">
                                                    <span>Buka</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </button> --}}
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ✅ EXPAND BUTTON -->
                                <div x-show="modalData.urgent_tasks.length > 3" class="mt-4 text-center">
                                    <button @click="showAllUrgent = !showAllUrgent"
                                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2 mx-auto">
                                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showAllUrgent }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <span
                                            x-text="showAllUrgent ? 'Sembunyikan' : `Tampilkan ${modalData.urgent_tasks.length - 3} tugas lainnya`"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Workload with Recommendations -->
                        <div x-show="modalData?.workload && modalData.workload.length > 0" class="mb-6">
                            <div class="bg-white border border-gray-200 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-2xl">👥</span>
                                    <h3 class="text-lg font-bold text-gray-900">BEBAN KERJA ANGGOTA</h3>
                                </div>

                                <div class="space-y-4 mb-4">
                                    <template x-for="member in modalData.workload" :key="member.user_id">
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center gap-3 mb-3">
                                                <img :src="member.avatar" :alt="member.name"
                                                    class="w-10 h-10 rounded-full">
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-900" x-text="member.name"></h4>
                                                    <p class="text-xs text-gray-600"
                                                        x-text="`${member.total_tasks} tugas • ${member.completed_tasks} selesai • ${member.overdue_tasks} terlambat`">
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-2xl font-bold"
                                                        :class="{
                                                            'text-red-600': member.load_percentage > 100,
                                                            'text-green-600': member.load_percentage <= 80,
                                                            'text-yellow-600': member.load_percentage > 80 && member
                                                                .load_percentage <= 100
                                                        }"
                                                        x-text="`${member.load_percentage}%`">
                                                    </div>
                                                    <p class="text-xs text-gray-500">beban</p>
                                                </div>
                                            </div>

                                            <!-- Progress Bar -->
                                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                                <div class="h-full rounded-full transition-all"
                                                    :class="{
                                                        'bg-red-500': member.load_percentage > 100,
                                                        'bg-green-500': member.load_percentage <= 80,
                                                        'bg-yellow-500': member.load_percentage > 80 && member
                                                            .load_percentage <= 100
                                                    }"
                                                    :style="`width: ${Math.min(member.load_percentage, 100)}%`">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ✅ WORKLOAD RECOMMENDATIONS -->
                                <div x-show="modalData?.workload_recommendations && modalData.workload_recommendations.length > 0"
                                    class="mt-4 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-lg">💡</span>
                                        <h5 class="text-sm font-bold text-blue-900">REKOMENDASI:</h5>
                                    </div>
                                    <div class="space-y-2">
                                        <template x-for="(rec, rIdx) in modalData.workload_recommendations"
                                            :key="rIdx">
                                            <div class="flex items-start gap-2">
                                                <input type="checkbox"
                                                    class="mt-1 w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                                <span class="flex-1 text-sm text-gray-800" x-text="rec"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Items -->
                        {{-- <div x-show="modalData?.suggestions?.actions && modalData.suggestions.actions.length > 0"
                            class="mb-6">
                            <div class="bg-indigo-50 border-2 border-indigo-200 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-2xl">💬</span>
                                    <h3 class="text-lg font-bold text-indigo-900">KESIMPULAN & REKOMENDASI</h3>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(action, idx) in modalData.suggestions.actions"
                                        :key="idx">
                                        <div
                                            class="flex items-start gap-3 bg-white rounded-lg p-3 border border-indigo-200">
                                            <input type="checkbox"
                                                class="mt-1 w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                            <span class="flex-1 text-sm text-gray-800" x-text="action"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div> --}}

                        <!-- Positive Feedback -->
                        <div x-show="modalData?.suggestions?.positive && modalData.suggestions.positive.length > 0">
                            <div class="bg-green-50 border-2 border-green-200 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-2xl">🟢</span>
                                    <h3 class="text-lg font-bold text-green-900">HAL POSITIF</h3>
                                </div>

                                <div class="grid grid-cols-1 gap-3">
                                    <template x-for="(positive, idx) in modalData.suggestions.positive"
                                        :key="idx">
                                        <div
                                            class="bg-white rounded-lg p-4 border border-green-200 flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                            </svg>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-900 text-sm" x-text="positive.title">
                                                </h4>
                                                <p class="text-xs text-gray-600 mt-1" x-text="positive.description"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Diperbarui:</span>
                            <strong class="text-gray-700" x-text="formatTimestamp(modalData?.generated_at)"></strong>
                        </div>

                        <div class="flex items-center gap-3">


                            <!-- Close Button -->
                            {{-- <button @click="showDSSModal = false"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                Tutup
                            </button> --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End DSS Modal -->


        <!-- Saran Box dengan DSS - Modern UI -->
        <div class="px-12" x-show="selectedMember === null" x-transition>
            <div class="bg-white rounded-xl shadow-lg w-full mx-auto mt-2 mb-1 overflow-hidden border-2"
                :class="{
                    'border-red-500': topSuggestion?.type === 'critical',
                    'border-yellow-500': topSuggestion?.type === 'warning',
                    'border-green-500': topSuggestion?.type === 'positive',
                    'border-blue-500': topSuggestion?.type === 'neutral',
                    'border-gray-200': !topSuggestion
                }">

                <!-- Loading State -->
                <div x-show="loadingSuggestion" class="p-6">
                    <div class="flex items-center gap-3">
                        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Menganalisis data workspace...</span>
                    </div>
                </div>

                <!-- Content -->
                <div x-show="!loadingSuggestion">

                    <!-- Header Bar dengan Gradient -->
                    <div class="px-5 py-3 flex items-center justify-between"
                        :class="{
                            'bg-gradient-to-r from-red-500 to-red-600': topSuggestion?.type === 'critical',
                            'bg-gradient-to-r from-yellow-500 to-yellow-600': topSuggestion?.type === 'warning',
                            'bg-gradient-to-r from-green-500 to-green-600': topSuggestion?.type === 'positive',
                            'bg-gradient-to-r from-blue-500 to-blue-600': topSuggestion?.type === 'neutral',
                            'bg-gradient-to-r from-indigo-500 to-indigo-600': topSuggestion?.type === 'empty',
                            'bg-gradient-to-r from-gray-400 to-gray-500': !topSuggestion
                        }">

                        <!-- Label Status -->
                        <div class="flex items-center gap-2">
                            <span class="text-3xl"
                                x-text="topSuggestion?.type === 'critical' ? '🔴' :
                  topSuggestion?.type === 'warning' ? '🟡' :
                  topSuggestion?.type === 'positive' ? '🟢' : 
                  topSuggestion?.type === 'empty' ? '📋' : '💡'">
                            </span>
                            <div class="flex flex-col">
                                <span class="text-white text-md font-bold tracking-wide uppercase"
                                    x-text="topSuggestion?.type === 'critical' ? 'MASALAH KRITIS' :
                                    topSuggestion?.type === 'warning' ? 'PERLU PERHATIAN' :
                                    topSuggestion?.type === 'positive' ? 'PERFORMA BAGUS' : 
                                    topSuggestion?.type === 'empty' ? 'BELUM ADA DATA' : 'INFORMASI'">
                                </span>
                                <span class="text-white/80 text-s font-medium"
                                    x-show="topSuggestion?.type === 'critical' || topSuggestion?.type === 'warning'"
                                    x-text="topSuggestion?.type === 'critical' ? 'Butuh tindakan segera!' : 'Perlu ditingkatkan'">
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2">
                            <!-- Refresh Button -->
                            <button @click="refreshSnapshot()" :disabled="loadingSuggestion"
                                class="p-2 bg-white/20 hover:bg-white/30 rounded-lg transition-all disabled:opacity-50 group"
                                title="Perbarui analisis">
                                <svg class="w-4 h-4 text-white" :class="{ 'animate-spin': loadingSuggestion }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>

                            <!-- Detail Button - HIDE untuk empty state -->
                            <button @click="openSuggestionModal()"
                                x-show="topSuggestion && topSuggestion.data && topSuggestion.type !== 'empty'"
                                class="px-4 py-2 bg-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all flex items-center gap-2"
                                :class="{
                                    'text-red-600 hover:bg-red-50': topSuggestion?.type === 'critical',
                                    'text-yellow-600 hover:bg-yellow-50': topSuggestion?.type === 'warning',
                                    'text-green-600 hover:bg-green-50': topSuggestion?.type === 'positive',
                                    'text-blue-600 hover:bg-blue-50': topSuggestion?.type === 'neutral'
                                }">
                                <span
                                    x-text="topSuggestion?.type === 'critical' || topSuggestion?.type === 'warning' ? 
                      'Lihat Tindak Lanjut' : 'Lihat Detail'">
                                </span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="px-5 py-4">

                        <!-- Suggestion Content -->
                        <div x-show="topSuggestion && topSuggestion.data">
                            <!-- Title & Description -->
                            <div class="space-y-2">
                                <!-- Title dengan icon -->
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0"
                                        :class="{
                                            'text-red-500': topSuggestion?.type === 'critical',
                                            'text-yellow-500': topSuggestion?.type === 'warning',
                                            'text-green-500': topSuggestion?.type === 'positive',
                                            'text-blue-500': topSuggestion?.type === 'neutral',
                                            'text-indigo-500': topSuggestion?.type === 'empty'
                                        }"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <h3 class="text-base font-bold text-gray-900" x-text="topSuggestion.data.title"></h3>
                                </div>

                                <!-- Description with better spacing -->
                                <p class="text-sm text-gray-700 leading-relaxed pl-7"
                                    x-text="topSuggestion.data.description"></p>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!topSuggestion || !topSuggestion.data"
                            class="flex flex-col items-center justify-center py-6 text-gray-400">
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm font-medium">Belum ada analisis untuk periode ini</p>
                            <p class="text-xs mt-1">Klik tombol refresh untuk memulai analisis</p>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <div class="flex flex-col gap-2">

                            <div class="flex items-center justify-between">

                                <!-- Timestamp Info -->
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>diperbarui:</span>
                                    <strong class="text-gray-700" x-text="formatTimestamp(suggestionTimestamp)"></strong>
                                </div>

                                <!-- Status Badge -->
                                <div class="flex items-center gap-2">
                                    <span x-show="!isCached"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                        Live
                                    </span>

                                    <span x-show="isCached"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                                        <span class="w-1.5 h-1.5 bg-orange-500 rounded-full"></span>
                                        Cached
                                    </span>
                                </div>

                            </div>

                            <!-- Cached Hint -->
                            <p x-show="isCached" class="text-[11px] text-gray-600">
                                Data ini berasal dari cache. Refresh untuk melihat kondisi terbaru.
                            </p>

                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- Dashboard Container -->
        <div class="dashboard-container m">

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
                        <!-- Member/Workspace Info - CONDITIONAL -->
                        <!-- Member/Workspace Info - CONDITIONAL (More Compact) -->
                        <div style="display: flex; align-items: center; gap: 12px; padding: 4px 0; margin-bottom: 16px;">

                            <!-- Avatar/Icon -->
                            <template x-if="selectedMember === null">
                                <!-- Workspace Icon -->
                                <div
                                    style="width: 40px; height: 40px; background: #f3f4f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2">
                                        </rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                </div>
                            </template>

                            <template x-if="selectedMember !== null">
                                <!-- Member Avatar -->
                                <img :src="members.find(m => m.id === selectedMember)?.avatar || 'https://i.pravatar.cc/40'"
                                    :alt="members.find(m => m.id === selectedMember)?.name"
                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            </template>

                            <!-- Name & Subtitle (Conditional) -->
                            <div style="flex: 1; min-width: 0;">
                                <!-- Workspace Mode -->
                                <template x-if="selectedMember === null">
                                    <div>
                                        <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 2px 0;"
                                            x-text="workspaceData ? workspaceData.name : '{{ $defaultWorkspace->name }}'">
                                        </h3>
                                        <p style="font-size: 12px; color: #6b7280; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                            x-text="workspaceData ? workspaceData.description : '{{ $defaultWorkspace->description ?? 'Workspace aktif' }}'">
                                        </p>
                                    </div>
                                </template>

                                <!-- Member Mode -->
                                <template x-if="selectedMember !== null">
                                    <div>
                                        <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 2px 0;"
                                            x-text="members.find(m => m.id === selectedMember)?.name"></h3>
                                        <p style="font-size: 12px; color: #6b7280; margin: 0;">Member</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Kehadiran Rapat Info (Only show when member selected) -->
                        <div x-show="selectedMember !== null"
                            style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; background: #f3f4f6; border-radius: 8px; margin-bottom: 16px;">

                            <img src="{{ asset('images/icons/Attendance.svg') }}" width="18" height="18"
                                style="display: block;" alt="Attendance Icon" />

                            <span style="font-size: 13px; color: #6b7280;">Kehadiran Rapat:</span>
                            <!-- ✅ GANTI INI -->
                            <strong style="font-size: 13px; color: #111827; font-weight: 600;">
                                <span
                                    x-text="`Hadir ${memberAttendance?.attended || 0} dari ${memberAttendance?.total || 0} rapat`"></span>
                            </strong>

                            {{-- <!-- ✅ OPTIONAL: Tampilkan persentase -->
                            <span x-show="memberAttendance?.total > 0"
                                style="font-size: 11px; color: #6b7280; margin-left: auto;"
                                x-text="`(${memberAttendance?.percentage || 0}%)`">
                            </span> --}}
                        </div>


                        <!-- Periode Info (Always show) -->
                        <div
                            style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; background: #f3f4f6; border-radius: 8px; margin-bottom: 16px;">
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" :fill="rekapKinerja?.performance?.color || '#3b82f6'"
                                    stroke="none">
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
                                {{-- <span style="display: block; margin-top: 4px; font-size: 11px;">
                                    (Score: <span x-text="rekapKinerja?.performance?.score || 0"></span>/100)
                                </span> --}}
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
                                        <span style="font-size: 12px; color: #374151;">Selesai Tepat Waktu</span>
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
                    <div class="tasks-container"
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
