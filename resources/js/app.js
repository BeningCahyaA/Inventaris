import './bootstrap';
import './echo';

import Alpine from 'alpinejs';
import { Html5Qrcode } from 'html5-qrcode';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

window.Alpine = Alpine;
window.Html5Qrcode = Html5Qrcode;
window.Chart = Chart;

Alpine.start();
