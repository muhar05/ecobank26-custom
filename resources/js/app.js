import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import { initSpaNav } from './spa-nav';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.css';

Alpine.plugin(intersect);

window.Alpine = Alpine;

Alpine.start();

initSpaNav();
initSaleDatePickers(document);

function syncSaleDateInput(input) {
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

function initSaleDatePickers(root) {
    const scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('input[data-sale-date-picker]').forEach((input) => {
        if (input._flatpickr) return;
        flatpickr(input, {
            dateFormat: 'Y-m-d',
            locale: Indonesian,
            allowInput: true,
            onChange(selectedDates, dateStr, instance) {
                if (!dateStr) return;
                if (instance.input.value !== dateStr) instance.input.value = dateStr;
                syncSaleDateInput(instance.input);
            },
        });
    });
}

if (typeof MutationObserver !== 'undefined') {
    const slot = document.getElementById('app-content-slot');
    if (slot) {
        new MutationObserver(() => initSaleDatePickers(slot)).observe(slot, { childList: true, subtree: true });
    }
}
