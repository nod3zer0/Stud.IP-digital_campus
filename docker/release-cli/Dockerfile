FROM alpine

RUN apk --no-cache add jq bash curl grep

ADD https://release-cli-downloads.s3.amazonaws.com/latest/release-cli-linux-amd64 /usr/bin/release-cli
RUN chmod u+x /usr/bin/release-cli