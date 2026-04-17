import './bootstrap';

import Alpine from 'alpinejs';
import { initializeAnalyticsCharts } from './modules/analytics-charts';
import { initializeReservationCalendars } from './modules/reservation-calendar';

window.Alpine = Alpine;

Alpine.start();

const masks = {
    cpf: {
        maxLength: 14,
        inputMode: 'numeric',
        format: (value) => applyPattern(value, '###.###.###-##'),
    },
    cnpj: {
        maxLength: 18,
        inputMode: 'numeric',
        format: (value) => applyPattern(value, '##.###.###/####-##'),
    },
    cpfCnpj: {
        maxLength: 18,
        inputMode: 'numeric',
        format: (value) => (value.length > 11
            ? applyPattern(value, '##.###.###/####-##')
            : applyPattern(value, '###.###.###-##')),
    },
    phone: {
        maxLength: 15,
        inputMode: 'tel',
        format: (value) => formatPhone(value),
    },
};

function applyPattern(value, pattern) {
    let formatted = '';
    let index = 0;

    for (const character of pattern) {
        if (character === '#') {
            if (index >= value.length) {
                break;
            }

            formatted += value[index];
            index += 1;
            continue;
        }

        if (index > 0 && index < value.length) {
            formatted += character;
        }
    }

    return formatted;
}

function formatPhone(value) {
    if (value.length <= 2) {
        return value ? `(${value}` : '';
    }

    const areaCode = value.slice(0, 2);
    const subscriberNumber = value.slice(2);
    const prefixLength = value.length > 10 ? 5 : 4;

    if (subscriberNumber.length <= prefixLength) {
        return `(${areaCode}) ${subscriberNumber}`;
    }

    return `(${areaCode}) ${subscriberNumber.slice(0, prefixLength)}-${subscriberNumber.slice(prefixLength, prefixLength + 4)}`;
}

function normalizeDigits(value) {
    return value.replace(/\D+/g, '');
}

function resolveMask(input) {
    const explicitMask = input.dataset.mask;

    if (explicitMask === 'cpf') {
        return masks.cpf;
    }

    if (explicitMask === 'cnpj') {
        return masks.cnpj;
    }

    if (explicitMask === 'cpf-cnpj' || explicitMask === 'cpfCnpj') {
        return masks.cpfCnpj;
    }

    if (explicitMask === 'phone' || explicitMask === 'telefone') {
        return masks.phone;
    }

    const identifier = `${input.name || ''} ${input.id || ''}`.toLowerCase();

    if (identifier.includes('cnpj')) {
        return masks.cnpj;
    }

    if (identifier.includes('cpf_cnpj') || identifier.includes('cpfcnpj') || identifier.includes('documento') || identifier.includes('document')) {
        return masks.cpfCnpj;
    }

    if (identifier.includes('cpf')) {
        return masks.cpf;
    }

    if (identifier.includes('telefone') || identifier.includes('phone') || identifier.includes('celular') || identifier.includes('whatsapp')) {
        return masks.phone;
    }

    return null;
}

function bindMask(input) {
    if (input.dataset.maskBound === 'true') {
        return;
    }

    const mask = resolveMask(input);

    if (!mask) {
        return;
    }

    input.dataset.maskBound = 'true';
    input.inputMode = mask.inputMode;
    input.maxLength = mask.maxLength;

    const formatValue = () => {
        input.value = mask.format(normalizeDigits(input.value));
    };

    input.addEventListener('input', formatValue);
    formatValue();
}

function initializeMasks(root = document) {
    root.querySelectorAll('input').forEach(bindMask);
}

function initializeMediaSlotPreviews(root = document) {
    root.querySelectorAll('[data-max-upload-bytes]').forEach((input) => {
        if (input.dataset.mediaPreviewBound === 'true') {
            return;
        }

        input.dataset.mediaPreviewBound = 'true';

        const slotCard = input.closest('.media-slot-card');
        const preview = slotCard?.querySelector('[data-media-slot-preview]');
        const maxUploadBytes = Number(input.dataset.maxUploadBytes || 0);

        input.addEventListener('change', () => {
            const currentObjectUrl = input.dataset.objectUrl;

            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                delete input.dataset.objectUrl;
            }

            input.setCustomValidity('');

            const [file] = input.files || [];

            if (!file) {
                return;
            }

            if (maxUploadBytes > 0 && file.size > maxUploadBytes) {
                input.setCustomValidity(`Use uma imagem de ate ${formatFileSize(maxUploadBytes)}.`);
                input.reportValidity();
                return;
            }

            if (!file.type.startsWith('image/') || !preview) {
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            const image = document.createElement('img');

            image.src = objectUrl;
            image.alt = file.name;
            image.className = 'media-slot-preview__image';

            preview.replaceChildren(image);
            input.dataset.objectUrl = objectUrl;
        });
    });
}

function formatFileSize(bytes) {
    const megabytes = bytes / (1024 * 1024);

    return `${megabytes.toFixed(megabytes >= 10 ? 0 : 1)} MB`;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeMasks();
        initializeMediaSlotPreviews();
        initializeAnalyticsCharts();
        initializeReservationCalendars();
    });
} else {
    initializeMasks();
    initializeMediaSlotPreviews();
    initializeAnalyticsCharts();
    initializeReservationCalendars();
}
