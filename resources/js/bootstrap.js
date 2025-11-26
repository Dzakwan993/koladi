import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * --------------------------------------------------------------------------
 * Echo / Real-Time Messaging Configuration
 * --------------------------------------------------------------------------
 *
 * KITA HAPUS KONFIGURASI YANG RUSAK DARI SINI.
 * Kita akan membiarkan file 'echo.js' yang mengurus semuanya.
 */

// PASTIKAN BLOK 'new Echo(...)' YANG RUSAK SUDAH DIHAPUS DARI SINI

import './echo'; // <-- BIARKAN BARIS INI. Ini akan memuat file echo.js
