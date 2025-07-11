<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use NGSOFT\Routing\Adapter\HttpMessageBridge;
use Psr\Http\Server\MiddlewareInterface as PSRMiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final readonly class BridgedMiddleware implements MiddlewareInterface
{
    public function __construct(
        private PSRMiddlewareInterface $middleware,
        private HttpMessageBridge $bridge
    ) {}

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $handler  = new BridgedHandler($handler, $this->bridge);
        $response = $this->middleware->process($this->bridge->createRequest($request), $handler);
        return $this->bridge->createFoundationResponse($response);
    }
}
