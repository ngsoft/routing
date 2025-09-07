<?php

declare(strict_types=1);

namespace NGSOFT\Routing;

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

    public function __construct(
        string $prefix,
        callable $callback,
        private readonly Router $router,
        private readonly ?RouteGroup $group = null
    ) {
        $this->callback = $callback;
        $this->prefix   = sprintf('/%s', ltrim($prefix, '/'));
    }

    public function __invoke()
    {
        ($this->callback)($this);
    }

    public function map(array $methods, string $path, array|callable|string $handler): Route
    {
        $isStar = $this->checkStarMethod($methods, $path);

        if (empty($methods = $this->filterMethods($methods)))
        {
            throw new \InvalidArgumentException('HTTP methods cannot be empty');
        }
        $this->router->register(
            $route = new Route($methods, $this->path($path), $handler, $this)
        );

        $this->router->getRouteCollector()->addRoute($isStar ? ['*'] : $methods, $path, $route);
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

    private function checkStarMethod(array &$methods, string $path): bool
    {
        if (['*'] === $methods)
        {
            $methods = array_combine(
                ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
            );

            $pth     = $this->path($path);

            foreach ($this->router as $route)
            {
                if ($route->getPattern() === $pth)
                {
                    foreach ($route->getMethods() as $method)
                    {
                        unset($methods[$method]);
                    }
                }
            }
            return true;
        }

        return false;
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
