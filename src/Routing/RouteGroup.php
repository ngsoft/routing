<?php

declare(strict_types=1);

namespace NGSOFT\Routing;

use FastRoute\RouteCollector;
use NGSOFT\Routing\Interface\MiddlewareCollectionInterface;
use NGSOFT\Routing\Interface\RouteCollectorInterface;
use NGSOFT\Routing\Internal\MethodFiltering;
use NGSOFT\Routing\Internal\MiddlewareCollector;
use NGSOFT\Routing\Internal\RouteCollection;

class RouteGroup implements MiddlewareCollectionInterface, RouteCollectorInterface
{
    use MiddlewareCollector;
    use RouteCollection;
    use MethodFiltering;

    /** @var callable */
    private $callback;
    private string $prefix;
    private RouteCollector $collector;

    public function __construct(
        string $prefix,
        callable $callback,
        private readonly Router $router,
        private readonly ?RouteGroup $group = null
    ) {
        $this->callback  = $callback;
        $this->prefix    = sprintf('/%s', ltrim($prefix, '/'));
        $this->collector = $this->router->getRouteCollector();
    }

    public function __invoke()
    {
        ($this->callback)($this);
    }

    public function map(array $methods, string $path, array|callable|string $handler): Route
    {
        if (empty($methods = $this->filterMethods($methods)))
        {
            throw new \InvalidArgumentException('HTTP methods cannot be empty');
        }
        $this->router->register(
            $route = new Route($methods, $this->path($path), $handler, $this)
        );
        $this->router->getRouteCollector()->addRoute($methods, $path, $route);
        return $route;
    }

    public function group(string $path, callable $handler): MiddlewareCollectionInterface
    {
        $group = new RouteGroup($this->path($path), $handler, $this->router, $this);
        $this->router->getRouteCollector()->addGroup($path, $group);
        return $group;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getGroup(): ?RouteGroup
    {
        return $this->group;
    }

    private function path(string $path): string
    {
        $pth = $this->prefix;

        if ($trimmed = ltrim($path, '/'))
        {
            $pth .= sprintf('/%s', $trimmed);
        }

        return $pth;
    }
}
