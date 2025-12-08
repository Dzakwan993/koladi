<div id="modalPilihPaket"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm font-[Inter,sans-serif]">
    <div class="bg-white rounded-2xl shadow-xl w-[90%] max-w-6xl p-8 relative max-h-[90vh] overflow-y-auto">
        <!-- Tombol Close -->
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Header -->
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-left">Pilih Paket</h2>

        <!-- Loading State -->
        <div id="loadingPlans" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#4A63E7] mx-auto"></div>
            <p class="text-gray-500 mt-4">Memuat paket...</p>
        </div>

        <!-- Kartu Paket -->
        <div id="plansContainer" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 hidden"></div>

        <!-- Info Tambahan -->
        <div id="planInfo" class="hidden space-y-2 text-sm text-gray-600 mb-6">
            <p>*Harga untuk 1 perusahaan</p>
            <p>*Untuk setiap penambahan 1 user dikenakan biaya <span id="addonPriceInfo" class="font-semibold">Rp
                    4000.000</span> / bulan</p>
        </div>

        <!-- Form Addon & Total -->
        <div id="addonSection" class="border-t pt-6 hidden">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Tambah User (Opsional)</h3>

            <div class="max-w-3xl mx-auto space-y-6">
                <!-- Input Addon -->
                <div class="flex items-center gap-4">
                    <input type="number" id="addonUserCount" min="0" value="0"
                        class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-[#4A63E7] focus:border-[#4A63E7]"
                        oninput="calculateTotal()" placeholder="Jumlah user tambahan">
                    <p class="text-sm text-gray-600 whitespace-nowrap">
                        Biaya: <span id="addonPrice" class="font-semibold">Rp 4.000</span>/user/bulan
                    </p>
                </div>

                <!-- Summary Card -->
                <div class="bg-gray-50 rounded-xl p-6 space-y-3">
                    <div class="flex justify-between items-center text-base">
                        <span class="text-gray-700">Paket:</span>
                        <span id="summaryPlan" class="font-semibold text-gray-900">-</span>
                    </div>
                    <div class="flex justify-between items-center text-base">
                        <span class="text-gray-700">Addon (<span id="addonQty">0</span> user):</span>
                        <span id="summaryAddon" class="font-semibold text-gray-900">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-base border-t pt-3">
                        <span class="text-gray-700">Total User:</span>
                        <span id="totalUsers" class="font-bold text-gray-900 text-lg">0</span>
                    </div>
                    <div class="flex justify-between items-center text-xl font-bold border-t-2 pt-4">
                        <span class="text-gray-900">Total Bayar:</span>
                        <span id="totalPrice" class="text-[#4A63E7]">Rp 0</span>
                    </div>
                </div>

                <!-- Button Bayar -->
                <button onclick="proceedPayment()" id="btnProceed"
                    class="w-full bg-[#4A63E7] text-white font-bold py-4 rounded-xl hover:bg-[#3a4fc7] transition disabled:bg-gray-300 disabled:cursor-not-allowed text-lg shadow-lg hover:shadow-xl">
                    <span id="btnText">Pilih Paket</span>
                    <span id="btnLoading" class="hidden flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let plans = [];
    let addon = null;
    let selectedPlan = null;
    let plansLoaded = false; // ✅ Flag untuk cek apakah sudah di-load

    function openModal() {
        const modal = document.getElementById('modalPilihPaket');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // ✅ Hanya load jika belum pernah di-load
        if (!plansLoaded) {
            loadPlans();
        } else {
            // Langsung tampilkan jika sudah ada di memory
            document.getElementById('loadingPlans').classList.add('hidden');
            document.getElementById('plansContainer').classList.remove('hidden');
            document.getElementById('planInfo').classList.remove('hidden');
        }
    }

    function closeModal() {
        const modal = document.getElementById('modalPilihPaket');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        resetModal();
    }

    async function loadPlans() {
        if (plansLoaded && plans.length > 0) {
            console.log('⚡ Using preloaded plans');
            renderPlans();
            updateAddonInfo();

            document.getElementById('loadingPlans').classList.add('hidden');
            document.getElementById('plansContainer').classList.remove('hidden');
            document.getElementById('planInfo').classList.remove('hidden');
            return;
        }

        console.log('🔄 Fetching plans from API...');
        try {
            // ✅ Tambahkan cache busting
            const timestamp = new Date().getTime();
            const response = await fetch('/api/plans?_=' + timestamp);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            plans = data.plans;
            addon = data.addon;
            plansLoaded = true;

            renderPlans();
            updateAddonInfo();

            document.getElementById('loadingPlans').classList.add('hidden');
            document.getElementById('plansContainer').classList.remove('hidden');
            document.getElementById('planInfo').classList.remove('hidden');
        } catch (error) {
            console.error('❌ Error loading plans:', error);
            document.getElementById('loadingPlans').innerHTML = `
            <div class="text-red-500 text-center">
                <p class="mb-4 font-semibold">Gagal memuat paket</p>
                <p class="text-sm mb-4">${error.message}</p>
                <button onclick="retryLoadPlans()" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Coba Lagi
                </button>
            </div>
        `;
        }
    }
    // ✅ Function untuk retry
    function retryLoadPlans() {
        document.getElementById('loadingPlans').innerHTML = `
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#4A63E7] mx-auto"></div>
        <p class="text-gray-500 mt-4">Memuat paket...</p>
    `;
        plansLoaded = false;
        loadPlans();
    }

    function renderPlans() {
        const container = document.getElementById('plansContainer');
        const fragment = document.createDocumentFragment(); // ✅ Gunakan fragment untuk performa

        plans.forEach(plan => {
            const card = document.createElement('div');
            card.className =
                'bg-[#E8EFFE] rounded-2xl p-6 cursor-pointer transition-all hover:shadow-xl border-2 border-transparent plan-card';
            card.setAttribute('data-plan-id', plan.id);

            let features = [
                `Dapat ${plan.base_user_limit} User`,
                'Penyimpanan Unlimited',
                'Akses seluruh fitur',
                'Tim & Proyek tanpa batas'
            ];

            card.innerHTML = `
            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">${plan.plan_name.replace('Paket ', '')}</h3>
                <p class="text-lg text-gray-700">Rp. ${formatNumber(plan.price_monthly)} / bulan</p>
            </div>
            <ul class="space-y-3 mb-6">
                ${features.map(feature => `
                    <li class="flex items-start gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>${feature}</span>
                    </li>
                `).join('')}
            </ul>
            <button class="w-full bg-[#3B4EC5] text-white font-semibold py-3 rounded-xl hover:bg-[#2d3a9f] transition">
                Pilih
            </button>
        `;

            card.addEventListener('click', () => selectPlan(plan.id));
            fragment.appendChild(card);
        });

        container.innerHTML = ''; // Clear dulu
        container.appendChild(fragment); // ✅ Append sekaligus (lebih cepat)
    }

    function selectPlan(planId) {
        selectedPlan = plans.find(p => p.id === planId);

        if (!selectedPlan) {
            console.error('Plan not found!');
            return;
        }

        // Highlight selected plan
        document.querySelectorAll('.plan-card').forEach(card => {
            if (card.dataset.planId === planId) {
                card.classList.add('border-[#4A63E7]', 'shadow-xl', 'scale-105');
            } else {
                card.classList.remove('border-[#4A63E7]', 'shadow-xl', 'scale-105');
            }
        });

        // Show addon section dengan smooth scroll
        const addonSection = document.getElementById('addonSection');
        addonSection.classList.remove('hidden');

        // ✅ Smooth scroll ke addon section
        setTimeout(() => {
            addonSection.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }, 100);

        calculateTotal();
    }

    function updateAddonInfo() {
        if (addon) {
            const price = formatNumber(addon.price_per_user);
            document.getElementById('addonPrice').textContent = `Rp ${price}`;
            document.getElementById('addonPriceInfo').textContent = `Rp${price}`;
        }
    }

    function calculateTotal() {
        if (!selectedPlan) return;

        const addonCount = parseInt(document.getElementById('addonUserCount').value) || 0;
        const planPrice = parseFloat(selectedPlan.price_monthly);
        const addonPrice = addon ? parseFloat(addon.price_per_user) * addonCount : 0;
        const totalPrice = planPrice + addonPrice;
        const totalUsers = parseInt(selectedPlan.base_user_limit) + addonCount;

        document.getElementById('summaryPlan').textContent =
        `${selectedPlan.plan_name} - Rp ${formatNumber(planPrice)}`;
        document.getElementById('addonQty').textContent = addonCount;
        document.getElementById('summaryAddon').textContent = `Rp ${formatNumber(addonPrice)}`;
        document.getElementById('totalUsers').textContent = totalUsers;
        document.getElementById('totalPrice').textContent = `Rp ${formatNumber(totalPrice)}`;
    }

    async function proceedPayment() {
        if (!selectedPlan) {
            alert('Silakan pilih paket terlebih dahulu');
            return;
        }

        const addonCount = parseInt(document.getElementById('addonUserCount').value) || 0;
        const btnProceed = document.getElementById('btnProceed');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');

        btnProceed.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');

        try {
            const companyId = '{{ $company->id ?? '' }}';

            if (!companyId) {
                throw new Error('Company ID tidak ditemukan. Silakan refresh halaman.');
            }

            const response = await fetch('/subscription/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    plan_id: selectedPlan.id,
                    addon_user_count: addonCount,
                    company_id: companyId
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Terjadi kesalahan pada server');
            }

            const data = await response.json();

            if (data.success) {
                if (typeof window.snap === 'undefined') {
                    throw new Error('Midtrans Snap tidak ter-load. Silakan refresh halaman.');
                }

                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        alert('Pembayaran berhasil!');
                        window.location.reload();
                    },
                    onPending: function(result) {
                        alert('Menunggu pembayaran...');
                        window.location.reload();
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal!');
                        btnProceed.disabled = false;
                        btnText.classList.remove('hidden');
                        btnLoading.classList.add('hidden');
                    },
                    onClose: function() {
                        btnProceed.disabled = false;
                        btnText.classList.remove('hidden');
                        btnLoading.classList.add('hidden');
                    }
                });
            } else {
                throw new Error(data.message || 'Gagal membuat pembayaran');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
            btnProceed.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
        }
    }

    function resetModal() {
        selectedPlan = null;
        document.getElementById('addonUserCount').value = 0;
        document.getElementById('addonSection').classList.add('hidden');
        document.querySelectorAll('.plan-card').forEach(card => {
            card.classList.remove('border-[#4A63E7]', 'shadow-xl', 'scale-105');
        });
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(parseFloat(num));
    }

    document.getElementById('modalPilihPaket')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
