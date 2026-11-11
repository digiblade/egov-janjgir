import './bootstrap';
import Alpine from '@alpinejs/csp';
import '@fontsource-variable/noto-sans-devanagari/wght.css';

const initialErrorSummary = document.querySelector('.form-error-summary');
if (initialErrorSummary) {
    initialErrorSummary.setAttribute('tabindex', '-1');
    window.requestAnimationFrame(() => initialErrorSummary.focus());
}

document.querySelectorAll('.form-field, .field').forEach((field) => {
    const control = field.querySelector('input, select, textarea');
    const error = field.querySelector('.field-error');
    if (!control || !error) return;

    if (!error.id) error.id = `${control.id || control.name || 'field'}-error`;
    control.setAttribute('aria-invalid', 'true');
    const describedBy = new Set((control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
    describedBy.add(error.id);
    control.setAttribute('aria-describedby', [...describedBy].join(' '));
});

document.querySelectorAll('.admin-main > .admin-status[role="status"], .admin-main > .form-success[role="status"]').forEach((toast) => {
    toast.setAttribute('aria-atomic', 'true');
    const dismiss = document.createElement('button');
    dismiss.type = 'button';
    dismiss.className = 'admin-toast__dismiss';
    dismiss.setAttribute('aria-label', document.body.dataset.dismissLabel || 'Dismiss notification');
    dismiss.textContent = '×';
    dismiss.addEventListener('click', () => toast.remove());
    toast.append(dismiss);
});

let adminLoadingTimer;
const showAdminLoadingState = () => {
    window.clearTimeout(adminLoadingTimer);
    adminLoadingTimer = window.setTimeout(() => {
        document.documentElement.classList.add('admin-is-loading');
        document.querySelector('.admin-main')?.setAttribute('aria-busy', 'true');
        const message = document.querySelector('[data-admin-loading-message]');
        if (message) message.hidden = false;
    }, 120);
};

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target.closest('.admin-sidebar__link, .admin-topbar__brand, .admin-main a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
    const destination = new URL(link.href, window.location.href);
    const sameDocumentPath = destination.pathname === window.location.pathname && destination.search === window.location.search;
    if (destination.origin === window.location.origin && !sameDocumentPath) showAdminLoadingState();
});

document.addEventListener('submit', (event) => {
    if (event.target.closest('.admin-main') && event.target.method.toLowerCase() !== 'dialog') showAdminLoadingState();
});

window.Alpine = Alpine;

Alpine.data('portalSidebar', () => ({
    collapsed: false,
    toggle() {
        this.collapsed = !this.collapsed;
    },
}));

Alpine.data('portalRichText', () => ({
    get editor() { return this.$el.querySelector('textarea'); },
    wrap(marker) {
        const field = this.editor;
        const start = field.selectionStart;
        const end = field.selectionEnd;
        const selected = field.value.slice(start, end) || 'text';
        const replacement = `${marker}${selected}${marker}`;
        field.setRangeText(replacement, start, end, 'select');
        field.focus();
    },
    prefix(mark) {
        const field = this.editor;
        const start = field.selectionStart;
        const lineStart = field.value.lastIndexOf('\n', Math.max(0, start - 1)) + 1;
        field.setRangeText(mark, lineStart, lineStart, 'end');
        field.focus();
    },
    link() {
        const field = this.editor;
        const start = field.selectionStart;
        const end = field.selectionEnd;
        const label = field.value.slice(start, end) || 'link text';
        field.setRangeText(`[${label}](https://)`, start, end, 'select');
        field.focus();
    },
}));

Alpine.data('portalMediaPreview', () => ({
    src: '',
    alt: '',
    query: '',
    matches(filename) {
        return !this.query || filename.includes(this.query.trim().toLocaleLowerCase());
    },
    hasMatchingAsset() {
        return [...this.$el.querySelectorAll('.media-picker-option[data-search]')]
            .some((option) => this.matches(option.dataset.search || ''));
    },
    refresh() {
        const option = this.$el.querySelector('input[type="radio"][data-preview-url]:checked')
            || this.$el.querySelector('select option:checked');
        this.src = option?.dataset.previewUrl || '';
        this.alt = option?.dataset.previewAlt || '';
    },
}));

Alpine.data('portalKpiPreview', () => ({
    title: '', description: '', value: '', prefix: '', suffix: '', icon: '',
    background: '#ffffff', textColor: '#102d55', iconColor: '#102d55',
    refresh() {
        const hindi = document.documentElement.lang.startsWith('hi');
        const read = (id) => this.$el.querySelector(`#${id}`)?.value || '';
        this.title = read(hindi ? 'title_hi' : 'title_en') || '—';
        this.description = read(hindi ? 'description_hi' : 'description_en');
        this.value = read('value') || '0';
        this.prefix = read('prefix');
        this.suffix = read('suffix');
        this.icon = read('icon');
        this.background = this.color(read('background_color'), '#ffffff');
        this.textColor = this.readableColor(this.background, read('text_color') || '#102d55');
        this.iconColor = this.readableColor(this.background, read('icon_color') || this.textColor);
    },
    color(value, fallback) {
        return /^#[0-9a-f]{6}$/i.test(value) ? value : fallback;
    },
    readableColor(background, preferred) {
        const contrast = (first, second) => {
            const luminance = (hex) => {
                const channels = hex.slice(1).match(/.{2}/g).map((part) => parseInt(part, 16) / 255).map((channel) => (
                    channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4
                ));
                return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
            };
            const values = [luminance(first), luminance(second)].sort((a, b) => b - a);
            return (values[0] + 0.05) / (values[1] + 0.05);
        };
        const selected = this.color(preferred, '#102d55');
        if (contrast(background, selected) >= 4.5) return selected;
        return contrast(background, '#102d55') >= contrast(background, '#ffffff') ? '#102d55' : '#ffffff';
    },
}));

Alpine.data('portalSortableRows', () => ({
    dragged: null,
    start(event) {
        this.dragged = event.currentTarget;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', 'row');
        this.dragged.classList.add('is-dragging');
    },
    end() {
        this.dragged?.classList.remove('is-dragging');
        this.dragged = null;
        this.syncOrder();
    },
    over(event) {
        event.preventDefault();
        if (!this.dragged) return;
        const target = event.target.closest('tr, fieldset');
        if (!target || target === this.dragged || target.parentElement !== this.$el) return;
        const bounds = target.getBoundingClientRect();
        const before = event.clientY < bounds.top + bounds.height / 2;
        this.$el.insertBefore(this.dragged, before ? target : target.nextSibling);
    },
    move(row, direction) {
        const sibling = direction < 0 ? row.previousElementSibling : row.nextElementSibling;
        if (!sibling) return;
        this.$el.insertBefore(row, direction < 0 ? sibling : sibling.nextElementSibling);
        this.syncOrder();
        row.querySelector('button[data-sort-move]')?.focus();
    },
    syncOrder() {
        [...this.$el.children].filter((row) => row.matches('tr, fieldset')).forEach((row, index) => {
            const input = row.querySelector('input[data-display-order]');
            if (input) input.value = String(index * 10);
        });
    },
}));

Alpine.data('portalCarousel', () => ({
    count: 0,
    active: 0,
    autoplay: false,
    interval: 7000,
    paused: true,
    reducedMotion: false,
    timer: null,
    pointerStartX: null,
    pointerStartY: null,
    init() {
        this.count = Number(this.$el.dataset.slideCount || 0);
        this.autoplay = this.$el.dataset.autoplay === 'true';
        this.interval = Number(this.$el.dataset.intervalMs || 7000);
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.start();
    },
    start() {
        if (this.count > 1 && this.autoplay && !this.reducedMotion) {
            this.paused = false;
            this.timer = window.setInterval(() => this.next(), this.interval);
        }
    },
    pause() {
        this.paused = true;
        window.clearInterval(this.timer);
        this.timer = null;
    },
    resume() {
        if (this.reducedMotion || this.count < 2 || this.timer) {
            return;
        }

        this.paused = false;
        this.timer = window.setInterval(() => this.next(), this.interval);
    },
    next() {
        this.active = (this.active + 1) % this.count;
    },
    previous() {
        this.active = (this.active - 1 + this.count) % this.count;
    },
    goTo(index) {
        this.active = index;
    },
    pointerStart(event) {
        if (event.pointerType === 'mouse' || event.target.closest('button, a')) {
            return;
        }

        this.pointerStartX = event.clientX;
        this.pointerStartY = event.clientY;
        event.currentTarget.setPointerCapture?.(event.pointerId);
    },
    pointerEnd(event) {
        if (this.pointerStartX === null || this.pointerStartY === null) {
            return;
        }

        const deltaX = event.clientX - this.pointerStartX;
        const deltaY = event.clientY - this.pointerStartY;
        this.pointerStartX = null;
        this.pointerStartY = null;

        if (Math.abs(deltaX) < 48 || Math.abs(deltaX) < Math.abs(deltaY) * 1.25) {
            return;
        }

        deltaX < 0 ? this.next() : this.previous();
    },
    pointerCancel() {
        this.pointerStartX = null;
        this.pointerStartY = null;
    },
}));

Alpine.data('galleryLightbox', () => ({
    active: 0,
    count: 0,
    opener: null,
    init() {
        this.count = Number(this.$el.dataset.imageCount || 0);
    },
    open(index, event) {
        this.active = index;
        this.opener = event.currentTarget;
        this.$refs.dialog.showModal();
        this.$nextTick(() => this.$refs.closeButton.focus());
    },
    close() {
        if (this.$refs.dialog.open) {
            this.$refs.dialog.close();
        }
    },
    restoreFocus() {
        const opener = this.opener;
        this.opener = null;

        if (opener instanceof HTMLElement && document.contains(opener)) {
            opener.focus();
        }
    },
    previous() {
        if (this.$refs.dialog.open && this.count > 0) {
            this.active = (this.active - 1 + this.count) % this.count;
        }
    },
    next() {
        if (this.$refs.dialog.open && this.count > 0) {
            this.active = (this.active + 1) % this.count;
        }
    },
}));

Alpine.start();
