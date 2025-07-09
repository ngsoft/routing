<?php

namespace NGSOFT\Routing;

use NGSOFT\Routing\Internal\FastRouteResult;
use Symfony\Component\HttpFoundation\Request;

readonly class RouteContext
{
    public const BASE      = '_base';
    public const RESULTS   = '_fast_route_results';
    public const GENERATOR = '_route_generator';

    public function __construct(
        private FastRouteResult $results,
        private RouteGenerator $generator,
        private string $basePath = ''
    ) {}

    public static function fromRequest(Request $request): static
    {
        $generator = $request->attributes->get(self::GENERATOR);
        $results   = $request->attributes->get(self::RESULTS);
        $base      = $request->attributes->get(self::BASE, $request->getBasePath());

        if ( ! $generator || ! $results)
        {
            throw new \RuntimeException(
                'cannot create route context before routing has been completed'
            );
        }

        return new static($results, $generator, $base);
    }

    public function getResults(): FastRouteResult
    {
        return $this->results;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getGenerator(): RouteGenerator
    {
        return $this->generator;
    }

    public function getArguments(): array
    {
        return $this->results->getArguments();
    }

    public function getArgument(string $name, mixed $defaultValue = null): mixed
    {
        return $this->getArguments()[$name] ?? $defaultValue;
    }
}
