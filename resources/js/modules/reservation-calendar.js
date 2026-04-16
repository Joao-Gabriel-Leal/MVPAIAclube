const WEEKDAY_LABELS = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB'];

export function initializeReservationCalendars(root = document) {
    root.querySelectorAll('[data-reservation-ui]').forEach((container) => {
        if (container.dataset.calendarBound === 'true') {
            return;
        }

        container.dataset.calendarBound = 'true';

        const form = container.closest('form');

        if (!form) {
            return;
        }

        const resourceSelect = form.querySelector('#club_resource_id');
        const dateInput = form.querySelector('#reservation_date');
        const startInput = form.querySelector('#start_time');
        const endInput = form.querySelector('#end_time');
        const submitButton = form.querySelector('[data-reservation-submit]');
        const monthLabel = container.querySelector('[data-calendar-month-label]');
        const calendarGrid = container.querySelector('[data-calendar-grid]');
        const calendarStatus = container.querySelector('[data-calendar-status]');
        const calendarShell = container.querySelector('[data-calendar-shell]');
        const slotGrid = container.querySelector('[data-slot-grid]');
        const slotStatus = container.querySelector('[data-slot-status]');
        const slotStage = container.querySelector('[data-slot-stage]');
        const summary = container.querySelector('[data-selection-summary]');
        const prevButton = container.querySelector('[data-calendar-prev]');
        const nextButton = container.querySelector('[data-calendar-next]');
        const today = container.dataset.today || formatDate(new Date());

        const state = {
            activeMonth: startOfMonth(createDateFromIso(dateInput.value || today)),
            selectedDate: dateInput.value || '',
            selectedSlotKey: startInput.value && endInput.value ? `${startInput.value}-${endInput.value}` : '',
            monthDays: {},
            slots: [],
            loadingMonth: false,
            loadingSlots: false,
            monthRequestId: 0,
            dayRequestId: 0,
        };

        function renderWeekdays() {
            return WEEKDAY_LABELS.map((label) => `<div class="calendar-weekday">${label}</div>`).join('');
        }

        function renderCalendar() {
            monthLabel.textContent = formatMonthLabel(state.activeMonth);
            calendarShell?.setAttribute('aria-busy', state.loadingMonth ? 'true' : 'false');
            calendarShell?.toggleAttribute('data-loading', state.loadingMonth);

            if (state.loadingMonth && resourceSelect.value) {
                calendarGrid.innerHTML = `${renderWeekdays()}${renderCalendarSkeleton()}`;
                return;
            }

            const cells = buildCalendarCells(state.activeMonth);
            const gridMarkup = cells.map((date) => renderCalendarCell(date)).join('');

            calendarGrid.innerHTML = `${renderWeekdays()}${gridMarkup}`;
        }

        function renderCalendarCell(date) {
            const inMonth = isSameMonth(date, state.activeMonth);

            if (!inMonth) {
                return `
                    <div class="calendar-day is-other-month is-disabled" aria-hidden="true">
                        <div class="calendar-day-content">
                            <span class="calendar-day-number">${date.getDate()}</span>
                        </div>
                    </div>
                `;
            }

            const isoDate = formatDate(date);
            const day = state.monthDays[isoDate];
            const stateClass = resolveCalendarClass(day);
            const disabled = !day || ['past', 'unavailable'].includes(day.state);
            const isSelected = state.selectedDate === isoDate;
            const isToday = isoDate === today;
            const label = describeDayState(day);
            const caption = describeDayCaption(day, isToday);

            return `
                <button
                    type="button"
                    class="calendar-day ${stateClass} ${isSelected ? 'is-selected' : ''} ${isToday ? 'is-today' : ''}"
                    data-calendar-day="${isoDate}"
                    ${disabled ? 'disabled' : ''}
                    aria-label="${formatFullDate(date)}${label ? `, ${label}` : ''}"
                >
                    <div class="calendar-day-content">
                        <span class="calendar-day-number">${date.getDate()}</span>
                        <span class="calendar-day-caption">${caption}</span>
                    </div>
                    ${day ? '<span class="calendar-day-dot"></span>' : ''}
                </button>
            `;
        }

        function renderSlots() {
            slotStage?.setAttribute('aria-busy', state.loadingSlots ? 'true' : 'false');
            slotStage?.toggleAttribute('data-loading', state.loadingSlots);

            if (!resourceSelect.value) {
                slotStatus.textContent = 'Selecione um recurso para abrir os horarios.';
                slotGrid.innerHTML = '';
                return;
            }

            if (!state.selectedDate) {
                slotStatus.textContent = 'Escolha um dia no calendario para ver os horarios.';
                slotGrid.innerHTML = '';
                return;
            }

            if (state.loadingSlots) {
                slotStatus.textContent = `Carregando horarios de ${formatCompactDate(state.selectedDate)}...`;
                slotGrid.innerHTML = renderSlotSkeletons();
                return;
            }

            if (!state.slots.length) {
                slotStatus.textContent = 'Nenhum horario configurado para o dia selecionado.';
                slotGrid.innerHTML = `
                    <div class="slot-empty-state">
                        <strong>Sem slots disponiveis</strong>
                        <span>Tente outra data ou altere o recurso selecionado.</span>
                    </div>
                `;
                return;
            }

            const availableCount = state.slots.filter((slot) => slot.available).length;

            slotStatus.textContent = availableCount
                ? `${availableCount} horario${availableCount > 1 ? 's' : ''} livre${availableCount > 1 ? 's' : ''} em ${formatCompactDate(state.selectedDate)}.`
                : `Nao ha horarios livres em ${formatCompactDate(state.selectedDate)}.`;

            slotGrid.innerHTML = state.slots.map((slot) => {
                const key = `${slot.start_time}-${slot.end_time}`;
                const isSelected = key === state.selectedSlotKey;

                return `
                    <button
                        type="button"
                        class="slot-button ${isSelected ? 'is-selected' : ''}"
                        data-slot="${key}"
                        ${slot.available ? '' : 'disabled'}
                    >
                        <div class="slot-button-row">
                            <div>
                                <div class="slot-time">${slot.start_time}</div>
                                <div class="slot-duration">Ate ${slot.end_time}</div>
                            </div>
                            <span class="slot-badge ${slot.available ? 'is-available' : 'is-unavailable'}">
                                ${slot.available ? (isSelected ? 'Selecionado' : 'Disponivel') : 'Indisponivel'}
                            </span>
                        </div>
                        <div class="slot-meta">
                            ${slot.available ? 'Toque para reservar este horario.' : 'Este horario ja foi ocupado.'}
                        </div>
                    </button>
                `;
            }).join('');
        }

        function renderSummary() {
            const option = resourceSelect.options[resourceSelect.selectedIndex];

            summary.innerHTML = `
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <div class="summary-label">Recurso</div>
                        <div class="summary-value">${option ? option.textContent.trim() : 'Selecione'}</div>
                    </div>
                    <div>
                        <div class="summary-label">Data</div>
                        <div class="summary-value">${state.selectedDate ? formatDisplayDate(state.selectedDate) : 'Escolha um dia'}</div>
                    </div>
                    <div>
                        <div class="summary-label">Horario</div>
                        <div class="summary-value">${selectedSlotLabel() || 'Escolha um horario'}</div>
                    </div>
                </div>
            `;
        }

        function selectedSlotLabel() {
            const slot = state.slots.find(({ start_time, end_time }) => `${start_time}-${end_time}` === state.selectedSlotKey);

            return slot ? `${slot.start_time} - ${slot.end_time}` : '';
        }

        function syncSubmitButton() {
            if (!submitButton) {
                return;
            }

            const ready = Boolean(state.selectedDate && state.selectedSlotKey && !state.loadingSlots && !state.loadingMonth);

            submitButton.disabled = !ready;
            submitButton.textContent = ready
                ? `Confirmar ${state.slots.find((slot) => `${slot.start_time}-${slot.end_time}` === state.selectedSlotKey)?.start_time ?? ''}`
                : 'Selecione um horario';
        }

        function clearSlotSelection(keepSlots = false) {
            state.selectedSlotKey = '';
            startInput.value = '';
            endInput.value = '';

            if (!keepSlots) {
                state.slots = [];
            }

            renderSlots();
            renderSummary();
            syncSubmitButton();
        }

        async function loadMonth() {
            if (!resourceSelect.value) {
                state.monthDays = {};
                state.loadingMonth = false;
                renderCalendar();
                calendarStatus.textContent = 'Selecione um recurso para carregar a disponibilidade.';
                clearSlotSelection();
                return;
            }

            state.loadingMonth = true;
            calendarStatus.textContent = 'Carregando agenda do mes...';
            renderCalendar();
            syncSubmitButton();

            const requestId = ++state.monthRequestId;
            const month = formatMonth(state.activeMonth);

            try {
                const response = await fetch(`/api/v1/resources/${resourceSelect.value}/availability/month?month=${month}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Nao foi possivel carregar a agenda mensal.');
                }

                const data = await response.json();

                if (requestId !== state.monthRequestId) {
                    return;
                }

                state.monthDays = Object.fromEntries((data.days || []).map((day) => [day.date, day]));
                state.loadingMonth = false;
                renderCalendar();
                calendarStatus.textContent = 'Selecione uma data destacada para continuar.';

                if (state.selectedDate) {
                    const selectedDay = state.monthDays[state.selectedDate];

                    if (!selectedDay || ['past', 'unavailable'].includes(selectedDay.state)) {
                        state.selectedDate = '';
                        dateInput.value = '';
                        clearSlotSelection();
                    } else {
                        await loadSlots(state.selectedDate);
                    }
                } else {
                    clearSlotSelection();
                }
            } catch (error) {
                if (requestId !== state.monthRequestId) {
                    return;
                }

                state.loadingMonth = false;
                state.monthDays = {};
                renderCalendar();
                calendarStatus.textContent = error.message;
                clearSlotSelection();
            } finally {
                syncSubmitButton();
            }
        }

        async function loadSlots(date) {
            if (!resourceSelect.value || !date) {
                return;
            }

            const previousDate = state.selectedDate;
            const preservedKey = previousDate === date
                ? (state.selectedSlotKey || (startInput.value && endInput.value ? `${startInput.value}-${endInput.value}` : ''))
                : '';

            state.loadingSlots = true;
            state.selectedDate = date;
            dateInput.value = date;
            calendarStatus.textContent = `${formatCompactDate(date)} selecionado. Carregando horarios...`;
            clearSlotSelection();
            renderCalendar();
            renderSlots();
            syncSubmitButton();

            const requestId = ++state.dayRequestId;

            try {
                const response = await fetch(`/api/v1/resources/${resourceSelect.value}/availability?date=${date}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Nao foi possivel carregar os horarios do dia.');
                }

                const data = await response.json();

                if (requestId !== state.dayRequestId) {
                    return;
                }

                state.loadingSlots = false;
                state.slots = data.slots || [];
                calendarStatus.textContent = `${formatCompactDate(date)} selecionado. Escolha um horario abaixo.`;

                if (preservedKey && state.slots.some((slot) => `${slot.start_time}-${slot.end_time}` === preservedKey && slot.available)) {
                    state.selectedSlotKey = preservedKey;

                    const selectedSlot = state.slots.find((slot) => `${slot.start_time}-${slot.end_time}` === preservedKey);

                    if (selectedSlot) {
                        startInput.value = selectedSlot.start_time;
                        endInput.value = selectedSlot.end_time;
                    }
                }

                renderSlots();
                renderSummary();
                syncSubmitButton();
            } catch (error) {
                if (requestId !== state.dayRequestId) {
                    return;
                }

                state.loadingSlots = false;
                state.slots = [];
                slotStatus.textContent = error.message;
                slotGrid.innerHTML = '';
                renderSummary();
                syncSubmitButton();
            }
        }

        function selectSlot(key) {
            const slot = state.slots.find(({ start_time, end_time, available }) => `${start_time}-${end_time}` === key && available);

            if (!slot) {
                return;
            }

            state.selectedSlotKey = key;
            startInput.value = slot.start_time;
            endInput.value = slot.end_time;
            renderSlots();
            renderSummary();
            syncSubmitButton();
        }

        container.addEventListener('click', async (event) => {
            const dayButton = event.target.closest('[data-calendar-day]');

            if (dayButton) {
                await loadSlots(dayButton.dataset.calendarDay);
                return;
            }

            const slotButton = event.target.closest('[data-slot]');

            if (slotButton) {
                selectSlot(slotButton.dataset.slot);
            }
        });

        prevButton?.addEventListener('click', async () => {
            if (state.loadingMonth) {
                return;
            }

            state.activeMonth = addMonths(state.activeMonth, -1);
            await loadMonth();
        });

        nextButton?.addEventListener('click', async () => {
            if (state.loadingMonth) {
                return;
            }

            state.activeMonth = addMonths(state.activeMonth, 1);
            await loadMonth();
        });

        resourceSelect?.addEventListener('change', async () => {
            await loadMonth();
        });

        renderSummary();
        syncSubmitButton();
        loadMonth();
    });
}

