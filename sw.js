// sw.js — Progressive Service Worker for DropshippingStack
const SW_VERSION = 'v1.0.0';
const SW_ENABLED = true; // Set to false to unregister immediately on all clients
const CACHE_NAME = `dstack-static-${SW_VERSION}`;

// Precache key assets
const PRECACHE_URLS = [
  '/assets/fonts/plus-jakarta-sans-latin.woff2',
  '/assets/img/logos/placeholder.svg',
  '/favicon.ico'
];

self.addEventListener('install', (event) => {
  if (!SW_ENABLED) {
    self.skipWaiting();
    return;
  }
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_URLS).catch(() => {});
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  if (!SW_ENABLED) {
    event.waitUntil(
      self.registration.unregister().then(() => {
        return caches.keys().then((keys) => Promise.all(keys.map((k) => caches.delete(k))));
      })
    );
    return;
  }
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (!SW_ENABLED) return;
  const request = event.request;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);

  // Skip admin, api, logout, go.php
  if (
    url.pathname.startsWith('/admin') ||
    url.pathname.startsWith('/api') ||
    url.pathname.includes('/logout') ||
    url.pathname.includes('go.php')
  ) {
    return;
  }

  // Static Assets (Cache-First for versioned assets, dist, logos, fonts)
  if (
    url.pathname.startsWith('/assets/') ||
    url.pathname.endsWith('.woff2') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.webp') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.ico')
  ) {
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) return cached;
        return fetch(request).then((response) => {
          if (response && response.status === 200) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // HTML Pages (Network-First with offline fallback)
  if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request).catch(() => {
        return caches.match(request).then((cached) => {
          if (cached) return cached;
          return new Response(
            `<!DOCTYPE html>
            <html lang="en">
            <head>
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width,initial-scale=1">
              <title>Offline · DropshippingStack</title>
              <style>
                body { font-family: system-ui, -apple-system, sans-serif; background: #0D1310; color: #EBF5EE; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; padding: 20px; }
                .box { max-width: 400px; padding: 32px; background: #141B17; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; }
                h1 { font-size: 20px; margin-bottom: 8px; color: #326346; }
                p { font-size: 14px; opacity: 0.8; margin-bottom: 20px; }
                button { background: #326346; color: #fff; border: none; padding: 10px 20px; border-radius: 99px; cursor: pointer; font-weight: 600; }
              </style>
            </head>
            <body>
              <div class="box">
                <h1>You are currently offline</h1>
                <p>Check your internet connection and reload to view verified dropshipping tools.</p>
                <button onclick="window.location.reload()">Retry Connection</button>
              </div>
            </body>
            </html>`,
            { headers: { 'Content-Type': 'text/html' } }
          );
        });
      })
    );
  }
});
