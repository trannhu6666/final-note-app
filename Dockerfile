FROM php:8.4-fpm

# Cài đặt các thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Xóa cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt PHP extensions cần cho Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Sao chép toàn bộ source code vào container
COPY . .

EXPOSE 9000
CMD ["php-fpm"]