#!/bin/bash

PUSH=$1

build_image () {
  docker build -t studip/$2 --platform=linux/amd64 -f $1/Dockerfile .
  if [[ $PUSH = 'push' ]]; then
    docker push studip/$2
  fi
}

build_image tests/php72 studip:tests-php7.2 &
build_image tests/php74 studip:tests &
build_image release-cli release-cli &
wait

echo "Images built"