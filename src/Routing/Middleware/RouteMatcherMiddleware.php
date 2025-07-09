<?php

namespace NGSOFT\Routing\Middleware;

use NGSOFT\Routing\Internal\AttributeManager;
use NGSOFT\Routing\Internal\FastRouteResult;
use NGSOFT\Routing\Internal\InvokerHandler;
use NGSOFT\Routing\Internal\RequestHandler;
use NGSOFT\Routing\Internal\Resolver;
use NGSOFT\Routing\Internal\RouteInvoker;
use NGSOFT\Routing\Route;
use NGSOFT\Routing\RouteContext;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteMatcherMiddleware implements MiddlewareInterface
{
    use AttributeManager;

    public function __construct(
        private readonly Resolver $resolver,
        private readonly RouteInvoker $invoker,
        private readonly RequestHandlerInterface $requestHandler,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $result        = $this->getAttribute($request, RouteContext::RESULTS);

        if ( ! $result instanceof FastRouteResult)
        {
            throw new \RuntimeException('An unexpected error occurred while handling routing results.');
        }

        $status        = $result->getStatusCode();

        if (404 === $status)
        {
            $this->logger?->error(vsprintf('%s route not found', [rtrim($request->getBasePath(), '/') . $request->getPathInfo()]));
            throw new NotFoundHttpException();
        }

        if (405 === $status)
        {
            $this->logger?->error(vsprintf('%s bad method %s', [rtrim($request->getBasePath(), '/') . $request->getPathInfo(), $request->getMethod()]));
            throw new MethodNotAllowedHttpException($result->getAllowed());
        }

        $route         = $result->getRoute();

        if ( ! $route)
        {
            throw new \RuntimeException('Route not found.');
        }

        $request       = $this->setAttribute($request, ContainerInterface::class, $this->invoker->getContainer());
        $request       = $this->setAttribute($request, 'parameters', $result->getArguments());
        $middlewares   = $this->getMiddlewares($route);

        // now we add middleware to invoke route
        $middlewares[] = new InvokerHandler($this->invoker, $route->getHandler(), $result->getArguments());

        $request       = $this->setAttribute($request, RequestHandler::MIDDLEWARE, $middlewares);

        $this->logger?->info(vsprintf('%s route matched: %s', [
            rtrim($request->getBasePath(), '/') . $request->getPathInfo(),
            $route,
        ]));

        return $this->requestHandler->handle($request);
    }

    private function getMiddlewares(Route $route): array
    {
        $middlewares = [];

        $group       = $route->getGroup();

        while (null !== $group)
        {
            $stack = $group->getMiddlewares();

            foreach ($stack as $middleware)
            {
                $middlewares[] = $middleware;
            }
            $group = $group->getGroup();
        }

        $middlewares = array_reverse($middlewares);

        $stack       = $route->getMiddlewares();

        foreach ($stack as $middleware)
        {
            $middlewares[] = $middleware;
        }
        return $middlewares;
    }
}
