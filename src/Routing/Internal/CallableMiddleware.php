<?php

namespace NGSOFT\Routing\Internal;

use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class CallableMiddleware implements MiddlewareInterface
{
    public function __construct(private $middleware) {}

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        return ($this->middleware)($request, $handler);
    }
}
