#!/bin/sh

php artisan mailcare:configure-postfix /etc/postfix "$MAILCARE_DOMAIN" mailcare

su - mailcare -c "php /mailcare/artisan cache:clear"
su - mailcare -c "php /mailcare/artisan route:cache"
su - mailcare -c "php /mailcare/artisan config:clear"
su - mailcare -c "php /mailcare/artisan config:cache"

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf