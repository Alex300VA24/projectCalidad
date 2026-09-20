import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const body = document.body;
const getFocusable = (container) => [...container.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),textarea:not([disabled]),select:not([disabled]),iframe,[tabindex]:not([tabindex="-1"])')].filter((element) => !element.closest('[hidden]'));

const qualityCharts = new Map();
const destroyQualityCharts = (root = document) => {
    const canvases = root.matches?.('[data-quality-chart]') ? [root] : [...root.querySelectorAll?.('[data-quality-chart]') ?? []];
    canvases.forEach((canvas) => {
        qualityCharts.get(canvas)?.destroy();
        qualityCharts.delete(canvas);
    });
};
window.renderQualityCharts = (root = document) => {
    const styles = getComputedStyle(document.documentElement);
    const textColor = styles.getPropertyValue('--ink-soft').trim() || '#536078';
    const gridColor = styles.getPropertyValue('--border').trim() || '#dde1da';
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    root.querySelectorAll('[data-quality-chart]').forEach((canvas) => {
        if (!canvas.dataset.chartConfig) return;

        qualityCharts.get(canvas)?.destroy();
        const config = JSON.parse(canvas.dataset.chartConfig);
        const isDoughnut = config.type === 'doughnut';
        config.options = {
            responsive: true,
            maintainAspectRatio: false,
            animation: reducedMotion ? false : { duration: 350 },
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, padding: 18 } },
                tooltip: { intersect: false },
            },
            scales: isDoughnut ? undefined : {
                x: { ticks: { color: textColor }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
            },
        };

        qualityCharts.set(canvas, new Chart(canvas, config));
    });
};

document.addEventListener('DOMContentLoaded', () => window.renderQualityCharts());
document.addEventListener('livewire:navigating', () => destroyQualityCharts());
document.addEventListener('livewire:navigated', () => window.renderQualityCharts());
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.removing', ({ el }) => destroyQualityCharts(el));
    Livewire.hook('morphed', ({ el }) => window.renderQualityCharts(el));
});

const setupDialog = (dialog, closeSelectors, onClose = () => {}) => {
    let previousFocus = null;
    const close = () => {
        dialog.hidden = true;
        onClose();
        body.classList.toggle('modal-open', Boolean(document.querySelector('.modal-layer:not([hidden]), .simple-modal:not([hidden]), .form-drawer:not([hidden])')));
        previousFocus?.focus();
    };
    const open = (trigger) => { previousFocus = trigger || document.activeElement; dialog.hidden = false; body.classList.add('modal-open'); requestAnimationFrame(() => getFocusable(dialog)[0]?.focus()); };
    dialog.querySelectorAll(closeSelectors).forEach((button) => button.addEventListener('click', close));
    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
        if (event.key !== 'Tab') return;
        const focusable = getFocusable(dialog); const first = focusable[0]; const last = focusable.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    return { open, close };
};

const sidebar = document.querySelector('#sidebar');
const menuToggle = document.querySelector('[data-menu-toggle]');
const menuOverlay = document.querySelector('[data-menu-overlay]');
const closeMenu = () => { sidebar?.classList.remove('open'); if (menuOverlay) menuOverlay.hidden = true; menuToggle?.setAttribute('aria-expanded','false'); };
menuToggle?.addEventListener('click', () => { const opened = sidebar.classList.toggle('open'); menuOverlay.hidden = !opened; menuToggle.setAttribute('aria-expanded',String(opened)); });
menuOverlay?.addEventListener('click', closeMenu);
sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));

document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next; localStorage.setItem('sigi-theme',next);
});

const pdfModal = document.querySelector('[data-pdf-modal]');
if (pdfModal) {
    const frame = pdfModal.querySelector('[data-pdf-frame]');
    const loader = pdfModal.querySelector('[data-frame-loader]');
    let sourceDialog = null;
    const modal = setupDialog(pdfModal, '[data-modal-close]', () => {
        if (frame) {
            frame.onload = null;
            frame.src = 'about:blank';
        }
        if (loader) loader.hidden = false;
        if (sourceDialog) sourceDialog.hidden = false;
        sourceDialog = null;
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-open-pdf]');
        if (!button) return;

        sourceDialog = button.closest('[data-execution-reports-modal], [data-student-references-modal]');
        if (sourceDialog) sourceDialog.hidden = true;

        pdfModal.querySelector('[data-modal-title]').textContent = button.dataset.title || 'Documento';
        const externalUrl = button.dataset.external || button.dataset.preview;
        pdfModal.querySelector('[data-modal-external]').href = externalUrl;

        let previewUrl = button.dataset.preview || externalUrl;
        if (previewUrl && previewUrl.includes('drive.google.com') && !previewUrl.includes('/preview')) {
            previewUrl = previewUrl.replace(/\/view(\?.*)?$/, '/preview');
        }

        if (loader) loader.hidden = false;
        if (frame) {
            frame.src = previewUrl;
            frame.onload = () => { if (loader) loader.hidden = true; };
        }
        modal.open(button);
    });
}

