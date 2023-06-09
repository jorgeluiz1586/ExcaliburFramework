<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit53087e06849b7dabe5482bd3bc44d0d9
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Excalibur\\Framework\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Excalibur\\Framework\\' => 
        array (
            0 => __DIR__ . '/../..' . '/framework/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit53087e06849b7dabe5482bd3bc44d0d9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit53087e06849b7dabe5482bd3bc44d0d9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit53087e06849b7dabe5482bd3bc44d0d9::$classMap;

        }, null, ClassLoader::class);
    }
}
