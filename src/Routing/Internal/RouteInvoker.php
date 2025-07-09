<?php

namespace NGSOFT\Routing\Internal;

use NGSOFT\Container\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class RouteInvoker
{
    public function __construct(private ContainerInterface $container) {}

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container): RouteInvoker
    {
        $this->container = $container;
        return $this;
    }

    public function invoke(callable $callable, Request $request, array $parameters = []): Response
    {
        $container = $this->getContainer();

        // Default Container
        if ($container instanceof Container)
        {
            // try using provided named parameters
            try
            {
                return $container->call($callable, ['request' => $request] + $parameters);
            } catch (ContainerExceptionInterface)
            {
            }

            // try using provided parameters if names in method are different from the route
            try
            {
                return $container->call($callable, [$request, ...array_values($parameters)]);
            } catch (ContainerExceptionInterface)
            {
            }

            // resolve without parameters, if no named parameters are present in the callable,
            // but we need other dependencies
            try
            {
                return $container->call($callable, [$request]);
            } catch (ContainerExceptionInterface)
            {
            }
        }

        // fallback (or another container (can fatal if too many required parameters))
        return $callable($request);
    }
}
