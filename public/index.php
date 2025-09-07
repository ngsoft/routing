<?php

declare(strict_types=1);

use NGSOFT\Routing\Middleware\CorsMiddleware;
use NGSOFT\Routing\Middleware\JsonHttpErrorMiddleware;
use NGSOFT\Routing\RouteGroup;
use NGSOFT\Routing\Router;
use NGSOFT\Routing\Routing;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$router = new Routing();

$router->addConfiguration(function (Router $router)
{
    $router->add(new JsonHttpErrorMiddleware());

    $router->group('/api', function (RouteGroup $router)
    {
        $router->get('/test', function ()
        {
            return new Response('ok');
        });

        $router->put('/test', function ()
        {
            return new Response('PUT ok');
        });
    })->add(CorsMiddleware::class);

    $router->setFallbackRoute(function ($path)
    {
        return new Response('404 Not Found: ' . $path, 404);
    });
});

$router->run();
