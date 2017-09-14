<?php

use ION\Ext\Builder;

return [
    "os_map" => [
        "linux" => [
            "*" => "ubuntu-16.4"
        ],
        "macos" => [
            "darwin-16.*" => "darwin-16"
        ]
    ],
    "builds" => [
        "linux" => [
            "mode"   => Builder::MODE_DOCKER,
            "docker_os" => 'ubuntu',
            "matrix" => [
                "OS_RELEASE" => [
                    "ubuntu-16.04" => "16.04"
                ],
                "PHP_RELEASE"    => [
                    "php-7.0" => "php-7.0.23",
                    "php-7.1" => "php-7.1.8",
                ],
                "PHP_DEBUG"      => [
                    "debug" => "--enable-debug",
                    "non-debug" => ""
                ],
                "PHP_ZTS"        => [
                    "zts" => "--enable-maintainer-zts",
                    "nts" => ""
                ],
            ]
        ],
        "macos" => [
            "mode"   =>  Builder::MODE_LOCAL,
            "build_path"  => __DIR__ . '/../build/macos',
            "matrix" => [
                "OS_RELEASE" => [
                    "darwin-16" => "16"
                ],
                "PHP_RELEASE"    => [
                    "php-7.0" => "php-7.0.23",
                    "php-7.1" => "php-7.1.8",
                ],
                "PHP_DEBUG"      => [
                    "debug"     => "--enable-debug",
                    "non-debug" => ""
                ],
                "PHP_ZTS"        => [
                    "zts" => "--enable-maintainer-zts",
                    "nts" => ""
                ],
            ]
        ],
    ],
];