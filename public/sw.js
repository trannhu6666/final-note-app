const CACHE_NAME = 'mynotes-v1';
const urlsToCache = [
    '/',
    '/notes',
    '/notes/shared',
    // Cache các thư viện Bootstrap để offline vẫn có giao diện đẹp
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'
];

// 1. Cài đặt Service Worker và Cache các file cần thiết
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// 2. Kích hoạt và xóa Cache cũ (nếu có cập nhật)
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// 3. Bắt các request (Fetch)
// Chiến lược: Ưu tiên lấy từ Network, nếu rớt mạng thì lấy từ Cache
self.addEventListener('fetch', event => {
    // Bỏ qua các request gọi API (chỉ cache giao diện)
    if (event.request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});