<?php

namespace ION\Ext\Builder;


interface BuilderInterface
{

    public function build(array $env);

    public function copyTo(string $path);

    public function cleanup();
}