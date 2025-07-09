<?php

namespace NGSOFT\Routing\Interface;

use Psr\Container\ContainerInterface;

interface ContainerFactoryInterface
{
    public function createContainer(array $definitions): ContainerInterface;
}
