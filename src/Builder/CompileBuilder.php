<?php

namespace ION\Ext\Builder;


use ION\Ext\Builder;

class CompileBuilder implements BuilderInterface
{

    public $builder;
    public $path;

    public function __construct(Builder $builder, string $path)
    {
        $this->builder = $builder;
        $this->path = $path;
        $this->cleanup();
    }

    public function build(array $env)
    {
        $env['build_path'] = 'BUILD_PATH="'.$this->path.'"';
        if (file_exists('/usr/local/opt/openssl/include')
            && file_exists('/usr/local/opt/openssl/lib/pkgconfig')) { // homebrew ssl
            $env['cflags'] = 'CFLAGS="-I/usr/local/opt/openssl/include $CFLAGS"';
            $env['pkg_path'] = 'PKG_CONFIG_PATH="/usr/local/opt/openssl/lib/pkgconfig"';
        }
        $this->builder->exec(implode(" ", $env) . " bin/compile.sh");
    }

    public function copyTo(string $path)
    {
        $this->builder->exec("cp ". $this->path . '/ion.so ' . $path);
    }

    public function cleanup()
    {
        $this->builder->exec("rm -rf {$this->path}; mkdir -p {$this->path}");
    }
}