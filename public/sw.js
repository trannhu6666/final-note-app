const CACHE_NAME = 'mynotes-v2'; // Đổi tên để trình duyệt ép cập nhật Service Worker mới
const urlsToCache = [
    '/',
    // Cache các thư viện Bootstrap để offline vẫn có giao diện đẹp
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'
];

// 1. Cài đặt Service Worker và Cache các file tĩnh cốt lõi
self.addEventListener('install', event => {
    self.skipWaiting(); // Ép kích hoạt ngay lập tức không cần chờ
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('✓ Mở cache thành công');
                return cache.addAll(urlsToCache);
            })
    );
});

// 2. Kích hoạt và xóa Cache cũ 
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
    self.clients.claim(); // Chiếm quyền điều khiển trang web ngay lập tức
});

// 3. Bắt các request (Fetch) - Tích hợp Dynamic Caching cho Vite
self.addEventListener('fetch', event => {
    if (!event.request.url.startsWith('http')) {
        return;
    }
    // Bỏ qua các request gọi API (để cho IndexedDB trong index.blade.php tự xử lý)

    if (event.request.url.includes('/api/')) {
        return;
    }

    // Chỉ cache các request lấy dữ liệu (GET), bỏ qua POST/PUT...
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // TỰ ĐỘNG CACHE ĐỘNG: Nếu tải thành công file mới (ví dụ file CSS/JS của Vite), thì lưu bản sao vào Cache luôn
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // RỚT MẠNG: Lôi từ cache ra xài (áp dụng cho HTML, CSS, JS, Hình ảnh...)
                return caches.match(event.request);
            })
    );
});