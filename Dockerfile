FROM php:8.2-fpm

# Cài đặt các công cụ hệ thống và thư viện cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Xóa cache để giảm dung lượng ảnh
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt các PHP Extensions phục vụ SQLite
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd

# CÀI ĐẶT NODE.JS & NPM (Để phục vụ lệnh npm run build ở Bước 6)
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc bên trong container
WORKDIR /var/www

# Cấp quyền ghi cho các thư mục cache và database của Laravel
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache /var/www/database \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]