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

# Runs the test suite
FROM php:8.4-cli-alpine AS test

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && apk del postgresql-dev \
    && apk add --no-cache libpq unzip

# Brings Composer in from the official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /usr/src/dns-record-manager

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist

COPY . .

# Builds the compiled Tailwind CSS
FROM node:20-alpine AS tailwind

WORKDIR /usr/src/dns-record-manager

COPY package.json package-lock.json* ./
RUN npm install

COPY app/Views ./app/Views
COPY public/assets/js ./public/assets/js
COPY public/assets/css/input.css ./public/assets/css/input.css

RUN npx @tailwindcss/cli \
    -i public/assets/css/input.css \
    -o public/assets/css/main.css \
    --minify

# Builds the final run time environement
FROM php:8.4-cli-alpine AS production

LABEL authors="kieranpritchard"

# Install system dependencies required for PostgreSQL and compile extensions
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && apk del postgresql-dev \
    && apk add --no-cache libpq

WORKDIR /usr/src/dns-record-manager

# Copy application source code
COPY . .
COPY --from=tailwind /usr/src/dns-record-manager/public/assets/css/main.css ./public/assets/css/main.css

# Overwrite vendor folder with cleaned production dependencies from build stage
COPY --from=build /usr/src/dns-record-manager/vendor ./vendor

# Force Docker to execute the 'test' stage and ensure PHPUnit passes before finishing the build
COPY --from=test /usr/src/dns-record-manager/composer.json ./tests-passed.flag
RUN rm ./tests-passed.flag

# Expose port and bind to all interfaces
EXPOSE 3000
CMD ["php", "-S", "0.0.0.0:3000", "-t", "public", "router.php"]