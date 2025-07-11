<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

/**
 * @internal
 */
trait MethodFiltering
{
    protected function filterMethods(array $methods): array
    {
        static $valid = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

        $ok           = [];

        foreach ($methods as $method)
        {
            if (in_array($method = strtoupper($method), $valid))
            {
                $ok[] = $method;
            }
        }

        return array_values(array_unique($ok));
    }
}
