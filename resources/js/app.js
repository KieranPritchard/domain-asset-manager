// resources/js/app.js
import './bootstrap';
import './config/dropzoneConfig'
import './errors/uploadErrors'
import './dashboard/charts/categoryChart'
import './dashboard/charts/perFileChart'

import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css'; // Optional: brings in default Dropzone styles if desired

// Make Dropzone globally available if your handlers rely on window.Dropzone
window.Dropzone = Dropzone;

// Disable automatic attachment if you are initializing it manually via your custom config
Dropzone.autoDiscover = false;