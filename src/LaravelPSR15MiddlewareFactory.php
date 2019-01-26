<?php

namespace KoharaKazuya\LaravelPSR15Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

class LaravelPSR15MiddlewareFactory
{
    private $diactorosFactory;
    private $httpFoundationFactory;

    public function __construct(DiactorosFactory $diactorosFactory, HttpFoundationFactory $httpFoundationFactory)
    {
        $this->diactorosFactory = $diactorosFactory;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    /**
     * @param MiddlewareInterface $psrMiddleware
     * @return LaravelPSR15Middleware
     */
    public function createMiddleware(MiddlewareInterface $psrMiddleware): LaravelPSR15Middleware
    {
        return new LaravelPSR15Middleware($this->diactorosFactory, $this->httpFoundationFactory, $psrMiddleware);
    }
}