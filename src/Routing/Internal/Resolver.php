<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use NGSOFT\Routing\Adapter\HttpMessageBridge;
use NGSOFT\Routing\Interface\HighPriorityMiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface as PSRMiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;

/**
 * @internal
 */
class Resolver
{
    private ?HttpMessageBridge $bridge;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    public function resolveQueue(array $queue): array
    {
        $high    = [];
        $regular = [];

        foreach ($queue as $value)
        {
            $middleware = $this->resolveMiddleware($value);

            if ($middleware instanceof HighPriorityMiddlewareInterface)
            {
                $high[] = $middleware;
                continue;
            }

            $regular[]  = $middleware;
        }

        return array_merge($high, $regular);
    }

    public function resolve(array|callable|object|string $identifier): mixed
    {
        if (is_object($identifier) || is_callable($identifier))
        {
            return $identifier;
        }

        if (is_string($identifier))
        {
            @list($class, $method) = preg_split('#[@:]+#', $identifier, 2);

            if ($method)
            {
                $identifier = [$class, $method];
            }
        }

        if (is_string($identifier))
        {
            return $this->container->get($identifier);
        }

        if (is_array($identifier))
        {
            $identifier[0] = $this->container->get($identifier[0]);

            if ( ! method_exists($identifier[0], $identifier[1]))
            {
                throw new \RuntimeException(sprintf('Method %s::%s() does not exist', get_class($identifier[0]), $identifier[1]));
            }
        }

        return $identifier;
    }

    private function resolveMiddleware(callable|MiddlewareInterface|PSRMiddlewareInterface|RequestHandlerInterface|string $middleware): MiddlewareInterface|RequestHandlerInterface
    {
        $middleware = $this->resolve($middleware);

        if ($middleware instanceof PSRMiddlewareInterface)
        {
            return new BridgedMiddleware($middleware, $this->getBridge());
        }

        if ($middleware instanceof MiddlewareInterface || $middleware instanceof RequestHandlerInterface)
        {
            return $middleware;
        }

        if (is_callable($middleware))
        {
            return new CallableMiddleware($middleware);
        }

        throw new \RuntimeException('Invalid middleware provided');
    }

    private function getBridge(): HttpMessageBridge
    {
        return $this->bridge ??= $this->container->get(HttpMessageBridge::class);
    }
}
