import { test } from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
const source = readFileSync(new URL('../../public/sw.js', import.meta.url), 'utf8');
function worker({ offline = false, status = 200, scope = 'https://unifood.test/' } = {}) {
    const listeners = {}, writes = [], deletes = [];
    const fallback = { offline: true };
    const cache = { addAll: async urls => writes.push(...urls), put: async (...args) => writes.push(args) };
    vm.runInNewContext(source, {
        URL, self: { registration: { scope }, location: { origin: new URL(scope).origin },
            addEventListener: (name, fn) => { listeners[name] = fn; }, clients: { claim: async () => {} } },
        caches: { open: async () => cache, match: async key => String(key).endsWith('offline.html') ? fallback : undefined,
            keys: async () => ['unifood-public-v0', 'unifood-public-v1', 'another-app'], delete: async key => deletes.push(key) },
        fetch: async () => { if (offline) throw new Error('offline'); return { status }; },
    });
    const request = (path, options = {}) => {
        let response;
        listeners.fetch({ request: { url: new URL(path, scope).href, method: 'GET', mode: 'cors', ...options }, respondWith: p => { response = p; } });
        return response;
    };
    return { listeners, writes, deletes, fallback, request };
}
test('private routes, queries, cross-origin requests and all writes bypass cache', () => {
    const w = worker();
    for (const path of ['catalogo', 'carrito', 'mis-pedidos/42', 'panel/pedidos', 'api/user', 'icons/icon-192.png?private=1', 'https://other.test/logo.png']) {
        assert.equal(w.request(path), undefined, path);
    }
    for (const method of ['POST', 'PUT', 'DELETE']) assert.equal(w.request('pedidos', { method }), undefined);
    assert.equal(w.writes.length, 0);
});
test('online navigation uses the network without caching user data', async () => {
    const w = worker();
    assert.equal((await w.request('mis-pedidos/42', { mode: 'navigate' })).status, 200);
    assert.equal(w.writes.length, 0);
});
test('failed nested navigation returns generic offline page', async () => {
    const w = worker({ offline: true });
    assert.equal(await w.request('mis-pedidos/42', { mode: 'navigate' }), w.fallback);
});
test('HTTP failures remain visible instead of claiming success', async () => {
    const w = worker({ status: 503 });
    assert.equal((await w.request('catalogo', { mode: 'navigate' })).status, 503);
});
test('installation caches only existing public resources and supports a subdirectory', async () => {
    const w = worker({ scope: 'https://unifood.test/campus/' });
    let ready;
    w.listeners.install({ waitUntil: p => { ready = p; } });
    await ready;
    assert.equal(w.writes.length, 7);
    for (const url of w.writes) {
        assert.ok(url.startsWith('https://unifood.test/campus/'));
        assert.ok(readFileSync(new URL('../../public/' + url.split('/campus/')[1], import.meta.url)).length > 0);
    }
});
test('activation removes only obsolete UniFood caches', async () => {
    const w = worker(); let ready;
    w.listeners.activate({ waitUntil: p => { ready = p; } }); await ready;
    assert.deepEqual(w.deletes, ['unifood-public-v0']);
});

