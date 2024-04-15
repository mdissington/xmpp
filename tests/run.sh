#!/bin/sh -e

cd "$(dirname $0)/.."

docker run --rm -it -v"$PWD:/app" -w/app php:5.6 ./vendor/bin/phpunit
