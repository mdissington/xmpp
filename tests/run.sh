#!/bin/sh -e

cd "$(dirname $0)/.."

CMD=${1:-phpunit}

docker run --rm -it -v"$PWD:/app" -w/app php:${PHP_VERSION:-5.6} ./vendor/bin/${CMD}
