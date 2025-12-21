import './bootstrap';

import Alpine from 'alpinejs';
import documentSearch from './dokumen-script.js'

window.Alpine = Alpine;

// Register komponen supaya bisa dipanggil di Blade
Alpine.data('documentSearch', documentSearch)

Alpine.start();
