<?php

namespace NGSOFT\Routing\Internal;

use NGSOFT\Routing\Adapter\HttpMessageBridge;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as PSRRequestHandlerInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;

/**
 * @internal
 */
final readonly class BridgedHandler implements PSRRequestHandlerInterface
{
    public function __construct(
        private RequestHandlerInterface $handler,
        private HttpMessageBridge $bridge
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->bridge->createResponse(
            $this->handler->handle($this->bridge->createFoundationRequest($request))
        );
    }
}
