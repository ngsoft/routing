<?php

namespace NGSOFT\Routing\Interface;

use NGSOFT\Routing\Route;

interface RouteCollectorInterface
{
    public function map(array $methods, string $path, callable|string $handler): Route;

    public function group(string $path, callable $handler): MiddlewareCollectionInterface;

    public function get(string $path, callable|string $handler): Route;

    public function post(string $path, callable|string $handler): Route;

    public function put(string $path, callable|string $handler): Route;

    public function patch(string $path, callable|string $handler): Route;

    public function delete(string $path, callable|string $handler): Route;

    public function options(string $path, callable|string $handler): Route;

    public function any(string $pattern, callable|string $handler): Route;
}
