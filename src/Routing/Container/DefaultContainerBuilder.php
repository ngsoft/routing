<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Container;

use NGSOFT\Container\Container;
use NGSOFT\Routing\Interface\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;

readonly class DefaultContainerBuilder implements ContainerFactoryInterface
{
    public function __construct(private ?Container $container = null) {}

    public function createContainer(array $definitions): ContainerInterface
    {
        if ($this->container)
        {
            $this->container->setMany($definitions);
            return $this->container;
        }

        return new Container($definitions);
    }
}
