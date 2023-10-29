#!/bin/bash
# maintainer - Lazar Ivkovic

temp_dir=$(mktemp -d)

github_url="https://gitlab.com/mailcare/mailcare/-/archive/master/mailcare-master.tar.gz"
destination_dir="src"

curl -L "$github_url" | tar xvz --strip-components=1 -C "$temp_dir"

rsync -a --exclude='.github/' "$temp_dir/" "$destination_dir/"

sed -i "s/\$proxies;/\$proxies = '*';/" src/app/Http/Middleware/TrustProxies.php
sed -i "s/\\\$middleware = \\[/\\\$middleware = \\[\\n\\t\\t\\\\App\\\\Http\\\\Middleware\\\\TrustProxies::class,/g" src/app/Http/Kernel.php

# git add .
# git commit -m "Sync features from source repo mailcare"
# git push origin master

# rm -r "$temp_dir"

# echo "Done"