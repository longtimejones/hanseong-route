# Hanseong Route

A simple minimalistic PRS-7 router.

## Installation
```
composer require longtimejones/hanseong-route
```

## Basic usage

Compatible with PSR-7 HTTP Message libraries incorporating a PSR-17 HTTP ResponseFactoryInterface.

 - guzzlehttp/psr7 and http-interop/http-factory-guzzle
 - laminas/laminas-diactoros (*recommended, fastest*)
 - nyholm/psr7 and nyholm/psr7-server
 - slim/psr7
 - sunrise/http-factory and sunrise/http-server-request

```PHP
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

use Hanseong\Route\Router;

require __DIR__ . '/path/to/vendor/autoload.php';

$request = ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$router  = new Router;

$router->map('GET /', function (Request $request, Response $response): Response {
    $response->getBody()->write('<h1>Hello, World!</h1>');
    return $response;
});

$response = $router->dispatch($request);

(new SapiEmitter)->emit($response);
```

## Routing
```PHP
$router->map('GET /', handler);
$router->map('POST /', handler);
$router->map('PUT /', handler);
$router->map('DELETE /', handler);
```

```PHP
$router->map('GET /foo/([^/]+)', function (Response $response, string $foo): Response {
    $response->getBody()->write("<h1>Hello, {$foo}!</h1>");
    return $response;
});

$router->map('GET /bar/([^/]+/([^/]+)', function (Response $response, string $foo, string $bar): Response {
    $response->getBody()->write("<h1>Hello, {$bar}!</h1>");
    return $response;
});

$router->map('GET /var/([^/]+/([^/]+)', function (Response $response, Request $request, array $var): Response {
    $response->getBody()->write("<h1>Hello, {$var[0]}!</h1>");
    return $response;
});
```

### Hanseong (한성, 漢城)

*한성 (romanized Hanseong, but pronounced Hansung) or Hanyang (한양, 漢陽),\
was the capital during the Joseon era, a Korean Kingdom.\
Today it's better known as Seoul (서울, 서울시, 首尔, 首爾),\
the capital and largest metropolis of South Korea.*