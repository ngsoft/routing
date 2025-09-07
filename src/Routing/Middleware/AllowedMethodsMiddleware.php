<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Middleware;

use NGSOFT\Routing\Internal\AttributeManager;
use NGSOFT\Routing\Internal\MethodFiltering;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class AllowedMethodsMiddleware implements MiddlewareInterface
{
    use MethodFiltering;
    use AttributeManager;

    public function __construct(private array $allowedMethods = ['GET'])
    {
        $this->allowedMethods = $this->filterMethods($this->allowedMethods);
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        /** @var CorsMiddleware $cors */
        if ($cors = $this->getAttribute($request, CorsMiddleware::class))
        {
            $cors->setAllowedMethods($this->allowedMethods);
        }

        if ( ! in_array($request->getMethod(), $this->allowedMethods))
        {
            throw new MethodNotAllowedHttpException($this->allowedMethods);
        }
        return $handler->handle($request);
    }
}
