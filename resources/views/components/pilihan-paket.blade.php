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
            <p>*Untuk setiap penambahan 1 user dikenakan biaya <span id="addonPriceInfo" class="font-semibold">Rp 4.000</span> / bulan</p>
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

                <!-- 🔥 Pilihan Metode Pembayaran -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6">
                    <h4 class="font-bold text-gray-900 mb-4">Pilih Metode Pembayaran</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Manual Payment -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="manual" checked class="peer sr-only">
                            <div class="border-2 border-gray-300 rounded-lg p-4 peer-checked:border-[#4A63E7] peer-checked:bg-blue-50 transition-all">
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-6 h-6 text-[#4A63E7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <span class="font-bold text-gray-900">Transfer Manual</span>
                                </div>
                                <p class="text-sm text-gray-600">Transfer ke rekening & upload bukti</p>
                                <span class="absolute top-4 right-4 hidden peer-checked:block text-[#4A63E7]">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </label>

                        <!-- Midtrans (Disabled) -->
                        <label class="relative cursor-not-allowed opacity-50">
                            <input type="radio" name="payment_method" value="midtrans" disabled class="peer sr-only">
                            <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-100">
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <span class="font-bold text-gray-600">Midtrans</span>
                                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Segera</span>
                                </div>
                                <p class="text-sm text-gray-500">Kartu kredit, e-wallet, dll</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Button Bayar -->
                <button onclick="proceedPayment()" id="btnProceed"
                    class="w-full bg-[#4A63E7] text-white font-bold py-4 rounded-xl hover:bg-[#3a4fc7] transition disabled:bg-gray-300 disabled:cursor-not-allowed text-lg shadow-lg hover:shadow-xl">
                    <span id="btnText">💳 Lanjutkan Pembayaran</span>
                    <span id="btnLoading" class="hidden flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 🔥 Modal Upload Bukti Transfer - RESPONSIVE -->
<div id="modalUploadProof" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto relative">
        <!-- Close Button -->
        <button onclick="closeUploadModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-6 sm:p-8">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">Upload Bukti Transfer</h3>

            <div class="space-y-4 sm:space-y-6">
                <!-- Info Rekening -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 sm:p-6">
                    <h4 class="font-bold text-blue-900 mb-3 sm:mb-4 text-sm sm:text-base">📋 Informasi Rekening</h4>
                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bank:</span>
                            <span class="font-semibold">BCA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">No. Rekening:</span>
                            <span class="font-semibold">1234567890</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Atas Nama:</span>
                            <span class="font-semibold">PT Koladi Digital</span>
                        </div>
                        <hr class="my-3 border-blue-200">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-semibold">Total Bayar:</span>
                            <span id="uploadAmount" class="text-lg sm:text-xl font-bold text-blue-900">Rp 0</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Invoice: <span id="uploadInvoiceId" class="font-mono bg-gray-100 px-2 py-0.5 rounded">-</span>
                        </p>
                    </div>
                </div>

                <!-- Upload Form -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        📤 Upload Bukti Transfer <span class="text-red-500">*</span>
                    </label>
                    <input type="file" id="proofFile" accept="image/jpeg,image/png,image/jpg"
                        class="w-full border-2 border-gray-300 rounded-lg p-2 sm:p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-2 flex items-start gap-1">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>Format: JPG, PNG (Max 2MB)</span>
                    </p>
                    <p id="fileError" class="text-xs text-red-600 mt-1 hidden font-semibold"></p>
                </div>

                <!-- Preview -->
                <div id="previewContainer" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">👁️ Preview:</label>
                    <div class="relative rounded-lg overflow-hidden border-2 border-gray-300">
                        <img id="previewImage" class="w-full h-auto" alt="Preview">
                        <button onclick="removePreview()" type="button"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-2">
                    <button onclick="closeUploadModal()"
                        class="w-full sm:flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-lg hover:bg-gray-300 transition text-sm sm:text-base order-2 sm:order-1">
                        ❌ Batal
                    </button>
                    <button onclick="submitProof()" id="btnUpload"
                        class="w-full sm:flex-1 bg-[#4A63E7] text-white font-bold py-3 rounded-lg hover:bg-[#3a4fc7] transition disabled:bg-gray-300 disabled:cursor-not-allowed text-sm sm:text-base order-1 sm:order-2">
                        <span id="btnUploadText">✅ Upload Bukti</span>
                        <span id="btnUploadLoading" class="hidden flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let plans = [];
let addon = null;
let selectedPlan = null;
let plansLoaded = false;
let currentInvoiceId = null;

// Preview image saat dipilih
// 🔥 Preview image dengan validasi
document.getElementById('proofFile')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileError = document.getElementById('fileError');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');

    // Reset error
    fileError.classList.add('hidden');
    fileError.textContent = '';

    if (file) {
        // Validasi ukuran (2MB = 2097152 bytes)
        if (file.size > 2097152) {
            fileError.textContent = '⚠️ Ukuran file terlalu besar! Maksimal 2MB.';
            fileError.classList.remove('hidden');
            e.target.value = '';
            previewContainer.classList.add('hidden');
            return;
        }

        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            fileError.textContent = '⚠️ Format file tidak valid! Gunakan JPG atau PNG.';
            fileError.classList.remove('hidden');
            e.target.value = '';
            previewContainer.classList.add('hidden');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.classList.add('hidden');
    }
});


