#!/bin/bash
# maintainer - Lazar Ivkovic

temp_dir=$(mktemp -d)

github_url="https://gitlab.com/mailcare/mailcare/-/archive/master/mailcare-master.tar.gz"
destination_dir="src"

curl -L "$github_url" | tar xvz --strip-components=1 -C "$temp_dir"

rsync -a --exclude='composer.json' --exclude='composer.lock' --exclude='package-lock.json' --exclude='package.json' --exclude='README.md' --exclude='.github/' --update --ignore-existing "$temp_dir/" "$destination_dir/"

git add .
git commit -m "sync from source for new release"
git push origin master

rm -r "$temp_dir"

echo "Done"