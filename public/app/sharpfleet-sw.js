/* SharpFleet-only Service Worker (scope: /app/sharpfleet/)
 * - Cache-first for static assets
 * - Network-first for navigations (HTML), with offline fallback
 * - Does not cache POST/PUT/etc
 */

const CACHE_VERSION = 'sharpfleet-v5';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;

const STATIC_ASSETS = [
  '/app/sharpfleet-offline.html',
  '/css/sharpfleet/sharpfleetmain.css',
  '/app/sharpfleet.webmanifest',
  // App icons (re-use existing site icons)
  '/apple-touch-icon.png',
  '/android-chrome-192.png',
  '/android-chrome-512.png',
];

const PRECACHE_PAGES = [
  // Driver dashboard (cached while authenticated so it loads offline after first visit)
  '/app/sharpfleet/driver',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    (async () => {
      const staticCache = await caches.open(STATIC_CACHE);
      await staticCache.addAll(STATIC_ASSETS);

      // Best-effort: cache the driver dashboard shell.
      // This may fail if the user is not logged in yet; that's OK.
      const pageCache = await caches.open(PAGE_CACHE);
      await Promise.all(
        PRECACHE_PAGES.map(async (path) => {
          try {
            const res = await fetch(path, { credentials: 'same-origin', cache: 'no-store' });
            if (res && res.ok) {
              await pageCache.put(path, res.clone());
            }
          } catch (e) {
            // ignore
          }
        })
      );

      await self.skipWaiting();
    })()
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys
        .filter((k) => ![STATIC_CACHE, PAGE_CACHE].includes(k))
        .map((k) => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

function isNavigationRequest(request) {
  return request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html');
}

function isStaticAsset(url) {
  return url.pathname.startsWith('/css/')
    || url.pathname.startsWith('/js/')
    || url.pathname.startsWith('/images/')
    || url.pathname === '/app/sharpfleet.webmanifest'
    || url.pathname === '/apple-touch-icon.png'
    || url.pathname === '/android-chrome-192.png'
    || url.pathname === '/android-chrome-512.png'
    || url.pathname === '/favicon.ico';
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Cache-first for static assets
  if (isStaticAsset(url)) {
    event.respondWith(
      caches.match(req).then((cached) => {
        if (cached) return cached;
        return fetch(req).then((res) => {
          if (res && res.ok) {
            const copy = res.clone();
            caches.open(STATIC_CACHE).then((cache) => cache.put(req, copy));
          }
          return res;
        });
      })
    );
    return;
  }

  // Network-first for page navigations; fallback to cached page, then offline.html
  if (isNavigationRequest(req)) {
    // Logout must never fall back to cached authenticated pages.
    // Always hit the network, and clear cached pages so the UI can't appear "stuck" after logout.
    if (url.pathname === '/app/sharpfleet/logout') {
      event.respondWith((async () => {
        try {
          const res = await fetch(req, { cache: 'no-store', credentials: 'same-origin' });
          try { await caches.delete(PAGE_CACHE); } catch (e) { /* ignore */ }
          return res;
        } catch (e) {
          try { await caches.delete(PAGE_CACHE); } catch (e2) { /* ignore */ }
          return caches.match('/app/sharpfleet-offline.html');
        }
      })());
      return;
    }

    event.respondWith((async () => {
      try {
        // Avoid serving stale authenticated pages from the HTTP cache.
        const res = await fetch(req, { cache: 'no-store' });
        if (res && res.ok) {
          const copy = res.clone();
          const cache = await caches.open(PAGE_CACHE);
          cache.put(req, copy);
        }
        return res;
      } catch (e) {
        const cached = await caches.match(req);
        if (cached) return cached;
        return caches.match('/app/sharpfleet-offline.html');
      }
    })());
    return;
  }
});