function openModal() {
    document.getElementById('modalPilihPaket').classList.remove('hidden');
    document.getElementById('modalPilihPaket').classList.add('flex');
    if (!plansLoaded) {
        loadPlans();
    } else {
        document.getElementById('loadingPlans').classList.add('hidden');
        document.getElementById('plansContainer').classList.remove('hidden');
        document.getElementById('planInfo').classList.remove('hidden');
    }
}

function closeModal() {
    document.getElementById('modalPilihPaket').classList.add('hidden');
    document.getElementById('modalPilihPaket').classList.remove('flex');
    resetModal();
}

function openUploadModal(invoiceId, amount) {
    currentInvoiceId = invoiceId;
    document.getElementById('uploadAmount').textContent = formatNumber(amount);
    document.getElementById('uploadInvoiceId').textContent = invoiceId;
    document.getElementById('modalUploadProof').classList.remove('hidden');
    document.getElementById('modalUploadProof').classList.add('flex');
    document.body.style.overflow = 'hidden'; // Prevent scroll
}

function closeUploadModal() {
    document.getElementById('modalUploadProof').classList.add('hidden');
    document.getElementById('modalUploadProof').classList.remove('flex');
    document.getElementById('proofFile').value = '';
    document.getElementById('previewContainer').classList.add('hidden');
    document.getElementById('fileError').classList.add('hidden');
    document.body.style.overflow = ''; // Restore scroll
}

function removePreview() {
    document.getElementById('proofFile').value = '';
    document.getElementById('previewContainer').classList.add('hidden');
}

async function loadPlans() {
    if (plansLoaded && plans.length > 0) {
        renderPlans();
        updateAddonInfo();
        document.getElementById('loadingPlans').classList.add('hidden');
        document.getElementById('plansContainer').classList.remove('hidden');
        document.getElementById('planInfo').classList.remove('hidden');
        return;
    }

    try {
        const timestamp = new Date().getTime();
        const response = await fetch('/api/plans?_=' + timestamp);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

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
        console.error('Error loading plans:', error);
        document.getElementById('loadingPlans').innerHTML = `
            <div class="text-red-500 text-center">
                <p class="mb-4 font-semibold">Gagal memuat paket</p>
                <button onclick="loadPlans()" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Coba Lagi
                </button>
            </div>
        `;
    }
}

