#!/bin/sh -e

cd "$(dirname $0)/.."

CMD=${1:-phpunit}

docker run --rm -it -v"$PWD:/app" -w/app php:${PHP_VERSION:-7.2} ./vendor/bin/${CMD}
