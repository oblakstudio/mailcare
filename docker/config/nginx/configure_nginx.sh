#!/bin/bash

# link
ln -sf /dev/stdout /var/log/nginx/access.log
ln -sf /dev/stderr /var/log/nginx/error.log

# files
sed -i 's/user www-data;/user nginx;/' /etc/nginx/nginx.conf
sed -i 's,/run/nginx.pid,/tmp/nginx.pid,' /etc/nginx/nginx.conf
sed -i "/^http {/a \    proxy_temp_path /tmp/proxy_temp;\n    client_body_temp_path /tmp/client_temp;\n    fastcgi_temp_path /tmp/fastcgi_temp;\n    uwsgi_temp_path /tmp/uwsgi_temp;\n    scgi_temp_path /tmp/scgi_temp;\n" /etc/nginx/nginx.conf

# dirs
mkdir -p /var/cache/nginx

# owner perm
chown -R $NUID:0 /var/cache/nginx
chmod -R g+w /var/cache/nginx
chown -R $NUID:0 /etc/nginx
chmod -R g+w /etc/nginx

chown -h $NUID:0 /var/log/nginx/access.log
chown -h $NUID:0 /var/log/nginx/error.log
chown -R $NUID:0 /var/log/nginx

# user
groupadd --system --gid $NGID nginx || true
useradd --system --gid nginx --no-create-home --home /nonexistent --comment "nginx user" --shell /bin/false --uid $NUID nginx || true

# clean
rm -rf /etc/nginx/sites-enabled/*
