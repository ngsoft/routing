<?php

namespace NGSOFT\Routing;

use NGSOFT\Routing\Interface\MiddlewareCollectionInterface;
use NGSOFT\Routing\Internal\MethodFiltering;
use NGSOFT\Routing\Internal\MiddlewareCollector;

class Route implements MiddlewareCollectionInterface, \Stringable
{
    use MethodFiltering;
    use MiddlewareCollector;

    private readonly array $methods;

    /** @var callable|string */
    private $handler;

    private ?string $name = null;

    public function __construct(
        array $methods,
        private readonly string $pattern,
        callable|string $handler,
        private readonly ?RouteGroup $group = null,
    ) {
        $this->methods = $this->filterMethods($methods);
        $this->handler = $handler;
    }

    public function __toString(): string
    {
        return $this->name ?? $this->pattern;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getHandler(): callable|string
    {
        return $this->handler;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getGroup(): ?RouteGroup
    {
        return $this->group;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }
}
