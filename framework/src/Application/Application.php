<?php

declare(strict_types=1);

namespace Modular\Framework;

use Infrastructure\Http\Interfaces\KernelInterface;

class Application
{
    private array $providers = [];
    private array $config    = [];
    private array $alias     = [];

    public function __construct(private KernelInterface $kernel)
    {}

    public function setProvider(object $provider)
    {
        $this->providers[] = $provider;
    }

    public function setConfiguration(object $config)
    {
        $this->config[$config->key] = $config->value;
    }

    public function setAlias(object $alias)
    {
        $this->alias[$alias->key] = $alias->value;
    }

    public function start()
    {
        $this->kernel->run();
    }
}
