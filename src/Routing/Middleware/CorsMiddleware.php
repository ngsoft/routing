<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Middleware;

use NGSOFT\Routing\Internal\AttributeManager;
use NGSOFT\Routing\Internal\FastRouteResult;
use NGSOFT\Routing\RouteContext;
use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;
use Reindeer\SymfonyMiddleware\Contracts\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware implements MiddlewareInterface
{
    use AttributeManager;

    private ?int $maxAge           = null;
    private ?array $allowedOrigins = null;
    private bool $allowCredentials = false;
    private array $allowedMethods  = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
    private array $allowedHeaders  = ['*'];
    private array $exposedHeaders  = [];
    private bool $useCache         = true;
    private bool $overridden       = false;

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $this->getConfigFromRoute($request);

        if (in_array($request->getMethod(), ['OPTIONS', 'HEAD']))
        {
            $response = new Response();
        } else
        {
            $response = $handler->handle($this->setAttribute($request, __CLASS__, $this));
        }

        $origin = $request->headers->get('origin');

        if ($origin && $this->isOriginAllowed($origin))
        {
            $response->headers->set('Access-Control-Allow-Origin', $origin);

            if ($this->allowCredentials)
            {
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }
        } elseif ( ! $this->allowedOrigins)
        {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        if ( ! empty($this->allowedMethods))
        {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        }

        if ( ! empty($this->allowedHeaders))
        {
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        }

        if ( ! empty($this->exposedHeaders))
        {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposedHeaders));
        }

        if (null !== $this->maxAge)
        {
            $response->headers->set('Access-Control-Max-Age', (string) $this->maxAge);

            if ($this->useCache)
            {
                $response->headers->set(
                    'Cache-Control',
                    sprintf('max-age=%d, must-revalidate', $this->maxAge)
                );
            }
        }

        if ( ! $this->useCache)
        {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    public function isOriginAllowed(string $origin): bool
    {
        if ( ! $this->allowedOrigins || in_array('*', $this->allowedOrigins))
        {
            return true;
        }

        return in_array($origin, $this->allowedOrigins);
    }

    public function setMaxAge(?int $maxAge): static
    {
        $this->overridden = true;
        $this->maxAge     = $maxAge;
        return $this;
    }

    public function setAllowedOrigins(?array $allowedOrigins): static
    {
        $this->overridden     = true;
        $this->allowedOrigins = $allowedOrigins;
        return $this;
    }

    public function setAllowCredentials(bool $allowCredentials): static
    {
        $this->overridden       = true;
        $this->allowCredentials = $allowCredentials;
        return $this;
    }

    public function setAllowedMethods(array $allowedMethods): static
    {
        $this->overridden     = true;
        $this->allowedMethods = $allowedMethods;
        return $this;
    }

    public function setAllowedHeaders(array $allowedHeaders): static
    {
        $this->overridden     = true;
        $this->allowedHeaders = $allowedHeaders;
        return $this;
    }

    public function setExposedHeaders(array $exposedHeaders): static
    {
        $this->overridden     = true;
        $this->exposedHeaders = $exposedHeaders;
        return $this;
    }

    public function setUseCache(bool $useCache): static
    {
        $this->overridden = true;
        $this->useCache   = $useCache;
        return $this;
    }

    private function getConfigFromRoute(Request $request)
    {
        if ($this->overridden)
        {
            return;
        }

        $result = $request->attributes->get(RouteContext::RESULTS);

        if ($result instanceof FastRouteResult)
        {
            $this->allowedMethods = $result->getAllowed();
        }
    }
}
