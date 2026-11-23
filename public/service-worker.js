const CACHE_NAME = 'antriyuk-static-v1';
const OFFLINE_URL = '/offline.html';
const ASSETS = [
  '/',
  '/manifest.json',
  '/offline.html',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.map(key => {
        if (!cacheWhitelist.includes(key)) {
          return caches.delete(key);
        }
      })
    ))
  );
});

self.addEventListener('fetch', event => {
  const { request } = event;
  // Network‑first for API endpoint
  if (request.url.includes('/admin/realtime-stats')) {
    event.respondWith(
      fetch(request).catch(() => caches.match(request))
    );
    return;
  }
  // Navigation fallback to offline page
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }
  // Cache‑first for other requests
  event.respondWith(
    caches.match(request).then(cached => {
      return cached || fetch(request).then(response => {
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(request, responseClone));
        return response;
      });
    })
  );
});
