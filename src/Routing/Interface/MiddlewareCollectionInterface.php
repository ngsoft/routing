<?php

namespace NGSOFT\Routing\Interface;

use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;

interface MiddlewareCollectionInterface
{
    /**
     * @param class-string|\Closure|MiddlewareInterface|PsrMiddlewareInterface $middleware
     *
     * @return static
     */
    public function add(\Closure|MiddlewareInterface|PsrMiddlewareInterface|string $middleware);

    public function prepend(\Closure|MiddlewareInterface|PsrMiddlewareInterface|string $middleware);
}
