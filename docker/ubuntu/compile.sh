#!/usr/bin/env bash
set -e
set -x

mkdir -p $BUILD_PATH/php

curl -fSL "https://github.com/php/php-src/archive/$PHP_RELEASE.zip" -o $BUILD_PATH/php.zip

cd $BUILD_PATH

unzip -q php.zip

cd $BUILD_PATH/php-src-$PHP_RELEASE
./buildconf --force
./configure \
--prefix="$BUILD_PATH/php" \
--disable-all              \
$PHP_DEBUG                 \
$PHP_ZTS


make -j2
make install

$BUILD_PATH/php/bin/php -v

cd $BUILD_PATH

git clone --depth=1 https://github.com/php-ion/php-ion.git --branch $ION_RELEASE --single-branch $BUILD_PATH/php-ion

cd $BUILD_PATH/php-ion

PATH="$BUILD_PATH/php/bin:$PATH" $BUILD_PATH/php/bin/php bin/ionizer.php --build=$BUILD_PATH/ion.so