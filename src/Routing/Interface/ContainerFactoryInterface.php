<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Interface;

use Psr\Container\ContainerInterface;

interface ContainerFactoryInterface
{
    public function createContainer(array $definitions): ContainerInterface;
}
