<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit288f021c40852f7cfee3a278b75a1800
{
    public static $prefixesPsr0 = array (
        'F' => 
        array (
            'Fenom\\' => 
            array (
                0 => __DIR__ . '/..' . '/fenom/fenom/src',
            ),
        ),
    );

    public static $classMap = array (
        'Fenom' => __DIR__ . '/..' . '/fenom/fenom/src/Fenom.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit288f021c40852f7cfee3a278b75a1800::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit288f021c40852f7cfee3a278b75a1800::$classMap;

        }, null, ClassLoader::class);
    }
}