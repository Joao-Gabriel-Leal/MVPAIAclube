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
            calendarGrid.innerHTML = `${renderWeekdays()}${cells.map((date) => renderCalendarCell(date)).join('')}`;
        }

        function renderCalendarCell(date) {
            const inMonth = isSameMonth(date, state.activeMonth);
            const isoDate = formatDate(date);
            const day = inMonth ? state.monthDays[isoDate] : null;
            const isInteractive = inMonth && canSelectDay(day);
            const isSelected = state.selectedDate === isoDate;
            const isToday = isoDate === today;

            if (!inMonth) {
                return `
                    <div class="calendar-day is-other-month" aria-hidden="true">
                        <span class="calendar-day-number">${date.getDate()}</span>
                    </div>
                `;
            }

            return `
                <button
                    type="button"
                    class="calendar-day ${isInteractive ? 'is-available' : 'is-disabled'} ${isSelected ? 'is-selected' : ''} ${isToday ? 'is-today' : ''}"
                    data-calendar-day="${isoDate}"
                    data-day-state="${day?.state ?? 'unavailable'}"
                    ${isInteractive ? '' : 'disabled'}
                    aria-pressed="${isSelected ? 'true' : 'false'}"
                    aria-label="${formatFullDate(date)}${describeDayState(day)}"
                >
                    <span class="calendar-day-number">${date.getDate()}</span>
                </button>
            `;
        }

        function renderSlots() {
            slotStage?.setAttribute('aria-busy', state.loadingSlots ? 'true' : 'false');
            slotStage?.toggleAttribute('data-loading', state.loadingSlots);

            if (!resourceSelect.value) {
                slotStatus.textContent = 'Selecione um recurso para ver os horarios.';
                slotGrid.innerHTML = '';
                return;
            }

            if (!state.selectedDate) {
                slotStatus.textContent = 'Escolha uma data com horarios disponiveis.';
                slotGrid.innerHTML = '';
                return;
            }

            if (state.loadingSlots) {
                slotStatus.textContent = `Carregando horarios de ${formatCompactDate(state.selectedDate)}...`;
                slotGrid.innerHTML = renderSlotSkeletons();
                return;
            }

            if (!state.slots.length) {
                slotStatus.textContent = 'Nenhum horario disponivel para esta data.';
                slotGrid.innerHTML = '';
                return;
            }

            slotStatus.textContent = formatCompactDate(state.selectedDate);
            slotGrid.innerHTML = state.slots.map((slot) => {
                const key = `${slot.start_time}-${slot.end_time}`;
                const isSelected = key === state.selectedSlotKey;

                return `
                    <button
                        type="button"
                        class="slot-button ${isSelected ? 'is-selected' : ''}"
                        data-slot="${key}"
                        aria-pressed="${isSelected ? 'true' : 'false'}"
                        aria-label="${isSelected ? `Horario ${slot.start_time} selecionado` : `Selecionar horario ${slot.start_time}`}"
                    >
                        ${slot.start_time}
                    </button>
                `;
            }).join('');
        }

        function syncSubmitButton() {
            if (!submitButton) {
                return;
            }

            const selectedSlot = state.slots.find((slot) => `${slot.start_time}-${slot.end_time}` === state.selectedSlotKey);
            const ready = Boolean(state.selectedDate && selectedSlot && !state.loadingSlots && !state.loadingMonth);

            submitButton.disabled = !ready;
            submitButton.classList.toggle('hidden', !ready);
            submitButton.textContent = ready ? `Confirmar ${selectedSlot.start_time}` : 'Confirmar horario';
        }

        function clearSlotSelection(keepSlots = false) {
            state.selectedSlotKey = '';
            startInput.value = '';
            endInput.value = '';

            if (!keepSlots) {
                state.slots = [];
            }

            renderSlots();
            syncSubmitButton();
        }

        async function loadMonth() {
            if (!resourceSelect.value) {
                state.monthDays = {};
                state.loadingMonth = false;
                state.selectedDate = '';
                dateInput.value = '';
                renderCalendar();
                clearSlotSelection();
                calendarStatus.textContent = 'Selecione um recurso para exibir os dias disponiveis.';
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

                const selectedDay = state.selectedDate ? state.monthDays[state.selectedDate] : null;

                if (state.selectedDate && !canSelectDay(selectedDay)) {
                    state.selectedDate = '';
                    dateInput.value = '';
                    clearSlotSelection();
                }

                renderCalendar();
                calendarStatus.textContent = '';

                if (state.selectedDate && canSelectDay(selectedDay)) {
                    await loadSlots(state.selectedDate);
                } else {
                    clearSlotSelection();
                }
            } catch (error) {
                if (requestId !== state.monthRequestId) {
                    return;
                }

                state.loadingMonth = false;
                state.monthDays = {};
                state.selectedDate = '';
                dateInput.value = '';
                renderCalendar();
                clearSlotSelection();
                calendarStatus.textContent = error.message;
            } finally {
                syncSubmitButton();
            }
        }

        async function loadSlots(date) {
            if (!resourceSelect.value || !date) {
                return;
            }

            const day = state.monthDays[date];

            if (!canSelectDay(day)) {
                return;
            }

            const previousDate = state.selectedDate;
            const preservedKey = previousDate === date
                ? (state.selectedSlotKey || (startInput.value && endInput.value ? `${startInput.value}-${endInput.value}` : ''))
                : '';

            state.loadingSlots = true;
            state.selectedDate = date;
            dateInput.value = date;
            calendarStatus.textContent = 'Carregando horarios do dia...';
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
                state.slots = (data.slots || []).filter((slot) => slot.available);

                if (!state.slots.length) {
                    state.selectedDate = '';
                    dateInput.value = '';
                    clearSlotSelection();
                    renderCalendar();
                    calendarStatus.textContent = 'Nenhum horario foi encontrado nessa data. Escolha outro dia marcado.';
                    return;
                }

                calendarStatus.textContent = '';

                if (preservedKey && state.slots.some((slot) => `${slot.start_time}-${slot.end_time}` === preservedKey)) {
                    state.selectedSlotKey = preservedKey;
                    const selectedSlot = state.slots.find((slot) => `${slot.start_time}-${slot.end_time}` === preservedKey);

                    if (selectedSlot) {
                        startInput.value = selectedSlot.start_time;
                        endInput.value = selectedSlot.end_time;
                    }
                }

                renderSlots();
                syncSubmitButton();
            } catch (error) {
                if (requestId !== state.dayRequestId) {
                    return;
                }

                state.loadingSlots = false;
                state.selectedDate = '';
                dateInput.value = '';
                state.slots = [];
                slotStatus.textContent = error.message;
                slotGrid.innerHTML = '';
                renderCalendar();
                syncSubmitButton();
            }
        }

        function selectSlot(key) {
            const slot = state.slots.find(({ start_time, end_time }) => `${start_time}-${end_time}` === key);

            if (!slot) {
                return;
            }

            state.selectedSlotKey = key;
            startInput.value = slot.start_time;
            endInput.value = slot.end_time;
            renderSlots();
            syncSubmitButton();
        }

        container.addEventListener('click', async (event) => {
            const dayButton = event.target.closest('[data-calendar-day]');

            if (dayButton && !dayButton.disabled) {
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
            state.selectedDate = '';
            dateInput.value = '';
            clearSlotSelection();
            await loadMonth();
        });

        renderCalendar();
        renderSlots();
        syncSubmitButton();
        loadMonth();
    });
}

function canSelectDay(day) {
    return Boolean(day && ['available', 'partial'].includes(day.state) && Number(day.available_slots_count) > 0);
}

function describeDayState(day) {
    if (!day) {
        return '';
    }

    if (day.state === 'available' || day.state === 'partial') {
        return `, ${day.available_slots_count} horarios disponiveis`;
    }

    if (day.state === 'past') {
        return ', dia passado';
    }

    return ', sem horarios disponiveis';
}

function renderCalendarSkeleton() {
    return Array.from({ length: 42 }, () => (
        '<div class="calendar-day-skeleton shimmer" aria-hidden="true"></div>'
    )).join('');
}

function renderSlotSkeletons() {
    return Array.from({ length: 5 }, () => '<div class="slot-skeleton shimmer" aria-hidden="true"></div>').join('');
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
    });

    return `${formatter.format(date)} ${date.getFullYear()}`;
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
