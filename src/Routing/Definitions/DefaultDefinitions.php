<?php

namespace NGSOFT\Routing\Definitions;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use NGSOFT\Routing\Interface\RouteCollectorInterface;
use NGSOFT\Routing\Internal\RequestHandler;
use NGSOFT\Routing\Router;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;

class DefaultDefinitions
{
    public function __invoke(): array
    {
        return [
            LoggerInterface::class         => function (): LoggerInterface
            {
                return new NullLogger();
            },
            RequestHandlerInterface::class => function (ContainerInterface $container): RequestHandlerInterface
            {
                return $container->get(RequestHandler::class);
            },
            RouteCollectorInterface::class => function (ContainerInterface $container): RouteCollectorInterface
            {
                return $container->get(Router::class);
            },
            Router::class                  => function (): Router
            {
                return new Router(new RouteCollector(
                    new Std(),
                    new GroupCountBased()
                ));
            },
        ];
    }
}