document.querySelectorAll('[data-execution-reports-modal]').forEach((executionReportsModal) => {
    const dialog = setupDialog(executionReportsModal, '[data-execution-reports-close]');
    const periodButtons = [...executionReportsModal.querySelectorAll('[data-execution-period]')];
    const periodPanels = [...executionReportsModal.querySelectorAll('[data-execution-period-panel]')];
    const selectPeriod = (period) => {
        periodButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.executionPeriod === period)));
        periodPanels.forEach((panel) => { panel.hidden = panel.dataset.executionPeriodPanel !== period; });
    };

    document.querySelector(`[data-open-execution-reports="${executionReportsModal.dataset.executionReportsModal}"]`)?.addEventListener('click', (buttonEvent) => {
        selectPeriod('2026-I');
        dialog.open(buttonEvent.currentTarget);
    });
    periodButtons.forEach((button) => button.addEventListener('click', () => selectPeriod(button.dataset.executionPeriod)));
});

const studentReferencesModal = document.querySelector('[data-student-references-modal]');
if (studentReferencesModal) {
    const dialog = setupDialog(studentReferencesModal, '[data-student-references-close]');
    const studentButtons = [...studentReferencesModal.querySelectorAll('[data-student-reference]')];
    const studentPanels = [...studentReferencesModal.querySelectorAll('[data-student-reference-panel]')];
    const selectStudent = (studentIndex) => {
        studentButtons.forEach((button) => button.setAttribute('aria-expanded', String(button.dataset.studentReference === studentIndex)));
        studentPanels.forEach((panel) => { panel.hidden = panel.dataset.studentReferencePanel !== studentIndex; });
    };

    document.querySelector('[data-open-student-references]')?.addEventListener('click', (buttonEvent) => {
        selectStudent(null);
        dialog.open(buttonEvent.currentTarget);
    });
    studentButtons.forEach((button) => button.addEventListener('click', () => selectStudent(button.dataset.studentReference)));
}

const formDrawer = document.querySelector('[data-form-drawer]');
if (formDrawer) {
    const drawer = setupDialog(formDrawer,'[data-close-form]');
    document.querySelectorAll('[data-open-form]').forEach((button) => button.addEventListener('click', () => drawer.open(button)));
    if (window.location.hash === `#${formDrawer.id}` || document.querySelector('.alert.error')) drawer.open();
}

const documentForm = document.querySelector('[data-document-form]');
if (documentForm) {
    const typeInputs = [...documentForm.querySelectorAll('input[name="document_type"]')];
    const panels = [...documentForm.querySelectorAll('[data-type-panel]')];
    const periodSelect = documentForm.querySelector('[data-syllabus-period]');
    const cycleSelect = documentForm.querySelector('[data-syllabus-cycle]');
    const syncCycles = () => {
        if (!periodSelect || !cycleSelect) return;

        const period = periodSelect.selectedOptions[0]?.dataset.period ?? '';
        const parity = period.endsWith('-I') ? 'odd' : period.endsWith('-II') ? 'even' : '';

        [...cycleSelect.options].forEach((option) => {
            if (!option.dataset.parity) return;
            const available = option.dataset.parity === parity;
            option.hidden = !available;
            option.disabled = !available;
            if (!available && option.selected) cycleSelect.value = '';
        });
    };
    const sync = () => {
        const selected = typeInputs.find((input) => input.checked)?.value ?? 'institucional';
        panels.forEach((panel) => { panel.hidden = panel.dataset.typePanel !== selected; });
        syncCycles();
    };
    typeInputs.forEach((input) => input.addEventListener('change', sync));
    periodSelect?.addEventListener('change', syncCycles);
    sync();
}

const processMap = document.querySelector('[data-process-map]');
if (processMap) {
    const search = processMap.querySelector('[data-process-search]');
    const entityFilter = processMap.querySelector('[data-process-entity-filter]');
    const cards = [...processMap.querySelectorAll('[data-process-card]')];
    const sections = [...processMap.querySelectorAll('[data-process-section]')];
    const resultCount = processMap.querySelector('[data-process-count]');
    const emptyState = processMap.querySelector('[data-process-empty]');
    const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();

    const applyFilters = () => {
        const query = normalize(search?.value || '');
        const entity = entityFilter?.value || '';
        let visibleCount = 0;

        cards.forEach((card) => {
            const matchesQuery = !query || normalize(card.dataset.searchable || '').includes(query);
            const matchesEntity = !entity || (card.dataset.entities || '').split('|').includes(entity);
            const visible = matchesQuery && matchesEntity;
            card.hidden = !visible;
            if (visible) visibleCount += 1;
        });

        sections.forEach((section) => {
            section.hidden = !section.querySelector('[data-process-card]:not([hidden])');
        });

        if (resultCount) {
            resultCount.textContent = query || entity
                ? `${visibleCount} ${visibleCount === 1 ? 'proceso encontrado' : 'procesos encontrados'}`
                : `${cards.length} procesos disponibles`;
        }

        if (emptyState) emptyState.hidden = visibleCount !== 0;
    };

    search?.addEventListener('input', applyFilters);
    entityFilter?.addEventListener('change', applyFilters);
}
