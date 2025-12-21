<div class="h-16 bg-white shadow-sm flex items-center px-6 justify-between border-b border-gray-200">
    <!-- Left Section: Logo & Company Name -->
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/logo-pt.svg') }}" alt="Logo PT" class="h-8 w-8">
        <span class="text-gray-600 font-medium whitespace-nowrap">
            {{ $activeCompany->name ?? 'Belum ada perusahaan' }}
        </span>
    </div>

    <!-- Center Section: Search Bar -->
    <div class="flex-1 max-w-md mx-6">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" placeholder="Cari ruang kerja, tugas..."
                class="w-full pl-10 pr-4 py-2 bg-[#E9EFFD] border-0 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:bg-white transition">
        </div>
    </div>

    <!-- Right Section: Active Users & Action Buttons -->
    <div class="flex items-center gap-3">

        <!-- Active Users Section -->
        <div class="flex items-center gap-2" x-data="activeUsersComponent" x-init="init('{{ $activeCompany->id ?? '' }}')">

            <!-- Loading State -->
            <template x-if="loading">
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-2">
                        <div class="w-7 h-7 rounded-full bg-gray-200 animate-pulse border-2 border-white"></div>
                        <div class="w-7 h-7 rounded-full bg-gray-200 animate-pulse border-2 border-white"></div>
                        <div class="w-7 h-7 rounded-full bg-gray-200 animate-pulse border-2 border-white"></div>
                    </div>
                    <span class="text-xs text-gray-400">Loading...</span>
                </div>
            </template>

            <!-- Active Users Display -->
            <template x-if="!loading && users.length > 0">
                <div class="flex items-center gap-2">
                    <!-- Avatar Stack -->
                    <div class="flex -space-x-2">
                        <template x-for="user in users.slice(0, 3)" :key="user.id">
                            <img :src="user.avatar" :alt="user.name"
                                :title="user.name + ' (' + user.role + ')'"
                                class="w-7 h-7 rounded-full border-2 border-white ring-1 ring-gray-200 hover:scale-110 transition-transform cursor-pointer object-cover">
                        </template>
                    </div>

                    <!-- Text Info -->
                    <span class="text-xs text-gray-600 whitespace-nowrap">
                        <span class="font-medium" x-text="users[0]?.name || 'User'"></span>
                        <template x-if="users.length > 1">
                            <span>
                                dan
                                <button @click="showAllUsers = true"
                                    class="text-blue-600 hover:text-blue-700 font-medium"
                                    x-text="(users.length - 1) + ' lainnya'">
                                </button>
                            </span>
                        </template>
                        <span>aktif</span>
                    </span>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!loading && users.length === 0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-400">Belum ada yang online</span>
                </div>
            </template>

            <!-- Modal Daftar Semua User -->
            <div x-show="showAllUsers" x-cloak @click.self="showAllUsers = false"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0"
                x-transition:enter-end="transform opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="transform opacity-100" x-transition:leave-end="transform opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

                <div class="bg-white rounded-2xl shadow-2xl w-[500px] max-h-[600px] overflow-hidden" @click.stop
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95">

                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <h3 class="text-lg font-bold text-gray-900">
                                Pengguna Aktif (<span x-text="users.length"></span>)
                            </h3>
                        </div>
                        <button @click="showAllUsers = false" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- User List -->
                    <div class="overflow-y-auto max-h-[500px] p-4">
                        <template x-for="user in users" :key="user.id">
                            <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                <div class="relative">
                                    <img :src="user.avatar" :alt="user.name"
                                        class="w-10 h-10 rounded-full border-2 border-white ring-2 ring-gray-200 object-cover">
                                    <!-- Online Indicator -->
                                    <span
                                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-semibold text-base text-gray-900 truncate" x-text="user.name">
                                        </h4>
                                        <!-- Role Badge dengan warna sesuai -->
                                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-bl-xl rounded-tr-xl"
                                            :class="{
                                                'bg-[#102A63] text-white': user.role === 'SuperAdmin' || user
                                                    .role === 'Super Admin',
                                                'bg-[#225AD6] text-white': user.role === 'Admin',
                                                'bg-[#DC2626] text-white': user.role === 'Administrator',
                                                'bg-[#0FA875] text-white': user.role === 'Manager',
                                                'bg-[#E4BA13] text-white': user.role === 'Member',
                                                'bg-gray-100 text-gray-700': !['SuperAdmin', 'Super Admin', 'Admin',
                                                    'Administrator', 'Manager', 'Member'
                                                ].includes(user.role)
                                            }"
                                            x-text="user.role">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State in Modal -->
                        <template x-if="users.length === 0">
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-gray-500 font-medium">Belum ada pengguna online</p>
                                <p class="text-sm text-gray-400 mt-1">Tunggu anggota lain untuk bergabung</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="h-6 w-px bg-gray-200"></div>

        <!-- Action Buttons -->
        <button class="p-1 hover:bg-gray-50 rounded-lg transition" title="Atur Akses"
            onclick="openAccessModal({ type: 'company' })">
            <img src="{{ asset('images/icons/akses.svg') }}" alt="Atur Akses" class="w-5 h-5">
        </button>

        <a href="{{ url('/pembayaran') }}" class="p-2 hover:bg-gray-100 rounded-lg transition" title="Dollar">
            <img src="{{ asset('images/icons/dollar.svg') }}" alt="Dollar" class="w-5 h-5">
        </a>

        <!-- Tombol Notifikasi -->
        <div class="relative" x-data="{ showNotifications: false }">
            <button @click="showNotifications = true" class="p-2 hover:bg-gray-100 rounded-lg transition relative"
                title="Notifikasi">
                <img src="{{ asset('images/icons/notifikasi.svg') }}" alt="Notifikasi" class="w-5 h-5">
                <!-- Unread Badge -->
                <span data-notification-badge
                    class="hidden absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">
                    0
                </span>
            </button>

            <!-- Modal Notifikasi -->
            <div x-show="showNotifications" @click.outside="showNotifications = false" x-transition
                class="absolute right-0 mt-3 z-50">

                <div class="bg-white rounded-xl shadow-xl w-[400px] max-h-[580px] border border-gray-200 flex flex-col"
                    @click.stop x-data="notificationModal()" x-init="init()"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95">

                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900">Notifikasi</h3>
                                <span x-show="unreadCount > 0"
                                    class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                                    x-text="unreadCount"></span>
                            </div>
                            <button @click="showNotifications = false"
                                class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Filter Tabs -->
                        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                            <template x-for="tab in tabs" :key="tab.id">
                                <button @click="activeTab = tab.id"
                                    class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all"
                                    :class="activeTab === tab.id ?
                                        'bg-[#225ad6] text-white shadow-md' :
                                        'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                    <span x-text="tab.label"></span>
                                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-bold"
                                        :class="activeTab === tab.id ? 'bg-white/20' : 'bg-gray-300'"
                                        x-text="getFilteredCount(tab.id)">
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Notification List -->
                    <div class="overflow-y-auto flex-1 max-h-[400px]">
                        <!-- Loading State -->
                        <template x-if="loading">
                            <div class="flex items-center justify-center py-12">
                                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#225ad6]"></div>
                            </div>
                        </template>

                        <!-- Notifications -->
                        <template x-if="!loading">
                            <div>
                                <template x-for="notif in filteredNotifications" :key="notif.id">
                                    <div class="border-b border-gray-100 hover:bg-gray-50 transition group"
                                        :class="!notif.is_read ? 'bg-blue-50/30' : ''">
                                        <div class="px-6 py-4 flex items-start gap-4">
                                            <!-- Icon dengan badge type -->
                                            <div class="relative flex-shrink-0">
                                                <img :src="notif.avatar" :alt="notif.user_name"
                                                    class="w-11 h-11 rounded-full object-cover border-2 border-white ring-2 ring-gray-200">
                                                <!-- Type Badge -->
                                                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center border-2 border-white"
                                                    :class="{
                                                        'bg-blue-500': notif.type === 'chat',
                                                        'bg-green-500': notif.type === 'task',
                                                        'bg-purple-500': notif.type === 'schedule',
                                                        'bg-orange-500': notif.type === 'announcement'
                                                    }">
                                                    <svg class="w-3 h-3 text-white" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <!-- Chat Icon -->
                                                        <path x-show="notif.type === 'chat'"
                                                            d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" />
                                                        <!-- Task Icon -->
                                                        <path x-show="notif.type === 'task'"
                                                            d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                                        <!-- Schedule Icon -->
                                                        <path x-show="notif.type === 'schedule'"
                                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" />
                                                        <!-- Announcement Icon -->
                                                        <path x-show="notif.type === 'announcement'"
                                                            d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" />
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 min-w-0" @click="handleNotificationClick(notif)">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="flex-1 cursor-pointer">
                                                        <p class="font-semibold text-gray-900 text-sm leading-tight"
                                                            x-text="notif.title"></p>
                                                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2"
                                                            x-text="notif.message"></p>
                                                        <p class="text-xs text-gray-400 mt-1" x-text="notif.context">
                                                        </p>
                                                        <p class="text-xs text-gray-400 mt-2" x-text="notif.time"></p>
                                                    </div>

                                                    <!-- Unread Indicator & Delete -->
                                                    <div class="flex items-center gap-2">
                                                        <template x-if="!notif.is_read">
                                                            <span
                                                                class="w-2.5 h-2.5 bg-[#225ad6] rounded-full flex-shrink-0"></span>
                                                        </template>

                                                        <!-- Delete Button (show on hover) -->
                                                        <button @click.stop="deleteNotification(notif.id)"
                                                            class="opacity-0 group-hover:opacity-100 p-1 hover:bg-red-100 rounded transition-all"
                                                            title="Hapus notifikasi">
                                                            <svg class="w-4 h-4 text-red-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State -->
                                <template x-if="filteredNotifications.length === 0">
                                    <div class="text-center py-12 px-6">
                                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <p class="text-gray-500 font-medium"
                                            x-text="activeTab === 'all' ? 'Belum ada notifikasi' : 'Belum ada notifikasi ' + tabs.find(t => t.id === activeTab)?.label.toLowerCase()">
                                        </p>
                                        <p class="text-sm text-gray-400 mt-1">Notifikasi baru akan muncul di sini</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 space-y-2">
                        <button @click="markAllAsRead()"
                            class="w-full py-2 text-sm font-semibold text-[#225ad6]
                               hover:bg-blue-50 rounded-lg transition">
                            Tandai Semua Sudah Dibaca
                        </button>

                        <button @click="clearReadNotifications()"
                            class="w-full py-2 text-sm font-semibold text-red-600
                               hover:bg-red-50 rounded-lg transition">
                            Hapus Notifikasi yang Sudah Dibaca
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Add this to your layout head -->
        <style>
            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }

            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        </style>

        <script>
            // ============================================
            // Alpine.js Component untuk Notification Modal
            // ============================================

            function notificationModal() {
                return {
                    activeTab: 'all',
                    loading: true,
                    tabs: [{
                            id: 'all',
                            label: 'Semua'
                        },
                        {
                            id: 'chat',
                            label: 'Chat'
                        },
                        {
                            id: 'task',
                            label: 'Tugas'
                        },
                        {
                            id: 'schedule',
                            label: 'Jadwal'
                        },
                        {
                            id: 'announcement',
                            label: 'Pengumuman'
                        }
                    ],
                    notifications: [],
                    unreadCount: 0,
                    userId: null,
                    echoChannel: null,

                    async init() {
                        console.log('🔔 Initializing notification modal...');

                        // Get user ID from Laravel
                        this.userId = window.Laravel?.userId;

                        if (!this.userId) {
                            console.error('❌ User ID not found');
                            this.loading = false;
                            return;
                        }

                        console.log('✅ User ID:', this.userId);

                        // Load notifications from server
                        await this.loadNotifications();

                        // Subscribe to real-time notifications
                        this.subscribeToNotifications();

                        // Update unread count
                        this.updateUnreadCount();
                    },

                    async loadNotifications() {
                        try {
                            this.loading = true;
                            console.log('📡 Loading notifications from server...');

                            const response = await fetch('/notifications', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();

                            console.log('📦 Response:', data);

                            if (data.success) {
                                // ✅ Pastikan setiap notification memiliki ID yang valid
                                this.notifications = (data.notifications || []).filter(n => n && n.id);
                                console.log('✅ Loaded notifications:', this.notifications.length);
                            } else {
                                console.warn('⚠️ Failed to load notifications:', data.message);
                                this.notifications = [];
                            }
                        } catch (error) {
                            console.error('❌ Error loading notifications:', error);
                            this.notifications = [];
                        } finally {
                            this.loading = false;
                        }
                    },

                    subscribeToNotifications() {
                        if (!window.Echo) {
                            console.error('❌ Laravel Echo not initialized');
                            return;
                        }

                        if (!this.userId) {
                            console.error('❌ Cannot subscribe: User ID not found');
                            return;
                        }

                        try {
                            console.log('🔔 Subscribing to notifications for user:', this.userId);

                            // Subscribe ke private channel user
                            this.echoChannel = window.Echo.private(`user.${this.userId}`)
                                .listen('.notification.sent', (data) => {
                                    console.log('📬 New notification received:', data);

                                    // ✅ Validasi data notification
                                    if (!data || !data.id) {
                                        console.error('❌ Invalid notification data:', data);
                                        return;
                                    }

                                    // ✅ Cek apakah notification sudah ada (prevent duplicate)
                                    const exists = this.notifications.find(n => n.id === data.id);
                                    if (exists) {
                                        console.warn('⚠️ Notification already exists:', data.id);
                                        return;
                                    }

                                    // Add notification ke list di awal
                                    const newNotification = {
                                        id: data.id,
                                        type: data.type || 'chat',
                                        title: data.title || 'Notifikasi Baru',
                                        message: data.message || '',
                                        context: data.context || '',
                                        user_name: data.actor?.name || data.actor?.full_name || 'System',
                                        avatar: data.actor?.avatar || this.getDefaultAvatar(data.actor?.name || data
                                            .actor?.full_name),
                                        is_read: false,
                                        action_url: data.action_url || null,
                                        time: 'Baru saja',
                                        created_at: data.created_at || new Date().toISOString()
                                    };

                                    this.notifications.unshift(newNotification);

                                    // Update unread count
                                    this.updateUnreadCount();

                                    // Show toast notification
                                    this.showToast(newNotification.title, newNotification.message);

                                    // Play sound (optional)
                                    this.playNotificationSound();
                                })
                                .error((error) => {
                                    console.error('❌ Error subscribing to notifications:', error);
                                });

                            console.log('✅ Successfully subscribed to notification channel');
                        } catch (error) {
                            console.error('❌ Failed to subscribe to notifications:', error);
                        }
                    },

                    get filteredNotifications() {
                        if (this.activeTab === 'all') {
                            return this.notifications || [];
                        }
                        return (this.notifications || []).filter(n => n && n.type === this.activeTab);
                    },

                    getFilteredCount(tabId) {
                        if (tabId === 'all') {
                            return (this.notifications || []).length;
                        }
                        return (this.notifications || []).filter(n => n && n.type === tabId).length;
                    },

                    updateUnreadCount() {
                        this.unreadCount = (this.notifications || []).filter(n => n && !n.is_read).length;

                        // Update badge di navbar
                        const badge = document.querySelector('[data-notification-badge]');
                        if (badge) {
                            if (this.unreadCount > 0) {
                                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }

                        console.log('🔢 Unread count:', this.unreadCount);
                    },

                    async markAsRead(notificationId) {
                        try {
                            console.log('📖 Marking as read:', notificationId);

                            const response = await fetch(`/notifications/${notificationId}/read`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Update local notification
                                const notification = this.notifications.find(n => n.id === notificationId);
                                if (notification) {
                                    notification.is_read = true;
                                }

                                this.updateUnreadCount();
                                console.log('✅ Notification marked as read');
                            } else {
                                console.error('❌ Failed to mark as read:', data.message);
                            }
                        } catch (error) {
                            console.error('❌ Error marking notification as read:', error);
                        }
                    },

                    async markAllAsRead() {
                        try {
                            console.log('📖 Marking all as read...');

                            const response = await fetch('/notifications/read-all', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Update all notifications locally
                                this.notifications = (this.notifications || []).map(n => ({
                                    ...n,
                                    is_read: true
                                }));

                                this.updateUnreadCount();
                                console.log('✅ All notifications marked as read');
                            } else {
                                console.error('❌ Failed to mark all as read:', data.message);
                            }
                        } catch (error) {
                            console.error('❌ Error marking all as read:', error);
                        }
                    },

                    async deleteNotification(notificationId) {
                        if (!confirm('Hapus notifikasi ini?')) {
                            return;
                        }

                        try {
                            console.log('🗑️ Deleting notification:', notificationId);

                            const response = await fetch(`/notifications/${notificationId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Remove from local list
                                this.notifications = (this.notifications || []).filter(n => n.id !== notificationId);
                                this.updateUnreadCount();
                                console.log('✅ Notification deleted');
                            } else {
                                console.error('❌ Failed to delete notification:', data.message);
                            }
                        } catch (error) {
                            console.error('❌ Error deleting notification:', error);
                        }
                    },

                    async clearReadNotifications() {
                        if (!confirm('Hapus semua notifikasi yang sudah dibaca?')) {
                            return;
                        }

                        try {
                            console.log('🗑️ Clearing read notifications...');

                            const response = await fetch('/notifications/clear-read', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Remove read notifications from local list
                                this.notifications = (this.notifications || []).filter(n => !n.is_read);
                                console.log('✅ Read notifications cleared');
                            } else {
                                console.error('❌ Failed to clear notifications:', data.message);
                            }
                        } catch (error) {
                            console.error('❌ Error clearing notifications:', error);
                        }
                    },

                    async handleNotificationClick(notification) {
                        console.log('👆 Notification clicked:', notification);

                        // Mark as read
                        if (!notification.is_read) {
                            await this.markAsRead(notification.id);
                        }

                        // Redirect to action URL
                        if (notification.action_url) {
                            window.location.href = notification.action_url;
                        }
                    },

                    showToast(title, message) {
                        // Check if we can show browser notification
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification(title, {
                                body: message,
                                icon: '/images/logo-pt.svg',
                                badge: '/images/logo-pt.svg'
                            });
                        }

                        console.log('🔔 Toast:', title, message);
                    },

                    playNotificationSound() {
                        // Optional: Play notification sound
                        try {
                            const audio = new Audio('/sounds/notification.mp3');
                            audio.volume = 0.3;
                            audio.play().catch(err => {
                                // Sound file not found or can't play, just ignore
                                console.log('Could not play sound (this is okay)');
                            });
                        } catch (error) {
                            // Sound file not found, ignore
                        }
                    },

                    getDefaultAvatar(name) {
                        const displayName = name || 'User';
                        return `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=4F46E5&color=fff&bold=true`;
                    },

                    destroy() {
                        // Cleanup when component is destroyed
                        if (this.echoChannel && this.userId) {
                            console.log('🔌 Leaving notification channel...');
                            window.Echo.leave(`user.${this.userId}`);
                            this.echoChannel = null;
                        }
                    }
                }
            }

            // ============================================
            // Request Browser Notification Permission
            // ============================================

            document.addEventListener('DOMContentLoaded', function() {
                console.log('📱 Initializing notifications...');

                // Request notification permission
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission().then(permission => {
                        console.log('🔔 Notification permission:', permission);
                    });
                }

                // Log Echo status
                if (window.Echo) {
                    console.log('✅ Laravel Echo is ready');
                } else {
                    console.error('❌ Laravel Echo is not initialized');
                }

                // Log user info
                if (window.Laravel) {
                    console.log('👤 User ID:', window.Laravel.userId);
                } else {
                    console.error('❌ Laravel object not found');
                }
            });
        </script>





        @php
            // ✅ Check apakah user adalah Super Admin
            $isSuperAdmin = false;
            if ($activeCompany) {
                $userCompany = Auth::user()
                    ->userCompanies()
                    ->where('company_id', $activeCompany->id)
                    ->with('role')
                    ->first();

                $isSuperAdmin =
                    $userCompany &&
                    $userCompany->role &&
                    in_array($userCompany->role->name, ['SuperAdmin', 'Super Admin']);
            }
        @endphp

        <!-- Button Perusahaan dengan Alpine.js -->
        <div class="relative" x-data="{ openCompany: false }">
            <button @click="openCompany = !openCompany" class="p-2 hover:bg-gray-100 rounded-lg transition"
                title="Perusahaan">
                <img src="{{ asset('images/icons/kantor.svg') }}" alt="Perusahaan" class="w-5 h-5">
            </button>

            <!-- Pop-up Ganti Perusahaan -->
            <div x-show="openCompany" @click.away="openCompany = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-200 z-50"
                style="display: none;">

                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Beralih perusahaan</h3>
                </div>

                <!-- List Perusahaan di Dropdown -->
                <div class="py-2 max-h-96 overflow-y-auto">
                    @forelse($companies as $company)
                        @php
                            // ✅ Check Super Admin untuk setiap company
                            $userCompanyItem = Auth::user()
                                ->userCompanies()
                                ->where('company_id', $company->id)
                                ->with('role')
                                ->first();

                            $isSuperAdminForCompany =
                                $userCompanyItem &&
                                $userCompanyItem->role &&
                                in_array($userCompanyItem->role->name, ['SuperAdmin', 'Super Admin']);
                        @endphp

                        <div x-data="{ showModal: false, showConfirm: false }" class="relative">
                            {{-- Wrapper baris perusahaan --}}
                            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition group">
                                {{-- Nama perusahaan (klik untuk switch) --}}
                                <a href="{{ route('company.switch', $company->id) }}"
                                    class="flex items-center gap-3 flex-1">
                                    <div
                                        class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $company->name }}</span>
                                </a>

                                {{-- Tombol pengaturan + centang --}}
                                <div class="flex items-center gap-2 ml-3">
                                    {{-- ✅ HANYA TAMPIL UNTUK SUPER ADMIN --}}
                                    @if ($isSuperAdminForCompany)
                                        <button type="button" @click.stop="showModal = true"
                                            class="hover:opacity-80 transition" title="Edit Perusahaan (Super Admin)">
                                            <img src="{{ asset('images/icons/pengaturan.svg') }}" alt="Pengaturan"
                                                class="w-5 h-5 cursor-pointer">
                                        </button>
                                    @endif

                                    @if ($activeCompany && $company->id == $activeCompany->id)
                                        <img src="{{ asset('images/icons/centang.svg') }}" alt="Active"
                                            class="w-5 h-5">
                                    @endif
                                </div>
                            </div>

                            {{-- ✅ MODAL HANYA BISA DIBUKA JIKA SUPER ADMIN --}}
                            @if ($isSuperAdminForCompany)
                                {{-- Modal Edit Perusahaan --}}
                                <div x-show="showModal"
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                                    x-transition>
                                    <div
                                        class="bg-gradient-to-br from-[#f4f7ff] to-[#e9f0ff] rounded-2xl shadow-2xl w-[520px] p-8 relative border border-white/30">

                                        {{-- Tombol Close --}}
                                        <button @click="showModal = false"
                                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>

                                        {{-- Badge Super Admin --}}
                                        <div class="flex justify-center mb-2">
                                            <span
                                                class="px-3 py-1 bg-purple-600 text-white text-xs font-semibold rounded-full">
                                                🔑 Super Admin Access
                                            </span>
                                        </div>

                                        {{-- Gambar Header --}}
                                        <div class="flex justify-center mb-6">
                                            <img src="{{ asset('images/pengaturan-perusahaan.svg') }}" alt="Kantor"
                                                class="w-64 h-auto drop-shadow-md">
                                        </div>

                                        {{-- Form Edit --}}
                                        <form action="{{ route('company.update', $company->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="mb-6">
                                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                                    Nama perusahaan
                                                </label>
                                                <input type="text" name="name" value="{{ $company->name }}"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-[#225ad6] focus:border-[#225ad6] shadow-sm transition">
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <button type="submit"
                                                    class="bg-[#2563EB] text-white px-5 py-2.5 rounded-lg hover:bg-[#1d4cc1] shadow-sm transition">
                                                    Simpan
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <button type="button"
                                                    class="flex items-center gap-2 bg-[#b7791f] hover:bg-[#695609] text-white px-4 py-2.5 rounded-lg transition shadow-sm"
                                                    @click="showConfirm = true">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m5 0H6" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </form>

                                        {{-- Konfirmasi Hapus --}}
                                        <div x-show="showConfirm"
                                            class="absolute right-6 bottom-24 bg-white border border-gray-200 rounded-xl shadow-xl p-4 w-64 transition-all duration-200"
                                            x-transition>
                                            <p class="font-semibold text-gray-800 mb-1">Hapus perusahaan?</p>
                                            <p class="text-sm text-gray-500 mb-4 leading-snug">
                                                Perusahaan akan dihapus dan semua datanya akan hilang selamanya.
                                            </p>
                                            <div class="flex justify-end gap-2">
                                                <button @click="showConfirm = false"
                                                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                                    Batal
                                                </button>
                                                <form action="{{ route('company.destroy', $company->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="px-3 py-1.5 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">
                                                        Ya, hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                            Belum ada perusahaan
                        </div>
                    @endforelse
                </div>

                <!-- Footer - Tambah Perusahaan -->
                <div class="border-t border-gray-200">
                    <a href="{{ url('buat-perusahaan') }}"
                        class="flex items-center gap-3 px-4 py-3 w-full text-left hover:bg-gray-50 transition">
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Tambah perusahaan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false"
                class="rounded-full overflow-hidden border-2 border-gray-200 hover:border-[#225ad6] transition">
                <img src="{{ $avatar }}" alt="{{ $user->name }}"
                    class="w-8 h-8 rounded-full object-cover border border-gray-300">
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                style="display: none;">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 text-center">Profil</h3>
                </div>

                <!-- User Info -->
                <div class="px-6 py-5">
                    <div class="flex items-center gap-3">
                        <img src="{{ $avatar }}" alt="{{ $user->name }}"
                            class="w-10 h-10 rounded-full object-cover border border-gray-300 flex-shrink-0">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-semibold text-gray-900 text-base">{{ $user->full_name }}</h4>
                            </div>
                            <span
                                class="inline-block bg-[#225ad6] text-white text-xs font-semibold px-2.5 py-1 rounded mt-1">
                                {{ $user->getRoleName($activeCompany->id) ?? 'Tanpa Role' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="border-t border-gray-200">
                    <a href="{{ url('profile') }}"
                        class="flex items-center gap-3 px-6 py-3.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="font-medium">Ubah profile</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-3 px-6 py-3.5 text-sm text-gray-700 hover:bg-gray-50 transition w-full text-left">
                            <div
                                class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="font-medium">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('activeUsersComponent', () => ({
                users: [],
                loading: true,
                showAllUsers: false,
                channel: null,
                companyId: null,

                init(companyId) {
                    this.companyId = companyId;

                    if (!companyId) {
                        console.warn('⚠️ No active company selected');
                        this.loading = false;
                        return;
                    }

                    console.log('🚀 Subscribing to company:', companyId);
                    this.subscribeToPresenceChannel(companyId);
                },

                // ✅ Helper function untuk memproses avatar URL
                processAvatarUrl(user) {
                    if (!user.avatar) {
                        // Jika tidak ada avatar, gunakan UI Avatars
                        return `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=4F46E5&color=fff&bold=true`;
                    }

                    // Jika sudah URL lengkap (http/https), return as is
                    if (user.avatar.startsWith('http://') || user.avatar.startsWith('https://')) {
                        return user.avatar;
                    }

                    // Jika path relatif, tambahkan base URL
                    // Pastikan tidak ada double slash
                    const cleanPath = user.avatar.startsWith('/') ? user.avatar : `/${user.avatar}`;

                    // Cek apakah path sudah mengandung 'storage/' atau tidak
                    if (user.avatar.includes('storage/')) {
                        return `${window.location.origin}${cleanPath}`;
                    } else {
                        return `${window.location.origin}/storage${cleanPath}`;
                    }
                },

                subscribeToPresenceChannel(companyId) {
                    try {
                        // Join presence channel
                        this.channel = window.Echo.join(`presence-company.${companyId}`)
                            .here((users) => {
                                // ✅ Process avatar untuk setiap user
                                console.log('✅ Users currently online:', users);
                                this.users = users.map(user => ({
                                    ...user,
                                    avatar: this.processAvatarUrl(user)
                                }));
                                this.loading = false;
                            })
                            .joining((user) => {
                                console.log('👋 User joined:', user);

                                // ✅ Process avatar sebelum push
                                const processedUser = {
                                    ...user,
                                    avatar: this.processAvatarUrl(user)
                                };

                                // Cek apakah user sudah ada di list (prevent duplicate)
                                const exists = this.users.find(u => u.id === processedUser.id);
                                if (!exists) {
                                    this.users.push(processedUser);
                                }
                            })
                            .leaving((user) => {
                                console.log('👋 User left:', user);
                                this.users = this.users.filter(u => u.id !== user.id);
                            })
                            .error((error) => {
                                console.error('❌ Presence channel error:', error);
                                this.loading = false;
                            });

                    } catch (error) {
                        console.error('❌ Failed to subscribe:', error);
                        this.loading = false;
                    }
                },

                destroy() {
                    // Cleanup saat component di-destroy
                    if (this.channel && this.companyId) {
                        console.log('🔌 Leaving presence channel...');
                        window.Echo.leave(`presence-company.${this.companyId}`);
                        this.channel = null;
                    }
                }
            }));
        });
    </script>
@endpush
