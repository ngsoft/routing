<?php

namespace NGSOFT\Routing\Internal;

use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;

/**
 * @internal
 */
trait MiddlewareCollector
{
    private array $middlewares = [];

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param class-string|\Closure|MiddlewareInterface|PsrMiddlewareInterface $middleware
     *
     * @return static
     */
    public function add(\Closure|MiddlewareInterface|PsrMiddlewareInterface|string $middleware)
    {
        if ( ! in_array($middleware, $this->middlewares, true))
        {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * @param class-string|\Closure|MiddlewareInterface|PsrMiddlewareInterface $middleware
     *
     * @return static
     */
    public function prepend(\Closure|MiddlewareInterface|PsrMiddlewareInterface|string $middleware)
    {
        if ( ! in_array($middleware, $this->middlewares, true))
        {
            $this->middlewares = [$middleware, ...$this->middlewares];
        }

        return $this;
    }
}
