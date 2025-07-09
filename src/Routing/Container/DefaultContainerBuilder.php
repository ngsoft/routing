<?php

namespace NGSOFT\Routing\Container;

use NGSOFT\Container\Container;
use NGSOFT\Routing\Interface\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;

class DefaultContainerBuilder implements ContainerFactoryInterface
{
    public function createContainer(array $definitions): ContainerInterface
    {
        return new Container($definitions);
    }
}
