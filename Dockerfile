#Node.js build stage
FROM node:18-alpine as node

# Only copy package.json and package-lock.json first to leverage Docker cache
WORKDIR /app
COPY package*.json ./
RUN npm ci

# Copy the rest of the code and build
COPY . .
RUN npm run production

FROM debian:bullseye-slim as base

LABEL org.opencontainers.image.source="https://github.com/oblakstudio/mailcare" \
    org.opencontainers.image.authors="Oblak Studio <support@oblak.studio>" \
    org.opencontainers.image.title="Mailcare" \
    org.opencontainers.image.description="Docker Optimized Mailcare.io app" \
    org.opencontainers.image.licenses="MIT"

ARG NUID=101
ARG NGID=101

ENV DEBIAN_FRONTEND=noninteractive

# Setup caches
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked,id=var-cache-apt-$TARGETPLATFORM \
    --mount=type=cache,target=/var/lib/apt,sharing=locked,id=var-lib-apt-$TARGETPLATFORM \
    --mount=type=tmpfs,target=/var/cache/apk \
    --mount=type=tmpfs,target=/tmp

# Add deb.sury.org repository
RUN apt-get update && apt-get -y install lsb-release ca-certificates curl netbase netcat-traditional && \
    curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg && \
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'

RUN set -x \
    && groupadd --system --gid $NGID nginx || true \
    && useradd --system --gid nginx --no-create-home --home /nonexistent --comment "nginx user" --shell /bin/false --uid $NUID nginx || true \
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
    zip

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && sed -i 's/user www-data;/user nginx;/' /etc/nginx/nginx.conf \
    && sed -i 's,/run/nginx.pid,/tmp/nginx.pid,' /etc/nginx/nginx.conf \
    && sed -i "/^http {/a \    proxy_temp_path /tmp/proxy_temp;\n    client_body_temp_path /tmp/client_temp;\n    fastcgi_temp_path /tmp/fastcgi_temp;\n    uwsgi_temp_path /tmp/uwsgi_temp;\n    scgi_temp_path /tmp/scgi_temp;\n" /etc/nginx/nginx.conf \
    && mkdir -p /var/cache/nginx \
    && chown -R $NUID:0 /var/cache/nginx \
    && chmod -R g+w /var/cache/nginx \
    && chown -R $NUID:0 /etc/nginx \
    && chmod -R g+w /etc/nginx \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && chown -h $NUID:0 /var/log/nginx/access.log \
    && chown -h $NUID:0 /var/log/nginx/error.log \
    && chown -R $NUID:0 /var/log/nginx \
    && rm -rf /etc/nginx/sites-enabled/*

COPY ./docker/config/nginx/mailcare.conf /etc/nginx/sites-enabled

# Create the mailcare user with home directory at /mailcare
RUN useradd -m -s /bin/bash -d /mailcare mailcare && usermod -aG nginx mailcare

#PHP Stuff
RUN rm -rf /etc/php/8.0/fpm/pool.d/*
COPY ./docker/config/php/php-fpm.conf /etc/php/8.0/fpm/
RUN phpenmod -v 8.0 mailparse && \
    sed -i 's/^max_execution_time = .*/max_execution_time = 120/' /etc/php/8.0/cli/php.ini && \
    sed -i 's/^max_execution_time = .*/max_execution_time = 120/' /etc/php/8.0/fpm/php.ini && \
    sed -i 's/^max_input_time = .*/max_input_time = 30/' /etc/php/8.0/cli/php.ini && \
    sed -i 's/^max_input_time = .*/max_input_time = 30/' /etc/php/8.0/fpm/php.ini && \ 
    sed -i 's/^; max_input_vars = .*/max_input_vars = 500/' /etc/php/8.0/cli/php.ini && \
    sed -i 's/^; max_input_vars = .*/max_input_vars = 500/' /etc/php/8.0/fpm/php.ini && \
    sed -i 's/^memory_limit = .*/memory_limit = 256M/' /etc/php/8.0/cli/php.ini && \
    sed -i 's/^memory_limit = .*/memory_limit = 256M/' /etc/php/8.0/fpm/php.ini && \
    sed -i 's/^post_max_size = .*/post_max_size = 32M/' /etc/php/8.0/cli/php.ini && \
    sed -i 's/^post_max_size = .*/post_max_size = 32M/' /etc/php/8.0/fpm/php.ini && \
    sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 32M/' /etc/php/8.0/cli/php.ini && \
    sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 32M/' /etc/php/8.0/fpm/php.ini && \
    sed -i 's/^allow_url_fopen = .*/allow_url_fopen = no/' /etc/php/8.0/cli/php.ini && \
    sed -i 's/^allow_url_fopen = .*/allow_url_fopen = no/' /etc/php/8.0/fpm/php.ini && \
    ln -s /dev/stdout /mailcare/php8.0-fpm.log && \
    chown -h mailcare:mailcare /mailcare/php8.0-fpm.log

COPY --from=composer /usr/bin/composer /usr/bin/composer

USER mailcare
WORKDIR /mailcare
COPY --from=node --chown=mailcare:mailcare /app/ ./
RUN rm -rf apiary.apib \
    node_modules docker LICENSE \
    package.json phpunit.dusk.xml \
    server.php tests Dockerfile \
    node_modules package-lock.json \
    phpunit.xml readme.md

RUN composer install --no-dev
RUN rm -rf ~/.composer/cache/*

#Supervisord
COPY ./docker/config/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /mailcare/storage/app \
    && mkdir -p /mailcare/storage/framework/cache \
    && mkdir -p /mailcare/storage/framework/sessions \
    && mkdir -p /mailcare/storage/framework/views \
    && mkdir -p /mailcare/storage/logs

VOLUME /mailcare/storage

EXPOSE 80
EXPOSE 25
COPY --chown=mailcare:mailcare ./docker/entrypoint.sh entrypoint.sh
COPY --chown=mailcare:mailcare ./docker/scheduler.sh scheduler.sh
RUN chmod +x entrypoint.sh
USER root
ENTRYPOINT ["/mailcare/entrypoint.sh"]
