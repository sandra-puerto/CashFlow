FROM php:8.2-cli

# Dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip curl gnupg ca-certificates nano \
    libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    libsqlite3-dev libcurl4-openssl-dev libssl-dev \
    libjpeg-dev libfreetype6-dev zlib1g-dev libicu-dev \
    libxslt-dev && \
    rm -rf /var/lib/apt/lists/*

# Extensiones PHP necesarias para Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Instalar Node.js LTS (v22) y npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g npm && \
    rm -rf /var/lib/apt/lists/*

# Directorio de trabajo
WORKDIR /app