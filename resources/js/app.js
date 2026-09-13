import './bootstrap';

import Alpine from 'alpinejs';
import documentSearch from './dokumen-script.js'

window.Alpine = Alpine;
window.documentSearch = documentSearch;

// Register komponen supaya bisa dipanggil di Blade
Alpine.data('documentSearch', documentSearch)

Alpine.start();
