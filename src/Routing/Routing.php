<?php

declare(strict_types=1);

namespace NGSOFT\Routing;

use NGSOFT\Routing\Definitions\DefaultDefinitions;
use NGSOFT\Routing\Definitions\HttpMessageDefinitions;
use NGSOFT\Routing\Interface\EmitterInterface;
use NGSOFT\Routing\Interface\MiddlewareCollectionInterface;
use NGSOFT\Routing\Internal\AttributeManager;
use NGSOFT\Routing\Internal\ContainerBuilder;
use NGSOFT\Routing\Internal\RequestHandler;
use NGSOFT\Routing\Internal\RouteCollection;
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Routing implements RequestHandlerInterface, EmitterInterface, MiddlewareCollectionInterface
{
    use RouteCollection;
    use ContainerBuilder;
    use AttributeManager;

    private ?Router $router                          = null;
    private ?RequestHandlerInterface $requestHandler = null;

    public function __construct()
    {
        $this
            ->addDefinitionClass(DefaultDefinitions::class)
            ->addDefinitionClass(HttpMessageDefinitions::class);
    }

    public function addConfiguration(callable $callback): Routing
    {
        $callback($this->getRouter());
        return $this;
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
        return $this->getRouter()->setFallbackRoute($handler);
    }

    public function run(?Request $request = null): void
    {
        if ($request)
        {
            $this->getRouter()->setRequest($request);
        }
        $this->emit($this->handle($request ?? $this->getRouter()->getRequest()));
    }

    public function handle(Request $request): Response
    {
        $request = $this->setAttribute($request, RequestHandler::MIDDLEWARE, $this->getRouter()->getMiddlewares());
        return $this->getRequestHandler()->handle($request);
    }

    public function emit(Response $response): void
    {
        $request = $this->router->getRequest();
        $response->prepare($request)->send();
    }

    public function map(array $methods, string $path, array|callable|string $handler): Route
    {
        return $this->getRouter()->map($methods, $path, $handler);
    }

    public function group(string $path, callable $handler): MiddlewareCollectionInterface
    {
        return $this->getRouter()->group($path, $handler);
    }

    public function add(\Closure|MiddlewareInterface|PsrMiddlewareInterface|string $middleware)
    {
        $this->getRouter()->add($middleware);
        return $this;
    }

    public function prepend(\Closure|MiddlewareInterface|PsrMiddlewareInterface|string $middleware)
    {
        $this->getRouter()->prepend($middleware);
        return $this;
    }

    public function getRouter(): Router
    {
        return $this->router ??= $this->getContainer()->get(Router::class)->setRouting($this);
    }

    private function getRequestHandler(): RequestHandlerInterface
    {
        return $this->getContainer()->get(RequestHandlerInterface::class);
    }
}
