<div class="h-16 bg-white shadow-sm flex items-center px-6 justify-between border-b border-gray-200">
    <!-- Left Section: Logo & Company Name -->
    <div class="flex items-center gap-3">
        <img src="/images/logo.png" alt="Logo" class="w-6 h-6">
        <span class="text-gray-500 font-medium text-sm">PT. Mencari Cinta Sejati</span>
    </div>

    <!-- Center Section: Search Bar -->
    <div class="flex-1 max-w-md mx-8">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input 
                type="text" 
                placeholder="Cari ruang kerja, tugas..." 
                class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-300 focus:bg-white transition"
            >
        </div>
    </div>

    <!-- Right Section: Active Users & Action Buttons -->
    <div class="flex items-center gap-4">
        <!-- Active Users -->
        <div class="flex items-center gap-2">
            <div class="flex -space-x-2">
                <img src="https://i.pravatar.cc/32?img=1" alt="User 1" class="w-7 h-7 rounded-full border-2 border-white">
                <img src="https://i.pravatar.cc/32?img=2" alt="User 2" class="w-7 h-7 rounded-full border-2 border-white">
                <img src="https://i.pravatar.cc/32?img=3" alt="User 3" class="w-7 h-7 rounded-full border-2 border-white">
            </div>
<span class="text-xs text-gray-600">
    Sabron dan 
    <button 
        class="text-blue-500 hover:underline" 
        onclick="openModal()"
    >
        5 lainnya
    </button> 
    aktif
</span>        </div>

        <!-- Action Buttons -->
        <button class="p-1 hover:bg-gray-50 rounded-lg transition" title="Atur Akses">
            <img src="/images/icons/akses.png" alt="Atur Akses" class="w-5 h-5">
        </button>

        <button class="p-1 hover:bg-gray-50 rounded-lg transition relative" title="Notifications">
            <img src="/images/icons/dollar.png" alt="Notification" class="w-5 h-5">

            
        </button>

        <button class="p-1 hover:bg-gray-50 rounded-lg transition" title="Apps">
            <img src="/images/icons/apps.png" alt="Apps" class="w-5 h-5">
        </button>

        <button class="p-1 hover:bg-gray-50 rounded-lg transition" title="Apps">
            <img src="/images/icons/apps.png" alt="Apps" class="w-5 h-5">
        </button>

        <button class="p-1 hover:bg-gray-50 rounded-lg transition" title="Apps">
            <img src="/images/icons/notification.png" alt="Apps" class="w-5 h-5">
            
        </button>

        <!-- Profile -->
        <button class="w-8 h-8 rounded-full overflow-hidden border-2 border-gray-200 hover:border-blue-400 transition">
            <img src="https://i.pravatar.cc/32?img=8" alt="Profile" class="w-full h-full object-cover">
        </button>
    </div>
</div>