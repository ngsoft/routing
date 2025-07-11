<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class InvokerHandler implements RequestHandlerInterface
{
    public function __construct(private readonly RouteInvoker $invoker, private $handler, private readonly array $parameters) {}

    public function handle(Request $request): Response
    {
        return $this->invoker->invoke($this->handler, $request, $this->parameters);
    }
}
