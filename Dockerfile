#Node.js build stage
FROM node:18-alpine as node

# Only copy package.json and package-lock.json first to leverage Docker cache
WORKDIR /app
COPY ./src/package*.json ./
RUN npm ci

# Copy the rest of the code and build
COPY ./src/ /app/
RUN npm run production

# Base image
FROM debian:bullseye-slim as base

LABEL org.opencontainers.image.source="https://github.com/oblakstudio/mailcare" \
    org.opencontainers.image.authors="Oblak Studio <support@oblak.studio>" \
    org.opencontainers.image.title="Mailcare" \
    org.opencontainers.image.description="Docker Optimized Mailcare.io app" \
    org.opencontainers.image.licenses="MIT"

ARG NUID=1001
ARG NGID=1001

ENV DEBIAN_FRONTEND noninteractive

# Prepare
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked,id=var-cache-apt-$TARGETPLATFORM \
    --mount=type=cache,target=/var/lib/apt,sharing=locked,id=var-lib-apt-$TARGETPLATFORM \
    --mount=type=tmpfs,target=/var/cache/apk \
    --mount=type=tmpfs,target=/tmp \
    apt-get update && apt-get -y install lsb-release ca-certificates curl netbase netcat-traditional && \
    curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg && \
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'

# Packages
RUN set -x \
    && apt-get update && apt-get install --no-install-recommends --no-install-suggests -y \
    nginx \
    php8.0-cli \
    php8.0-fpm \
    php8.0-common \
    php8.0-gd \
    php8.0-mbstring \
    php8.0-curl \
    php8.0-dom \
    php8.0-xml \
    php8.0-bcmath \
    php8.0-tokenizer \
    php8.0-pdo \
    php8.0-mysqlnd \
    php8.0-zip \
    php8.0-mailparse \
    postfix \
    tzdata \
    supervisor \
    rsyslog \
    bash \
    zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configure nginx
COPY ./docker/config/nginx/configure_nginx.sh /usr/local/bin/
RUN set -x && \
    chmod +x /usr/local/bin/configure_nginx.sh && \
    sh ./usr/local/bin/configure_nginx.sh && \
    useradd -m -s /bin/bash -d /mailcare mailcare && usermod -aG nginx mailcare

# Copy PHP configuration files
COPY ./docker/config/php/php-fpm.conf /etc/php/8.0/fpm/
COPY ./docker/config/php/php.ini /etc/php/8.0/fpm/php.ini

# PHP Stuff
RUN phpenmod -v 8.0 mailparse && \
    ln -s /dev/stdout /mailcare/php8.0-fpm.log && \
    chown -h mailcare:mailcare /mailcare/php8.0-fpm.log

# Copy Composer from a previous stage
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Switch to the mailcare user and set the working directory
USER mailcare
WORKDIR /mailcare

# Copy the application code
COPY --from=node --chown=mailcare:mailcare /app/ ./

# Remove unnecessary files
RUN rm -rf apiary.apib \
    node_modules LICENSE \
    package.json phpunit.dusk.xml \
    server.php tests \
    node_modules package-lock.json \
    phpunit.xml readme.md

# Install Composer dependencies without dev dependencies
RUN composer install --no-dev

# Clean up Composer cache
RUN rm -rf ~/.composer/cache/*

# Supervisord
COPY ./docker/config/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p /mailcare/storage/app \
    && mkdir -p /mailcare/storage/framework/cache \
    && mkdir -p /mailcare/storage/framework/sessions \
    && mkdir -p /mailcare/storage/framework/views \
    && mkdir -p /mailcare/storage/logs

# Define volumes
VOLUME /mailcare/storage

# Expose ports
EXPOSE 80
EXPOSE 25

# Copy entrypoint and scheduler scripts
COPY --chown=mailcare:mailcare ./docker/entrypoint.sh entrypoint.sh
COPY --chown=mailcare:mailcare ./docker/scheduler.sh scheduler.sh
RUN chmod +x entrypoint.sh
USER root
ENTRYPOINT ["/mailcare/entrypoint.sh"]