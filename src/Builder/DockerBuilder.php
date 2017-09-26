<?php

namespace ION\Ext\Builder;


use ION\Ext\Builder;

class DockerBuilder implements BuilderInterface
{

    /**
     * @var string
     */
    public $image_id;
    /**
     * @var Builder
     */
    public $builder;

    public $container_name = "";

    public function __construct(Builder $builder, $os, $version)
    {
        $this->builder = $builder;
        $this->image_id = "php-ion:ion_$os$version";
        $lines = $builder->exec("docker images --format '{{.Tag}}' | grep ".escapeshellarg("ion_$os$version") . " || true", true);
        if (!$lines) {
            if(!file_exists("docker/$os")) {
                throw new \RuntimeException("Dockerfile for $os not found");
            }
            @unlink("docker/$os/compile.sh");
            copy("bin/compile.sh", "docker/$os/compile.sh");
            $builder->exec("docker build --force-rm --no-cache "
                . "--tag={$this->image_id} "
                . "--build-arg OS_RELEASE=" . escapeshellarg($version) . " "
                . " docker/$os");
        }
    }

    public function build(array $env) {
        $this->container_name = "ion_".implode("_", array_keys($env));
        $this->builder->exec("docker run --name "
            . $this->container_name . " "
            . "--env " . implode(" --env ", $env) . " "
            . $this->image_id);
    }

    public function copyTo(string $path) {
        if($this->container_name) {
            $this->builder->exec("docker cp {$this->container_name}:/root/build/ion.so $path");
        }
    }

    public function cleanup() {
        if($this->container_name) {
            $this->builder->exec("docker rm ".$this->container_name);
            $this->container_name = "";
        }
    }
}