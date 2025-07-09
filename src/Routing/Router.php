<?php

namespace NGSOFT\Routing;

use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use NGSOFT\Routing\Interface\MiddlewareCollectionInterface;
use NGSOFT\Routing\Interface\RouteCollectorInterface;
use NGSOFT\Routing\Internal\MethodFiltering;
use NGSOFT\Routing\Internal\MiddlewareCollector;
use NGSOFT\Routing\Internal\RouteCollection;
use NGSOFT\Routing\Middleware\RouteMatcherMiddleware;
use NGSOFT\Routing\Middleware\RoutingMiddleware;
use Symfony\Component\HttpFoundation\Request;

class Router implements Version, \Countable, \IteratorAggregate, RouteCollectorInterface
{
    use RouteCollection;
    use MiddlewareCollector;
    use MethodFiltering;

    private RouteCollector $collector;
    private ?Request $request = null;
    private ?string $basePath = null;
    private array $routes     = [];

    public function __construct(?RouteCollector $collector = null)
    {
        $this->collector = $collector ?? new RouteCollector(
            new Std(),
            new GroupCountBasedGenerator()
        );
        $this
            ->add(RoutingMiddleware::class)
            ->add(RouteMatcherMiddleware::class);
    }

    public function register(Route $route): static
    {
        $this->routes[] = $route;
        return $this;
    }

    public function getRequest(): Request
    {
        if ( ! $this->request)
        {
            // prevent errors if used in cli context
            $_SERVER['SERVER_NAME'] ??= 'localhost';
            $_SERVER['SERVER_ADDR'] ??= '127.0.0.1';
            $this->setRequest(Request::createFromGlobals());
        }

        return $this->request;
    }

    public function setRequest(Request $request): Router
    {
        $this->request = $request;
        $this->setBasePath($request->getBasePath());
        return $this;
    }

    public function getRouteCollector(): RouteCollector
    {
        return $this->collector;
    }

    public function map(array $methods, string $path, callable|string $handler): Route
    {
        if (empty($methods = $this->filterMethods($methods)))
        {
            throw new \InvalidArgumentException('HTTP methods cannot be empty');
        }
        $path = $this->normalize($path);
        $this->register(
            $route = new Route($methods, $path, $handler)
        );
        $this->collector->addRoute($methods, $path, $route);
        return $route;
    }

    public function group(string $path, callable $handler): MiddlewareCollectionInterface
    {
        $path  = $this->normalize($path);
        $group = new RouteGroup($path, $handler, $this);
        $this->collector->addGroup($path, $group);
        return $group;
    }

    public function getBasePath(): string
    {
        return $this->basePath ?? '';
    }

    public function setBasePath(string $basePath): static
    {
        $this->basePath = ltrim($basePath, '/');
        return $this;
    }

    public function count(): int
    {
        return count($this->routes);
    }

    /**
     * @return \Traversable<Route>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->routes;
    }

    private function normalize(string $path): string
    {
        if (in_array($path, ['', '/']))
        {
            return '/';
        }

        return preg_replace(
            '#/+#',
            '/',
            sprintf('/%s', trim($path, '/'))
        );
    }
}
