<?php

declare(strict_types=1);

namespace NGSOFT\Routing;

use FastRoute\RouteParser\Std;
use NGSOFT\Routing\Interface\UrlGeneratorInterface;

readonly class RouteGenerator implements UrlGeneratorInterface
{
    private Std $routeParser;

    public function __construct(private Router $router)
    {
        $this->routeParser = new Std();
    }

    public function generate(string $name, array $parameters = [], array $query = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return match ($referenceType)
        {
            self::ABSOLUTE_PATH => $this->urlFor($name, $parameters, $query),
            self::ABSOLUTE_URL  => $this->fullUrlFor($name, $parameters, $query),
            self::NETWORK_PATH  => preg_replace('#^\w:#', '', $this->fullUrlFor($name, $parameters, $query)),
            default             => throw new \InvalidArgumentException(sprintf('Invalid reference type: %d', $referenceType))
        };
    }

    private function relativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        $route    = $this->getNamedRoute($routeName);
        $pattern  = $route->getPattern();
        $segments = $this->getSegments($pattern, $data);

        $url      = implode('', $segments);

        if ($queryParams)
        {
            $url .= '?' . http_build_query($queryParams);
        }

        $basePath = $this->router->getBasePath();

        if ($basePath)
        {
            $url = $basePath . $url;
        }

        return $url;
    }

    private function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->relativeUrlFor($routeName, $data, $queryParams);
    }

    private function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        $path     = $this->urlFor($routeName, $data, $queryParams);

        $request  = $this->router->getRequest();
        $scheme   = $request->getScheme();
        $host     = $request->getHost();

        if ( ! in_array($port = $request->getPort(), [80, 443]))
        {
            $host .= ":{$port}";
        }

        $protocol = sprintf('%s://%s', $scheme, $host);

        return $protocol . $path;
    }

    private function getNamedRoute(string $name): Route
    {
        $routes   = $this->router->getRouteCollector()->getData();

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($routes, \RecursiveArrayIterator::CHILD_ARRAYS_ONLY)
        );

        foreach ($iterator as $route)
        {
            if ($route instanceof Route && $name === $route->getName())
            {
                return $route;
            }
        }

        throw new \UnexpectedValueException('Named route does not exist for name: ' . $name);
    }

    private function getSegments(string $pattern, array $data): array
    {
        $segments    = [];
        $segmentName = '';

        $expressions = array_reverse($this->routeParser->parse($pattern));

        foreach ($expressions as $expression)
        {
            foreach ($expression as $segment)
            {
                if (is_string($segment))
                {
                    $segments[] = $segment;
                    continue;
                }

                /** @var string[] $segment */
                if ( ! array_key_exists($segment[0], $data))
                {
                    $segments    = [];
                    $segmentName = $segment[0];
                    break;
                }

                $segments[] = $data[$segment[0]];
            }

            if ( ! $segments)
            {
                break;
            }
        }

        if ( ! $segments)
        {
            throw new \InvalidArgumentException('Missing data for URL segment: ' . $segmentName);
        }

        return $segments;
    }
}
