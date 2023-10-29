#!/bin/bash

# Enable MailParse extension
phpenmod -v 8.2 mailparse

# PHP 8.2 Setup
# max_execution_time
sed -i 's/^max_execution_time = .*/max_execution_time = 120/' /etc/php/8.2/cli/php.ini
sed -i 's/^max_execution_time = .*/max_execution_time = 120/' /etc/php/8.2/fpm/php.ini

# max_input_time
sed -i 's/^max_input_time = .*/max_input_time = 30/' /etc/php/8.2/cli/php.ini
sed -i 's/^max_input_time = .*/max_input_time = 30/' /etc/php/8.2/fpm/php.ini

# max_input_vars
sed -i 's/^; max_input_vars = .*/max_input_vars = 500/' /etc/php/8.2/cli/php.ini
sed -i 's/^; max_input_vars = .*/max_input_vars = 500/' /etc/php/8.2/fpm/php.ini

# memory_limit
sed -i 's/^memory_limit = .*/memory_limit = 256M/' /etc/php/8.2/cli/php.ini
sed -i 's/^memory_limit = .*/memory_limit = 256M/' /etc/php/8.2/fpm/php.ini

# post_max_size
sed -i 's/^post_max_size = .*/post_max_size = 32M/' /etc/php/8.2/cli/php.ini
sed -i 's/^post_max_size = .*/post_max_size = 32M/' /etc/php/8.2/fpm/php.ini

# upload_max_filesize
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 32M/' /etc/php/8.2/cli/php.ini
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 32M/' /etc/php/8.2/fpm/php.ini

# allow_url_fopen
sed -i 's/^allow_url_fopen = .*/allow_url_fopen = no/' /etc/php/8.2/cli/php.ini
sed -i 's/^allow_url_fopen = .*/allow_url_fopen = no/' /etc/php/8.2/fpm/php.ini
