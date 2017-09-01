<?php

return [
    "matrix" => [
        "ubuntu" => [
            "UBUNTU_VERSION" => [
//                "12.04",
//                "14.04",
                "16.04",
//                "17.04"
            ],
            "PHP_RELEASE"    => [
                "php7.0" => "php-7.0.23",
                "php7.1" => "php-7.1.8",
                "php7.2" => "php-7.2.0RC1"
            ],
            "PHP_DEBUG"      => [
                "debug" => "--enable-debug",
                "non-debug" => ""
            ],
            "PHP_ZTS"        => [
                "zts" => "--enable-maintainer-zts",
                "nts" => ""
            ],
        ],
//        "debian" => [
//            "DEBIAN_VERSION" => [
//                "8",
//                "7",
//            ],
//            "PHP_RELEASE"    => [
//                "php70" => "php-7.0.23",
//                "php71" => "php-7.1.8",
//                "php72" => "php-7.2.0RC1"
//            ],
//            "PHP_DEBUG"      => [
//                "debug" => "--enable-debug",
//                "non-debug" => ""
//            ],
//            "PHP_ZTS"        => [
//                "zts" => "--enable-maintainer-zts",
//                "nts" => ""
//            ],
//        ]
    ],
];