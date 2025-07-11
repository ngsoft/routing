<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class RequestHandler implements RequestHandlerInterface
{
    use AttributeManager;

    public const MIDDLEWARE = '_request_handler';

    public function __construct(private readonly Resolver $resolver) {}

    public function handle(Request $request): Response
    {
        $middlewares = $this->getAttribute($request, self::MIDDLEWARE, []);
        $queue       = $this->resolver->resolveQueue($middlewares);
        reset($queue);
        return (new RequestRunner($queue))->handle($this->removeAttribute($request, self::MIDDLEWARE));
    }
}
