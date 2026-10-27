const installButton = document.querySelector('[data-pwa-install]');
const installHelp = document.querySelector('[data-pwa-help]');
let installPrompt;
window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    installPrompt = event;
    if (installButton) installButton.hidden = false;
    if (installHelp) installHelp.hidden = true;
});
installButton?.addEventListener('click', async () => {
    if (!installPrompt) return;
    installButton.disabled = true;
    try {
        await installPrompt.prompt();
        await installPrompt.userChoice;
    } catch (error) {
        console.warn('No se pudo abrir la instalación de UniFood.', error);
    } finally {
        installPrompt = null;
        installButton.hidden = true;
        installButton.disabled = false;
    }
});
window.addEventListener('appinstalled', () => {
    installPrompt = null;
    if (installButton) installButton.hidden = true;
    if (installHelp) installHelp.hidden = true;
});
const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
const ios = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
if (ios && !standalone && window.isSecureContext && installHelp) installHelp.hidden = false;
const connectionNotice = document.querySelector('[data-connection-notice]');
function updateConnection() {
    if (connectionNotice) connectionNotice.hidden = navigator.onLine;
}
window.addEventListener('online', updateConnection);
window.addEventListener('offline', updateConnection);
updateConnection();
const workerUrl = document.querySelector('meta[name="unifood-service-worker"]')?.content;
if ('serviceWorker' in navigator && window.isSecureContext && workerUrl) {
    const register = () => navigator.serviceWorker.register(workerUrl, { updateViaCache: 'none' })
        .catch(error => console.warn('No se pudo activar el modo PWA de UniFood.', error));
    if (document.readyState === 'complete') register();
    else window.addEventListener('load', register, { once: true });
}
