# Builds the dependancies
FROM composer:2 AS build

WORKDIR /usr/src/dns-record-manager

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Builds the compiled Tailwind CSS
FROM node:20-alpine AS tailwind

WORKDIR /usr/src/dns-record-manager

COPY package.json package-lock.json* ./
RUN npm install

COPY tailwind.config.js ./
COPY app/Views ./app/Views
COPY public/assets/css/input.css ./public/assets/css/input.css

RUN npx @tailwindcss/cli \
    -i public/assets/css/input.css \
    -o public/assets/css/main.css \
    --minify

# Builds the final run time environement
FROM php:8.4-cli-alpine

LABEL authors="kieranpritchard"

RUN docker-php-ext-install mysqli

WORKDIR /usr/src/dns-record-manager

# Copy application source code
COPY . .

# Overwrite vendor folder with cleaned production dependencies from build stage
COPY --from=build /usr/src/dns-record-manager/vendor ./vendor

# Expose port and bind to all interfaces
EXPOSE 3000
CMD ["php", "-S", "0.0.0.0:3000", "-t", "public", "router.php"]