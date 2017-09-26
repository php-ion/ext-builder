
ARG OS_RELEASE="16.04"

FROM ubuntu:$OS_RELEASE

MAINTAINER Ivan Shalagnov <ivan@shalganov.me>

ENV DEBIAN_FRONTEND noninteractive

RUN cat /etc/lsb-release

# Setup ubuntu
RUN apt-get update && apt-get install -y \
    bison                \
    autoconf             \
    automake             \
    libtool              \
    g++                  \
    gcc                  \
    libc-dev             \
    make                 \
    pkg-config           \
    re2c                 \
    ca-certificates      \
    curl                 \
    libedit2             \
    libsqlite3-0         \
    libxml2              \
    xz-utils             \
    git-core             \
    libcurl4-openssl-dev \
    libedit-dev          \
    libsqlite3-dev       \
    libssl-dev           \
    libxml2-dev          \
    libevent-dev         \
    unzip                \
    valgrind             \
    lsb-release          \
    --no-install-recommends


COPY compile.sh /root/compile.sh
RUN chmod +x /root/compile.sh

RUN mkdir -p /root/build
ENV BUILD_PATH="/root/build"

CMD /root/compile.sh