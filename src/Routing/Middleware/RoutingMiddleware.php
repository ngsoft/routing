<?php

namespace NGSOFT\Routing\Middleware;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use NGSOFT\Routing\Internal\AttributeManager;
use NGSOFT\Routing\Internal\FastRouteResult;
use NGSOFT\Routing\Route;
use NGSOFT\Routing\RouteContext;
use NGSOFT\Routing\RouteGenerator;
use NGSOFT\Routing\Router;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoutingMiddleware implements MiddlewareInterface
{
    use AttributeManager;

    public function __construct(
        private readonly Router $router,
        private readonly RouteGenerator $routeGenerator
    ) {}

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if ( ! count($this->router))
        {
            throw new NotFoundHttpException('No routes are defined.');
        }
        $path                               = $request->getPathInfo();
        $method                             = $request->getMethod();
        $dispatcher                         = new GroupCountBased($this->router->getRouteCollector()->getData());
        @list($status, $route, $parameters) = $dispatcher->dispatch($method, $path);

        $status                             = (int) $status;
        $result                             = new FastRouteResult(
            $status,
            match ($status)
            {
                Dispatcher::METHOD_NOT_ALLOWED, Dispatcher::NOT_FOUND => null,
                default => $route
            },
            $request->getMethod(),
            $request->getPathInfo(),
            match ($status)
            {
                Dispatcher::NOT_FOUND          => [],
                Dispatcher::METHOD_NOT_ALLOWED => $route,
                default                        => $parameters ?? []
            },
            $route instanceof Route ? $route->getMethods() : [],
        );

        return $handler->handle(
            $this->setAttributes($request, [
                RouteContext::GENERATOR => $this->routeGenerator,
                RouteContext::RESULTS   => $result,
            ])
        );
    }
}
