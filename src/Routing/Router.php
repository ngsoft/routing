<?php

declare(strict_types=1);

namespace NGSOFT\Routing;

use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use NGSOFT\Routing\Interface\MiddlewareCollectionInterface;
use NGSOFT\Routing\Interface\RouteCollectorInterface;
use NGSOFT\Routing\Internal\FallbackRouteMiddleware;
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

    private Routing $routing;
    private bool $hasFallback = false;

    public function __construct(?RouteCollector $collector = null)
    {
        $this->collector = $collector ?? new RouteCollector(
            new Std(),
            new GroupCountBasedGenerator()
        );
        $this->add(RoutingMiddleware::class)->add(RouteMatcherMiddleware::class);
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
            // set default variables to prevent PSR-7 adapters errors when in CLI
            $_SERVER = array_replace([
                'SERVER_NAME'          => 'localhost',
                'SERVER_PORT'          => 80,
                'HTTP_HOST'            => 'localhost',
                'HTTP_USER_AGENT'      => 'Symfony',
                'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
                'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'REMOTE_ADDR'          => '127.0.0.1',
                'SERVER_ADDR'          => '127.0.0.1',
                'SCRIPT_NAME'          => '',
                'SCRIPT_FILENAME'      => '',
                'SERVER_PROTOCOL'      => 'HTTP/1.1',
                'REQUEST_TIME'         => time(),
                'REQUEST_TIME_FLOAT'   => microtime(true),
            ], $_SERVER);

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

    /**
     * Set a fallback route (when 404 error).
     *
     * @param array|callable|string $handler
     *
     * @return Route
     */
    public function setFallbackRoute(array|callable|string $handler): Route
    {
        if ($this->hasFallback)
        {
            throw new \LogicException('Fallback already set');
        }
        $this->hasFallback = true;
        $route             = new Route(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '', $handler);
        $this->add($this->getRouting()->getContainer()->get(FallbackRouteMiddleware::class)
            ->setRoute($route));
        return $route;
    }

    public function map(array $methods, string $path, array|callable|string $handler): Route
    {
        $methods = $this->checkStarMethod($methods, $path);

        if (empty($methods = $this->filterMethods($methods)))
        {
            throw new \InvalidArgumentException('HTTP methods cannot be empty');
        }
        $path    = $this->normalize($path);
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

    public function getRouting(): Routing
    {
        return $this->routing;
    }

    public function setRouting(Routing $routing): static
    {
        $this->routing ??= $routing;
        return $this;
    }

    private function checkStarMethod(array $methods, string $path): array
    {
        if (['*'] === $methods)
        {
            $methods = array_combine(
                ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
            );

            foreach ($this as $route)
            {
                if ($route->getPattern() === $path)
                {
                    foreach ($route->getMethods() as $method)
                    {
                        unset($methods[$method]);
                    }
                }
            }
            return array_values($methods);
        }

        return $methods;
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
