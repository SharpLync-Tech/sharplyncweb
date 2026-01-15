/* SharpFleet Service Worker
 * - Cache-first for static assets
 * - Network-first for navigations
 * - PWA install compatible
 */

const CACHE_VERSION = 'sharpfleet-v2';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;

const STATIC_ASSETS = [
  '/offline.html',
  '/css/sharpfleet/sharpfleet-mobile.css',
  '/css/sharpfleet/sharpfleet-sheets.css',
  '/manifest.json',
  '/images/sharpfleet/pwa/icon-192.png',
  '/images/sharpfleet/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(k => ![STATIC_CACHE, PAGE_CACHE].includes(k))
          .map(k => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

function isNavigationRequest(request) {
  return request.mode === 'navigate'
    || (request.headers.get('accept') || '').includes('text/html');
}

function isStaticAsset(url) {
  return url.pathname.startsWith('/css/')
    || url.pathname.startsWith('/js/')
    || url.pathname.startsWith('/images/')
    || url.pathname === '/manifest.json';
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Cache-first for static assets
  if (isStaticAsset(url)) {
    event.respondWith(
      caches.match(req).then(cached => {
        if (cached) return cached;
        return fetch(req).then(res => {
          const copy = res.clone();
          caches.open(STATIC_CACHE).then(cache => cache.put(req, copy));
          return res;
        });
      })
    );
    return;
  }

  // Network-first for pages
  if (isNavigationRequest(req)) {
    event.respondWith(
      (async () => {
        try {
          const res = await fetch(req);
          const copy = res.clone();
          const cache = await caches.open(PAGE_CACHE);
          cache.put(req, copy);
          return res;
        } catch {
          const cached = await caches.match(req);
          if (cached) return cached;

          if (url.pathname.startsWith('/app/sharpfleet/mobile')) {
            const cachedDashboard = await caches.match('/app/sharpfleet/mobile');
            if (cachedDashboard) return cachedDashboard;
          }

          return caches.match('/offline.html');
        }
      })()
    );
  }
});
