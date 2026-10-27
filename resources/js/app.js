import { gsap } from 'gsap';

gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', () => {
    const elements = document.querySelectorAll('[data-reveal]');
    if (elements.length) gsap.from(elements, { y: 16, opacity: 0, duration: .45, stagger: .045, ease: 'power2.out', clearProps: 'all' });
});

const navToggle = document.querySelector('.nav-toggle');
navToggle?.addEventListener('click', () => {
    const open = navToggle.getAttribute('aria-expanded') !== 'true';
    navToggle.setAttribute('aria-expanded', String(open));
    navToggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    document.querySelector('#main-nav')?.classList.toggle('is-open', open);
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && navToggle?.getAttribute('aria-expanded') === 'true') {
        navToggle.click();
        navToggle.focus();
    }
});
document.querySelectorAll('[data-password-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.closest('.password-field').querySelector('input');
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        button.setAttribute('aria-pressed', String(visible));
    });
});

const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
document.querySelector('[data-product-search]')?.addEventListener('input', event => {
    let visible = 0;
    document.querySelectorAll('[data-product-name]').forEach(card => {
        card.hidden = !normalize(card.dataset.productName).includes(normalize(event.target.value));
        if (!card.hidden) visible++;
    });
    document.querySelector('[data-search-empty]').hidden = visible !== 0;
});
document.querySelectorAll('[data-step]').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        const minimum = Number(input.min) || 1;
        const maximum = Number(input.max) || 50;
        input.value = String(Math.min(maximum, Math.max(minimum, (Number(input.value) || minimum) + Number(button.dataset.step))));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
});

const toast = document.querySelector('.app-toast');
let toastTimer;
function notify(message, error = false) {
    clearTimeout(toastTimer);
    toast.querySelector('[data-toast-message]').textContent = message;
    toast.querySelector('[data-toast-cart]').hidden = error;
    toast.hidden = false;
    toastTimer = setTimeout(() => { toast.hidden = true; }, 7000);
}
document.querySelector('[data-toast-close]')?.addEventListener('click', () => {
    toast.hidden = true;
    clearTimeout(toastTimer);
});
let adding = false;
document.querySelectorAll('[data-add-form]').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (adding) return;
        adding = true;
        const buttons = document.querySelectorAll('[data-add-form] button');
        buttons.forEach(button => { button.disabled = true; });
        form.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form), credentials: 'same-origin', signal: AbortSignal.timeout(15000),
            });
            if (new URL(response.url).pathname === '/login') {
                window.location.assign(response.url);
                return;
            }
            if (!response.ok) throw new Error(response.status === 419 ? 'Tu sesión expiró. Recarga la página para continuar.' : 'No pudimos confirmar la operación. Revisa tu carrito antes de intentar otra vez.');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const error = page.querySelector('[data-error]');
            if (error) throw new Error(error.textContent.trim());
            const count = page.querySelector('[data-cart-count]');
            const success = page.querySelector('[data-success]');
            if (!count || !success) throw new Error('Revisa tu carrito para confirmar si se agregó el producto.');
            document.querySelectorAll('[data-cart-count]').forEach(badge => { badge.textContent = count.textContent; });
            notify(success.textContent.trim());
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                gsap.fromTo('[data-cart-count]', { scale: 1.3 }, { scale: 1, duration: .5, ease: 'elastic.out(1, .5)', clearProps: 'transform' });
            }
        } catch (error) {
            notify(error.name === 'TimeoutError' ? 'La conexión tardó demasiado. Revisa tu carrito antes de volver a agregar.' : error.message || 'No hay conexión. Revisa tu carrito antes de volver a intentar.', true);
        } finally {
            adding = false;
            form.removeAttribute('aria-busy');
            buttons.forEach(button => { button.disabled = false; });
        }
    });
});
document.addEventListener('submit', event => {
    if (event.defaultPrevented || event.target.matches('[data-add-form]')) return;
    if (event.target.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }
    event.target.dataset.submitting = 'true';
});

const liveRegion = document.querySelector('[data-live-region]');
let liveBusy = false;
async function refreshOrders() {
    if (!liveRegion || liveBusy || document.hidden || document.querySelector('form[data-submitting="true"]') || liveRegion.contains(document.activeElement)) return;
    liveBusy = true;
    const note = document.querySelector('[data-live-note]');
    try {
        const response = await fetch(window.location.href, { credentials: 'same-origin', cache: 'no-store', signal: AbortSignal.timeout(10000) });
        if (new URL(response.url).pathname === '/login') {
            window.location.assign(response.url);
            return;
        }
        if (!response.ok) throw new Error('refresh failed');
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const fresh = page.querySelector('[data-live-region]');
        if (!fresh) throw new Error('missing region');
        if (liveRegion.dataset.signature !== fresh.dataset.signature) {
            liveRegion.replaceChildren(...fresh.childNodes);
            liveRegion.dataset.signature = fresh.dataset.signature;
            notify('El estado de los pedidos se actualizó.');
            toast.querySelector('[data-toast-cart]').hidden = true;
        }
        if (note) {
            note.textContent = 'Actualización automática · cada 15 s';
            note.classList.remove('is-offline');
        }
    } catch {
        if (note) {
            note.textContent = 'Sin conexión · volveremos a intentar';
            note.classList.add('is-offline');
        }
    } finally {
        liveBusy = false;
    }
}
if (liveRegion) {
    setInterval(refreshOrders, 15000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshOrders(); });
}
