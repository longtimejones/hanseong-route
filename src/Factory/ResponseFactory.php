<?php
/**
 * Response Factory
 *
 * @package    Hanseong
 * @subpackage Route
 * @category   Factory
 *
 * @author     Tim Jong Olesen <tim@olesen.be>
 * @copyright  Copyright (c) 2020, Tim Jong Olesen
 * @link       https://github.com/longtimejones/hanseong-route/
 */

declare(strict_types=1);

namespace Hanseong\Route\Factory;

use Http\Factory\Guzzle as Guzzle;
use Laminas\Diactoros as Laminas;
use Nyholm\Psr7\Factory as Nyholm;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Slim\Psr7 as Slim;
use Sunrise\Http\Factory as Sunrise;

use ReflectionClass;
use BadFunctionCallException, OutOfRangeException;

use function class_exists;

class ResponseFactory
{
    /**
     * @var null|\Psr\Http\Message\ResponseInterface
     */
    protected static $factory;

    /**
     * PSR-17 HTTP ResponseFactoryInterface
     *
     * @var array<string, string>
     */
    protected static $factories = [
        'GuzzleHttp\Psr7'            => Guzzle\ResponseFactory::class,
        'Laminas\Diactoros'          => Laminas\ResponseFactory::class,
        'Nyholm\Psr7'                => Nyholm\Psr17Factory::class,
        'Slim\Psr7'                  => Slim\Factory\ResponseFactory::class,
        'Sunrise\Http\ServerRequest' => Sunrise\ResponseFactory::class,
    ];

    /**
     * Gets instance of Psr\Http\Message\ResponseInterface
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \OutOfRangeException if unsupported PSR-17 HTTP ResponseFactory detected
     * @throws \BadFunctionCallException if uninstalled PSR-17 HTTP ResponseFactory detected
     *
     * @access public
     *
     * @static
     */
    public static function getResponseFactory(ServerRequestInterface $request): ResponseInterface
    {
        if (static::$factory instanceof ResponseInterface)
            return static::$factory;

        $factory_ns    = (new ReflectionClass($request))->getNamespaceName();
        if (!isset(static::$factories[$factory_ns]))
            throw new OutOfRangeException('Unsupported PSR-17 HTTP ResponseFactoryInterface detected');

        $psr17_factory = static::$factories[$factory_ns];
        if (!class_exists($psr17_factory, true))
            throw new BadFunctionCallException('Uninstalled PSR-17 HTTP ResponseFactoryInterface detected');

        return static::setResponseFactory($psr17_factory);
    }

    /**
     * Sets instance of Psr\Http\Message\ResponseInterface
     *
     * @param string $psr17_factory
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @access public
     *
     * @static
     */
    public static function setResponseFactory(string $psr17_factory): ResponseInterface
    {
        return static::$factory = (new $psr17_factory)->createResponse();
    }
}