<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * PSR/Foundation attribute manager.
 *
 * @internal
 */
trait AttributeManager
{
    protected function setAttributes(Request|ServerRequestInterface $request, iterable $values): Request|ServerRequestInterface
    {
        foreach ($values as $name => $value)
        {
            $request = $this->setAttribute($request, $name, $value);
        }
        return $request;
    }

    protected function setAttribute(Request|ServerRequestInterface $request, string $name, mixed $value): Request|ServerRequestInterface
    {
        if ($request instanceof ServerRequestInterface)
        {
            return $request->withAttribute($name, $value);
        }

        $request->attributes->set($name, $value);
        return $request;
    }

    protected function removeAttribute(Request|ServerRequestInterface $request, string $name): Request|ServerRequestInterface
    {
        if ($request instanceof ServerRequestInterface)
        {
            return $request->withoutAttribute($name);
        }

        $request->attributes->remove($name);
        return $request;
    }

    protected function getAttribute(Request|ServerRequestInterface $request, string $name, mixed $defaultValue = null): mixed
    {
        if ($request instanceof ServerRequestInterface)
        {
            return $request->getAttribute($name, $defaultValue);
        }

        return $request->attributes->get($name, $defaultValue);
    }
}
