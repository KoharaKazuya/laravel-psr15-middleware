# Laravel PSR-15 Middleware

Adaptor to use PSR-15 middlewares in Laravel.

## Installation

```console
$ composer require koharakazuya/laravel-psr15-middleware
```

## Usage

```php
<?php

use KoharaKazuya\LaravelPSR15Middleware\LaravelPSR15MiddlewareFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

$middlewareFactory = new LaravelPSR15MiddlewareFactory(
    new DiactorosFactory(),
    new HttpFoundationFactory()
);
$laravelMiddleware = $middlewareFactory->createMiddleware($psrMiddleware);
```
