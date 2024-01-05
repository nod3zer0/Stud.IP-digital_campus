#!/bin/bash

PUSH=$1

build_image () {
  docker build -t studip/$2 --platform=linux/amd64 -f $1/Dockerfile .
  if [[ $PUSH = 'push' ]]; then
    docker push studip/$2
  fi
}

build_image tests/php82 studip:tests-php8.2 &
build_image tests/php74 studip:tests-php7.4 &
build_image release-cli release-cli &
wait

echo "Images built"
