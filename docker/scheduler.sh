#!/usr/bin/env bash

while true; do
    echo "Running laravel cron"
    cd /mailcare && php artisan schedule:run
    sleep 60
done