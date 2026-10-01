import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import { initSpaNav } from './spa-nav';

Alpine.plugin(intersect);

window.Alpine = Alpine;

Alpine.start();

initSpaNav();