function resolveCalendarClass(day) {
    if (!day) {
        return 'is-disabled';
    }

    if (day.state === 'available') {
        return 'is-available';
    }

    if (day.state === 'partial') {
        return 'is-partial';
    }

    if (day.state === 'unavailable') {
        return 'is-unavailable is-disabled';
    }

    return 'is-disabled';
}

function describeDayState(day) {
    if (!day) {
        return '';
    }

    if (day.state === 'available') {
        return `${day.available_slots_count} horarios disponiveis`;
    }

    if (day.state === 'partial') {
        return `${day.available_slots_count} horarios ainda livres`;
    }

    if (day.state === 'unavailable') {
        return 'dia indisponivel';
    }

    return 'dia passado';
}

function describeDayCaption(day, isToday) {
    if (!day) {
        return isToday ? 'Hoje' : 'Sem agenda';
    }

    if (day.state === 'available') {
        return day.available_slots_count > 0 ? `${day.available_slots_count} livres` : 'Livre';
    }

    if (day.state === 'partial') {
        return `${day.available_slots_count} livres`;
    }

    if (day.state === 'unavailable') {
        return 'Fechado';
    }

    return isToday ? 'Hoje' : 'Encerrado';
}

function renderCalendarSkeleton() {
    return Array.from({ length: 42 }, () => (
        '<div class="calendar-day-skeleton shimmer" aria-hidden="true"></div>'
    )).join('');
}

