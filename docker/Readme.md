# How to build ci images

Use the script `build_images.sh` to build all relevant images for ci pipeline
You can trigger an automated push to the studip docker repository by providing the string "push" as first argument. (Permission to push to hub.docker.com/studip is required)

`./build_images.sh push`

All images are automatically built for linux/amd64
