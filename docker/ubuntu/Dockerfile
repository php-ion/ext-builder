
ARG UBUNTU_VERSION="14.04"

FROM ubuntu:$UBUNTU_VERSION

ARG PHP_RELEASE="php-7.0.23"
ARG PHP_DEBUG="--enable-debug"
ARG PHP_ZTS="--enable-maintainer-zts"
ARG ION_RELEASE="0.8"
ARG ION_DEBUG=""
ARG CFLAGS=""

MAINTAINER Ivan Shalagnov <ivan@shalganov.me>

ENV DEBIAN_FRONTEND noninteractive


# Setup debian
RUN apt-get update && apt-get install -y \
    bison \
    autoconf \
    automake \
    libtool \
    g++ \
    gcc \
    libc-dev \
    make \
    pkg-config \
    re2c \
    ca-certificates \
    curl \
    libedit2 \
    libsqlite3-0 \
    libxml2 \
    xz-utils \
    git-core \
    libcurl4-openssl-dev \
    libedit-dev \
    libsqlite3-dev \
    libssl-dev \
    libxml2-dev \
    libevent-dev \
    unzip         \
    --no-install-recommends


# Build php
ENV PHP_INI_DIR /usr/local/etc/php
RUN mkdir -p $PHP_INI_DIR/conf.d


RUN curl -fSL "https://github.com/php/php-src/archive/$PHP_RELEASE.zip" -o /usr/src/php-src.zip

RUN cd /usr/src \
    && unzip -q php-src.zip \
    && cd "/usr/src/php-src-$PHP_RELEASE" \
    && ./buildconf --force \
    && ./configure \
        \
		--with-config-file-path="$PHP_INI_DIR" \
        --with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
        \
        --disable-cgi \
        \
        --with-curl \
        --with-libedit \
        --with-openssl \
        --with-zlib \
        \
        --enable-debug \
        --enable-pcntl  \
        --enable-maintainer-zts \
         \
     && make -j"$(nproc)" \
     && make install  \
     && make clean

RUN php -m && php -v

RUN git clone https://github.com/php-ion/php-ion.git /usr/src/php-ion \
    && cd "/usr/src/php-ion" \
    && git checkout $ION_RELEASE

RUN cd "/usr/src/php-ion" \
    && CFLAGS="$CFLAGS" php bin/ionizer.php $ION_DEBUG --build=/usr/src/ion.so
