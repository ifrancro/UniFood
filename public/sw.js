// Increment the version whenever public offline resources change.
const CACHE_PREFIX = 'unifood-public-';
const CACHE_NAME = `${CACHE_PREFIX}v1`;
const localUrl = path => new URL(path, self.registration.scope).href;
const OFFLINE_URL = localUrl('offline.html');
const PUBLIC_ASSETS = [
    'offline.html', 'manifest.webmanifest', 'images/univalle.png',
    'icons/icon-192.png', 'icons/icon-512.png', 'icons/icon-maskable-512.png', 'icons/apple-touch-icon.png',
].map(localUrl);
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(PUBLIC_ASSETS)));
    // Updates activate after existing tabs close, keeping their current version consistent.
});
self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter(key => key.startsWith(CACHE_PREFIX) && key !== CACHE_NAME).map(key => caches.delete(key)));
        await self.clients.claim();
    })());
});
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;
    // Authenticated HTML, redirects, API responses and transactions never enter the cache.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }
    if (!PUBLIC_ASSETS.includes(url.href)) return;
    event.respondWith((async () => {
        const cached = await caches.match(request);
        if (cached) return cached;
        const response = await fetch(request);
        if (response.ok && !response.redirected && response.type === 'basic') {
            const cache = await caches.open(CACHE_NAME);
            await cache.put(request, response.clone());
        }
        return response;
    })());
});
