<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use NGSOFT\Routing\Interface\MiddlewareCollectionInterface;
use NGSOFT\Routing\Route;

/**
 * @internal
 */
trait RouteCollection
{
    abstract public function map(array $methods, string $path, callable|string $handler): Route;

    abstract public function group(string $path, callable $handler): MiddlewareCollectionInterface;

    public function get(string $path, callable|string $handler): Route
    {
        return $this->map(['GET'], $path, $handler);
    }

    public function post(string $path, callable|string $handler): Route
    {
        return $this->map(['POST'], $path, $handler);
    }

    public function put(string $path, callable|string $handler): Route
    {
        return $this->map(['PUT'], $path, $handler);
    }

    public function patch(string $path, callable|string $handler): Route
    {
        return $this->map(['PATCH'], $path, $handler);
    }

    public function delete(string $path, callable|string $handler): Route
    {
        return $this->map(['DELETE'], $path, $handler);
    }

    public function options(string $path, callable|string $handler): Route
    {
        return $this->map(['OPTIONS'], $path, $handler);
    }

    public function any(string $pattern, callable|string $handler): Route
    {
        return $this->map([
            'GET', 'POST',
            'PUT', 'PATCH',
            'DELETE', 'OPTIONS',
        ], $pattern, $handler);
    }
}
