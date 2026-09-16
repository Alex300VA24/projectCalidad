const body = document.body;
const getFocusable = (container) => [...container.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),textarea:not([disabled]),select:not([disabled]),iframe,[tabindex]:not([tabindex="-1"])')].filter((element) => !element.hidden);

const setupDialog = (dialog, closeSelectors) => {
    let previousFocus = null;
    const close = () => { dialog.hidden = true; body.classList.remove('modal-open'); previousFocus?.focus(); };
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
    const modal = setupDialog(pdfModal,'[data-modal-close]');
    const frame = pdfModal.querySelector('[data-pdf-frame]'); const loader = pdfModal.querySelector('[data-frame-loader]');
    document.querySelectorAll('[data-open-pdf]').forEach((button) => button.addEventListener('click', () => {
        pdfModal.querySelector('[data-modal-title]').textContent = button.dataset.title;
        pdfModal.querySelector('[data-modal-external]').href = button.dataset.external;
        loader.hidden = false; frame.src = button.dataset.preview; frame.onload = () => { loader.hidden = true; }; modal.open(button);
    }));
    pdfModal.querySelectorAll('[data-modal-close]').forEach((button) => button.addEventListener('click', () => { frame.src = 'about:blank'; loader.hidden = false; }));
}

const formDrawer = document.querySelector('[data-form-drawer]');
if (formDrawer) {
    const drawer = setupDialog(formDrawer,'[data-close-form]');
    document.querySelectorAll('[data-open-form]').forEach((button) => button.addEventListener('click', () => drawer.open(button)));
    if (window.location.hash === `#${formDrawer.id}` || document.querySelector('.alert.error')) drawer.open();
}

const updateModal = document.querySelector('[data-update-modal]');
if (updateModal) {
    const updater = setupDialog(updateModal,'[data-close-update]');
    document.querySelectorAll('[data-edit-indicator]').forEach((button) => button.addEventListener('click', () => {
        updateModal.querySelector('[data-update-title]').textContent = button.dataset.name;
        updateModal.querySelector('[data-update-value]').value = button.dataset.current;
        updateModal.querySelector('[data-update-form]').action = `/indicadores/${button.dataset.id}`;
        updater.open(button);
    }));
}
