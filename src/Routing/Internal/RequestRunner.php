<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class RequestRunner implements RequestHandlerInterface
{
    public function __construct(private array $queue) {}

    public function handle(Request $request): Response
    {
        $middleware = current($this->queue);

        if ( ! $middleware)
        {
            throw new \RuntimeException('At least one middleware must be defined');
        }

        next($this->queue);

        if ($middleware instanceof MiddlewareInterface)
        {
            return $middleware->process($request, $this);
        }

        if ($middleware instanceof RequestHandlerInterface)
        {
            return $middleware->handle($request);
        }

        if (is_callable($middleware))
        {
            return $middleware($request, $this);
        }

        throw new \RuntimeException('Cannot resolve middleware');
    }
}
