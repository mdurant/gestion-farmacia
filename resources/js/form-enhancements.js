import jQuery from 'jquery';
import select2 from 'select2';
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';

import 'select2/dist/css/select2.min.css';
import 'flatpickr/dist/flatpickr.min.css';

window.$ = window.jQuery = jQuery;
select2(jQuery);

const select2LocaleReady = import('select2/dist/js/i18n/es.js');

const initializedSelects = new WeakSet();
const flatpickrInstances = new WeakMap();

function selectPlaceholder(select) {
    const emptyOption = select.querySelector('option[value=""]');

    return emptyOption?.textContent?.trim() || 'Seleccionar…';
}

function shouldEnableSelectSearch(select) {
    if (select.closest('.filter-toolbar')) {
        return true;
    }

    return select.options.length > 8;
}

function initSelect2(root = document) {
    root.querySelectorAll('select.select.vx-control:not([data-vx-native])').forEach((select) => {
        if (initializedSelects.has(select)) {
            return;
        }

        try {
            const $select = jQuery(select);

            if ($select.hasClass('select2-hidden-accessible')) {
                initializedSelects.add(select);

                return;
            }

            $select.select2({
                width: '100%',
                language: 'es',
                allowClear: !select.required,
                placeholder: selectPlaceholder(select),
                minimumResultsForSearch: shouldEnableSelectSearch(select) ? 0 : Infinity,
                dropdownParent: jQuery(document.body),
            });

            $select.on('change.select2vx', () => {
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            initializedSelects.add(select);
        } catch (error) {
            console.error('Select2 init error', select, error);
        }
    });
}

function prepareDateInput(input) {
    if (input.type === 'date') {
        input.type = 'text';
        input.dataset.vxDatepicker = 'true';

        if (! input.placeholder) {
            input.placeholder = 'dd/mm/aaaa';
        }

        if (! input.autocomplete) {
            input.autocomplete = 'off';
        }

        if (! input.classList.contains('vx-control')) {
            input.classList.add('vx-control');
        }
    }

    if (! input.dataset.vxDatepicker) {
        input.dataset.vxDatepicker = 'true';
    }
}

function attachLabelToVisibleDateInput(input, altInput) {
    if (! altInput || ! input.id) {
        return;
    }

    const fieldId = input.id;
    altInput.id = fieldId;
    input.removeAttribute('id');
    input.setAttribute('aria-hidden', 'true');
    input.tabIndex = -1;

    document.querySelectorAll(`label[for="${fieldId}"]`).forEach((label) => {
        label.setAttribute('for', altInput.id);
    });
}

function initDatepickers(root = document) {
    const candidates = root.querySelectorAll(
        '[data-vx-datepicker], input[type="date"], input[data-vx-date]'
    );

    candidates.forEach((input) => {
        if (flatpickrInstances.has(input) || input.dataset.vxFlatpickrInit === 'true') {
            return;
        }

        prepareDateInput(input);

        try {
            const defaultDate = input.value?.trim() || null;

            const instance = flatpickr(input, {
                locale: Spanish,
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                altInputClass: 'input vx-control w-full flatpickr-input vx-datepicker-visible',
                allowInput: true,
                clickOpens: true,
                disableMobile: true,
                defaultDate,
                monthSelectorType: 'static',
                appendTo: document.body,
                onReady: (_, __, fp) => {
                    attachLabelToVisibleDateInput(fp.input, fp.altInput);

                    if (fp.altInput) {
                        fp.altInput.setAttribute('inputmode', 'numeric');
                        fp.altInput.setAttribute('aria-label', fp.input.getAttribute('aria-label') || 'Seleccionar fecha');
                    }
                },
            });

            input.dataset.vxFlatpickrInit = 'true';
            flatpickrInstances.set(input, instance);
        } catch (error) {
            console.error('Flatpickr init error', input, error);
        }
    });
}

export function initFormEnhancements(root = document) {
    initDatepickers(root);
    initSelect2(root);
}

async function bootFormEnhancements() {
    await select2LocaleReady;
    initFormEnhancements();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        bootFormEnhancements();
    });
} else {
    bootFormEnhancements();
}

window.addEventListener('load', () => {
    bootFormEnhancements();
});

document.addEventListener('livewire:navigated', () => {
    initFormEnhancements();
});
