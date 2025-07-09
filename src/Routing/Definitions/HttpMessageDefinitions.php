<?php

namespace NGSOFT\Routing\Definitions;

use GuzzleHttp\Psr7\HttpFactory;
use NGSOFT\Routing\Adapter\HttpMessageBridge;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

/**
 * For Symfony PSR Bridge.
 * Using Guzzle PSR-7 Cause of the HTTP Client.
 */
class HttpMessageDefinitions
{
    public function __invoke()
    {
        return [
            ServerRequestFactoryInterface::class  => function (ContainerInterface $container)
            {
                return $container->get(HttpFactory::class);
            },
            UriFactoryInterface::class            => function (ContainerInterface $container)
            {
                return $container->get(HttpFactory::class);
            },
            StreamFactoryInterface::class         => function (ContainerInterface $container)
            {
                return $container->get(HttpFactory::class);
            },
            UploadedFileFactoryInterface::class   => function (ContainerInterface $container)
            {
                return $container->get(HttpFactory::class);
            },
            ResponseFactoryInterface::class       => function (ContainerInterface $container)
            {
                return $container->get(HttpFactory::class);
            },
            HttpMessageFactoryInterface::class    => function (ContainerInterface $container)
            {
                $factory = $container->get(HttpFactory::class);
                return new PsrHttpFactory($factory, $factory, $factory, $factory);
            },
            HttpFoundationFactory::class          => function ()
            {
                return new HttpFoundationFactory();
            },
            HttpFoundationFactoryInterface::class => function (ContainerInterface $container)
            {
                return $container->get(HttpFoundationFactory::class);
            },
            HttpMessageBridge::class              => function (ContainerInterface $container)
            {
                return new HttpMessageBridge(
                    $container->get(HttpFoundationFactoryInterface::class),
                    $container->get(HttpMessageFactoryInterface::class)
                );
            },
        ];
    }
}