function renderPlans() {
    const container = document.getElementById('plansContainer');
    const fragment = document.createDocumentFragment();

    plans.forEach(plan => {
        const card = document.createElement('div');
        card.className = 'bg-[#E8EFFE] rounded-2xl p-6 cursor-pointer transition-all hover:shadow-xl border-2 border-transparent plan-card';
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

    container.innerHTML = '';
    container.appendChild(fragment);
}

function selectPlan(planId) {
    selectedPlan = plans.find(p => p.id === planId);
    if (!selectedPlan) return;

    document.querySelectorAll('.plan-card').forEach(card => {
        if (card.dataset.planId === planId) {
            card.classList.add('border-[#4A63E7]', 'shadow-xl', 'scale-105');
        } else {
            card.classList.remove('border-[#4A63E7]', 'shadow-xl', 'scale-105');
        }
    });

    const addonSection = document.getElementById('addonSection');
    addonSection.classList.remove('hidden');
    setTimeout(() => {
        addonSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);

    calculateTotal();
}

function updateAddonInfo() {
    if (addon) {
        const price = formatNumber(addon.price_per_user);
        document.getElementById('addonPrice').textContent = `Rp ${price}`;
        document.getElementById('addonPriceInfo').textContent = `Rp ${price}`;
    }
}

function calculateTotal() {
    if (!selectedPlan) return;

    const addonCount = parseInt(document.getElementById('addonUserCount').value) || 0;
    const planPrice = parseFloat(selectedPlan.price_monthly);
    const addonPrice = addon ? parseFloat(addon.price_per_user) * addonCount : 0;
    const totalPrice = planPrice + addonPrice;
    const totalUsers = parseInt(selectedPlan.base_user_limit) + addonCount;

    document.getElementById('summaryPlan').textContent = `${selectedPlan.plan_name} - Rp ${formatNumber(planPrice)}`;
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

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const addonCount = parseInt(document.getElementById('addonUserCount').value) || 0;
    const btnProceed = document.getElementById('btnProceed');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');

    btnProceed.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    try {
        const companyId = '{{ $company->id ?? '' }}';
        if (!companyId) throw new Error('Company ID tidak ditemukan');

        const response = await fetch('/subscription/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                plan_id: selectedPlan.id,
                addon_user_count: addonCount,
                company_id: companyId,
                payment_method: paymentMethod
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Terjadi kesalahan pada server');
        }

        const data = await response.json();

        if (data.success) {
            if (data.payment_method === 'manual') {
                // Redirect ke modal upload bukti
                const totalAmount = parseFloat(selectedPlan.price_monthly) + (addon ? parseFloat(addon.price_per_user) * addonCount : 0);
                openUploadModal(data.external_id, totalAmount);
            } else {
                // Midtrans (nanti)
                alert('Midtrans belum tersedia');
            }
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

async function submitProof() {
    const fileInput = document.getElementById('proofFile');
    const file = fileInput.files[0];
    const btnUpload = document.getElementById('btnUpload');
    const btnText = document.getElementById('btnUploadText');
    const btnLoading = document.getElementById('btnUploadLoading');

    if (!file) {
        alert('⚠️ Pilih file bukti transfer terlebih dahulu');
        return;
    }

    btnUpload.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');

    try {
        const formData = new FormData();
        formData.append('invoice_id', currentInvoiceId);
        formData.append('proof_file', file);

        console.log('📤 Uploading:', {
            invoice_id: currentInvoiceId,
            file_name: file.name,
            file_size: file.size,
            file_type: file.type
        });

        const response = await fetch('/subscription/upload-proof', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        console.log('📥 Response status:', response.status);
        console.log('📥 Response headers:', response.headers.get('content-type'));

        // 🔥 Cek apakah response adalah JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('❌ Response bukan JSON:', text);
            throw new Error('Server mengembalikan response yang tidak valid. Cek console untuk detail.');
        }

        const data = await response.json();
        console.log('✅ Data:', data);

        if (data.success) {
            alert('✅ ' + data.message);
            closeUploadModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Upload gagal');
        }
    } catch (error) {
        console.error('❌ Error:', error);
        alert('❌ Terjadi kesalahan: ' + error.message);
    } finally {
        btnUpload.disabled = false;
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
</script>
