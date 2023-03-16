# Clarity Logger - Useful Exception Logs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/code-distortion/clarity-logger.svg?style=flat-square)](https://packagist.org/packages/code-distortion/clarity-logger)
![PHP Version](https://img.shields.io/badge/PHP-8.0%20to%208.3-blue?style=flat-square)
![Laravel](https://img.shields.io/badge/laravel-8%20to%2010-blue?style=flat-square)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/code-distortion/clarity-logger/run-tests.yml?branch=master&style=flat-square)](https://github.com/code-distortion/clarity-logger/actions)
[![Buy The World a Tree](https://img.shields.io/badge/treeware-%F0%9F%8C%B3-lightgreen?style=flat-square)](https://plant.treeware.earth/code-distortion/clarity-logger)
[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-v2.1%20adopted-ff69b4.svg?style=flat-square)](.github/CODE_OF_CONDUCT.md)

***code-distortion/clarity-logger*** is a Laravel package that generates useful exception logs.

```
EXCEPTION (CAUGHT):

exception     Illuminate\Http\Client\ConnectionException: "cURL error 6: Could not resolve host: api.example-gateway.com (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://api.example-gateway.com"
- location    app/Http/Controllers/CheckoutController.php on line 50 (method "submit")
- vendor      vendor/laravel/framework/src/Illuminate/Http/Client/PendingRequest.php on line 856 (closure)
request       POST https://my-website.com/checkout
- referrer    https://my-website.com/checkout
- route       cart.checkout
- middleware  web
- action      CheckoutController@submit
user          3342 - Bob - bob@example.com (123.123.123.123)
- agent       Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36
date/time     Sunday 2nd April at 7:08pm (Australia/Sydney)  2023-04-02 19:08:23 AEST +10:00
```



<br />



## Clarity Suite

Clarity Logger is a part of the ***Clarity Suite***, designed to let you manage exceptions more easily:
- [Clarity Context](https://github.com/code-distortion/clarity-context) - Understand Your Exceptions
- **Clarity Logger** - Useful Exception Logs
- [Clarity Control](https://github.com/code-distortion/clarity-control) - Handle Your Exceptions



<br />



## Table of Contents

- [Installation](#installation)
  - [Config File](#config-file)
- [Update Your Exception Handler](#update-your-exception-handler)
- [Manual Logging](#manual-logging)
- [Adding Some Context](#adding-some-context)



## Installation

Install the package via composer:

``` bash
composer require code-distortion/clarity-logger
```



### Config File

Use the following command if you would like to publish the `config/code_distortion.clarity_logger.php` config file:

``` bash
php artisan vendor:publish --provider="CodeDistortion\ClarityLogger\ServiceProvider" --tag="config"
```



## Update Your Exception Handler

Laravel projects use an [exception handler](https://laravel.com/docs/10.x/errors#the-exception-handler) class to log exceptions. You'll need to update this so Clarity Logger can log exceptions.

Add the following to the `register()` method of `app/Exceptions/Handler.php`. 

If you use them, Laravel's own context details can be included by adding `$this->exceptionContext($e)`.

By default, Laravel adds the PHP's stacktrace to the log afterwards. You can turn this off if you like by adding `->stop()`.

``` php
// app/Exceptions/Handler.php

namespace App\Exceptions;

use CodeDistortion\ClarityLogger\Logger; // <<<
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    …

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {

            Logger::log($e, $this->exceptionContext($e)); // <<<

        })->stop(); // <<<
    }
}
```

Laravel will now log exceptions using Clarity Logger.

> See [Laravel's documentation](https://laravel.com/docs/10.x/errors#the-exception-handler) for more information about exception handling.



## Manual Logging

If you catch an exception, or would like to just log a message, you can trigger the logging yourself:

``` php
Logger::log($exception);
Logger::log('message');
```

You can specify the reporting level:

``` php
Logger::debug($exception);     // or ::debug('message')
Logger::info($exception);      // or ::info('message')
Logger::notice($exception);    // or ::notice('message')
Logger::warning($exception);   // or ::warning('message')
Logger::error($exception);     // or ::error('message')
Logger::critical($exception);  // or ::critical('message')
Logger::alert($exception);     // or ::alert('message')
Logger::emergency($exception); // or ::emergency('message')
// or
Logger::level(Settings::REPORTING_LEVEL_INFO)->log($exception); // or ->log('message');
```

If you'd like to log to a particular channel, specify it before triggering the log action:

``` php
Logger::channel('slack')->log($exception);
```

These methods can be chained:

``` php
Logger::channel('slack')->debug()->log($exception);
Logger::emergency()->channel('slack')->log('message');
```



## Adding Some Context

If you add [Clarity Context](https://github.com/code-distortion/clarity-context) to your project (which lets you add context details to your code), Clarity Logger will include your context details automatically.

This can be a powerful tool when debugging exceptions. e.g.

```
EXCEPTION (UNCAUGHT):

exception     Illuminate\Http\Client\ConnectionException: "cURL error 6: Could not resolve host: api.example-gateway.com (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://api.example-gateway.com"
- location    app/Http/Controllers/CheckoutController.php on line 50 (method "submit")
- vendor      vendor/laravel/framework/src/Illuminate/Http/Client/PendingRequest.php on line 856 (closure)
request       POST https://my-website.com/checkout
- referrer    https://my-website.com/checkout
- route       cart.checkout
- middleware  web
- action      CheckoutController@submit
- trace-id    1234567890
user          3342 - Bob - bob@example.com (123.123.123.123)
- agent       Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36
date/time     Sunday 2nd April at 7:08pm (Australia/Sydney)  2023-04-02 19:08:23 AEST +10:00

CONTEXT:

app/Domain/Checkout/PerformCheckoutAction.php on line 20 (method "handle")
- "Performing checkout"
- user-id = 5
- order-id = 123

app/Domain/Payments/MakePaymentAction.php on line 19 (method "handle") (last application frame)
- "Sending payment request to gateway"
- payment-gateway = 'examplexyz.com'
- card-id = 456
- amount = '10.99'

vendor/laravel/framework/src/Illuminate/Http/Client/PendingRequest.php on line 856 (closure)
- The exception was thrown
```



<br />



## Testing This Package

- Clone this package: `git clone https://github.com/code-distortion/clarity-logger.git .`
- Run `composer install` to install dependencies
- Run the tests: `composer test`



## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.



### SemVer

This library uses [SemVer 2.0.0](https://semver.org/) versioning. This means that changes to `X` indicate a breaking change: `0.0.X`, `0.X.y`, `X.y.z`. When this library changes to version 1.0.0, 2.0.0 and so forth, it doesn't indicate that it's necessarily a notable release, it simply indicates that the changes were breaking.



## Treeware

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/code-distortion/clarity-logger) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.



## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.



### Code of Conduct

Please see [CODE_OF_CONDUCT](.github/CODE_OF_CONDUCT.md) for details.



### Security

If you discover any security related issues, please email tim@code-distortion.net instead of using the issue tracker.



## Credits

- [Tim Chandler](https://github.com/code-distortion)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
