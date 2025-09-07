<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use NGSOFT\Container\Container;
use NGSOFT\Reflection\Reflect;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class RouteInvoker
{
    private readonly Reflect $reflector;

    public function __construct(private ContainerInterface $container)
    {
        $this->reflector = new Reflect();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container): RouteInvoker
    {
        $this->container = $container;
        return $this;
    }

    public function invoke(array|callable|string $callable, Request $request, array $parameters = []): Response
    {
        $container = $this->getContainer();

        // Default Container
        if ($container instanceof Container)
        {
            if (is_string($callable))
            {
                $parts = preg_split('#[@:]+#', $callable);

                if (2 === count($parts))
                {
                    $callable = $parts;
                } elseif (1 === count($parts)
                    && class_exists($parts[0])
                    && method_exists($parts[0], '__invoke')
                ) {
                    $callable = [$callable, '__invoke'];
                }
            }

            if (is_array($callable) && 2 === count($callable))
            {
                if (class_exists($callable[0]) && method_exists($callable[0], $callable[1]))
                {
                    try
                    {
                        $instance    = $container->get($callable[0]);
                        $callable[0] = $instance;
                    } catch (ContainerExceptionInterface)
                    {
                    }
                }
            }

            try
            {
                $params   = $this->reflector->reflect($callable);
                $named    = [];
                $list     = [];
                $variadic = array_values($parameters);

                foreach ($params as $name => $item)
                {
                    if (in_array(Request::class, $item->getTypes()))
                    {
                        $named[$name] = $request;
                        $list[]       = $request;
                        continue;
                    }

                    if (in_array($name, array_keys($parameters)))
                    {
                        $named[$name] = $parameters[$name];
                        $list[]       = $parameters[$name];
                    }
                }

                // try using $named
                try
                {
                    return $container->call($callable, $named);
                } catch (ContainerExceptionInterface)
                {
                }

                // try using $list
                try
                {
                    return $container->call($callable, $list);
                } catch (ContainerExceptionInterface)
                {
                }

                // try using $variadic
                try
                {
                    return $container->call($callable, $variadic);
                } catch (ContainerExceptionInterface)
                {
                }

                // try using none
                try
                {
                    return $container->call($callable);
                } catch (ContainerExceptionInterface)
                {
                }
            } catch (\Throwable)
            {
            }
        }

        // fallback (or another container (can fatal if too many required parameters))
        return $callable($request);
    }
}
