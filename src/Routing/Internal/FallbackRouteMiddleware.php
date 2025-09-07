<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use NGSOFT\Routing\Interface\HighPriorityMiddlewareInterface;
use NGSOFT\Routing\Route;
use Psr\Container\ContainerInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 */
class FallbackRouteMiddleware implements HighPriorityMiddlewareInterface
{
    use AttributeManager;

    private ?Route $route = null;

    public function __construct(
        private readonly Resolver $resolver,
        private readonly RouteInvoker $invoker,
        private readonly RequestHandlerInterface $requestHandler,
    ) {}

    public function setRoute(Route $route): static
    {
        $this->route = $route;
        return $this;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        try
        {
            return $handler->handle($request);
        } catch (NotFoundHttpException)
        {
            $params        = ['path' => $request->getPathInfo()];
            $request       = $this->setAttribute($request, ContainerInterface::class, $this->invoker->getContainer());
            $request       = $this->setAttribute($request, 'parameters', $params);
            $middlewares   = $this->route->getMiddlewares();
            // now we add middleware to invoke route
            $middlewares[] = new InvokerHandler(
                $this->invoker,
                ! $request->isMethod(Request::METHOD_OPTIONS)
                    ? $this->route->getHandler()
                    : fn () => new Response(),
                $params
            );
            $request       = $this->setAttribute($request, RequestHandler::MIDDLEWARE, $middlewares);
            return $this->requestHandler->handle($request);
        }
    }
}