function renderSlotSkeletons() {
    return Array.from({ length: 6 }, () => `
        <div class="slot-skeleton shimmer" aria-hidden="true">
            <div class="slot-skeleton-line slot-skeleton-line-lg"></div>
            <div class="slot-skeleton-line slot-skeleton-line-sm"></div>
            <div class="slot-skeleton-line slot-skeleton-line-md"></div>
        </div>
    `).join('');
}

function buildCalendarCells(activeMonth) {
    const firstDay = startOfMonth(activeMonth);
    const start = new Date(firstDay.getFullYear(), firstDay.getMonth(), 1 - firstDay.getDay());
    const cells = [];

    for (let index = 0; index < 42; index += 1) {
        cells.push(new Date(start.getFullYear(), start.getMonth(), start.getDate() + index));
    }

    return cells;
}

function formatMonthLabel(date) {
    const formatter = new Intl.DateTimeFormat('pt-BR', {
        month: 'long',
        year: 'numeric',
    });

    return formatter.format(date);
}

function formatDisplayDate(isoDate) {
    return formatFullDate(createDateFromIso(isoDate));
}

function formatCompactDate(isoDate) {
    const formatter = new Intl.DateTimeFormat('pt-BR', {
        day: 'numeric',
        month: 'long',
    });

    return formatter.format(createDateFromIso(isoDate));
}

function formatFullDate(date) {
    const formatter = new Intl.DateTimeFormat('pt-BR', {
        day: 'numeric',
        month: 'long',
        weekday: 'long',
    });

    return formatter.format(date);
}

function formatMonth(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
}

function formatDate(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function startOfMonth(date) {
    return new Date(date.getFullYear(), date.getMonth(), 1);
}

function addMonths(date, amount) {
    return new Date(date.getFullYear(), date.getMonth() + amount, 1);
}

function createDateFromIso(isoDate) {
    const [year, month, day] = isoDate.split('-').map(Number);

    return new Date(year, month - 1, day || 1);
}

function isSameMonth(first, second) {
    return first.getFullYear() === second.getFullYear() && first.getMonth() === second.getMonth();
}
