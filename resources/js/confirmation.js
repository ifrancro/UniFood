const dialog = document.querySelector('[data-confirm-dialog]');
const approved = new WeakSet();
let pending = null;

if (dialog) {
    const accept = dialog.querySelector('[data-confirm-accept]');
    const cancel = dialog.querySelector('.confirmation-actions [data-confirm-cancel]');

    // Capture before the shared handler marks a form as already submitted.
    // Delegation also covers order cards refreshed by the live updates.
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!form.matches('form[data-confirm]') || approved.has(form)) return;
        event.preventDefault();
        if (dialog.open || form.dataset.submitting === 'true') return;
        pending = { form, submitter: event.submitter };
        dialog.querySelector('[data-confirm-title]').textContent = form.dataset.confirmTitle || '¿Continuar?';
        dialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
        accept.textContent = form.dataset.confirmLabel || 'Confirmar';
        dialog.showModal();
        cancel.focus();
    }, true);

    dialog.querySelectorAll('[data-confirm-cancel]').forEach(button => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('close', () => { pending = null; });
    // Escape uses the native dialog cancellation and restores focus.
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
    });
    accept.addEventListener('click', () => {
        const target = pending;
        if (!target) return;
        pending = null;
        dialog.close();
        if (!target.form.isConnected) return;
        approved.add(target.form);
        try {
            target.form.requestSubmit(target.submitter?.isConnected ? target.submitter : undefined);
        } finally {
            approved.delete(target.form);
        }
    });
}
