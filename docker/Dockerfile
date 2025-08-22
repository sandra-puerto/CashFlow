# -------- Stage único: PHP 8.2-FPM + Composer + Node completo --------
FROM php:8.2-fpm

# Instalar dependencias del sistema, herramientas útiles y compilación
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl gnupg ca-certificates zip unzip nano \
    libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    libsqlite3-dev libjpeg-dev libfreetype6-dev zlib1g-dev libicu-dev \
    libxslt-dev default-mysql-client nodejs npm \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias para Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd intl xsl zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar proyecto e instalar dependencias PHP y Node
COPY src/package.json ./ 
RUN npm install

COPY src/ ./ 
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Compilar assets (Vite / frontend)
RUN npm run build

# Ajustar permisos de runtime
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
