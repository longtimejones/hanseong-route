<?php
/**
 * Router
 *
 * @package    Hanseong
 * @subpackage Route
 * @category   Router
 *
 * @author     Tim Jong Olesen <tim@olesen.be>
 * @copyright  Copyright (c) 2020, Tim Jong Olesen
 * @link       https://github.com/longtimejones/hanseong-route/
 */

declare(strict_types=1);

namespace Hanseong\Route;

use Hanseong\Route\Factory\ResponseFactory;
use Hanseong\Route\Helper\Sanitizer;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

use Closure;
use ReflectionClass, ReflectionFunction;
use InvalidArgumentException, LengthException, OutOfRangeException;

use function array_shift;
use function array_slice;
use function call_user_func_array;
use function is_callable;
use function is_object;
use function preg_match;
use function str_replace;
use function strpos;

class Router
{
    /**
     * Mapped routes
     *
     * @var array<string, callable>
     */
    protected $routes = [];

    /**
     * Maps a route configuration
     *
     * @param string $pattern
     * @param \Closure $callable
     *
     * @return void
     *
     * @access public
     */
    public function map(string $pattern, Closure $callable): void
    {
        $this->routes[$pattern] = $callable;
    }

    /**
     * Serves a route matching requested URI
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return mixed
     *
     * @throws \LengthException if no routes found
     * @throws \InvalidArgumentException if callable is not closure or object
     * @throws \OutOfRangeException if no routes dispatched
     *
     * @access public
     */
    public function dispatch(ServerRequestInterface $request)
    {
        /**
         * Invalid route detected
         */
        if (empty($this->routes) !== false)
            throw new LengthException('No routes found');

        $subject = $request->getMethod() . ' ' . $request->getUri()->getPath();

        foreach ($this->routes as $pattern => $callable) {

            /**
             * Perform match for the route
             */
            if (preg_match(
                '/^' . str_replace('/', '\/', $pattern) . '\/?$/i', $subject, $args
            ) !== false && $args[0] !== null) {

                /**
                 * Accept Closures only
                 */
                if (!$callable instanceof Closure)
                    throw new InvalidArgumentException('Closures are supported only');

                /**
                 * Sanitize arguments from URI path
                 */
                $args   = Sanitizer::sanitizeArguments(array_slice($args, 1));

                /**
                 * Construct parameters
                 */
                $params = $this->constructParams($request, $callable, $args);

                /**
                 * And fingers crossed
                 */
                return call_user_func_array($callable, $params);
            }
        }

        throw new OutOfRangeException('No routes dispatched');
    }

    /**
     * Constructs dispatching parameters
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Closure                                 $callable
     * @param array<int, string>                       $args
     *
     * @return array<int, string>
     *
     * @throws InvalidArgumentException if unresolvable instance found
     *
     * @access protected
     */
    protected function constructParams(ServerRequestInterface $request, Closure $callable, array $args): array
    {
        $func   = new ReflectionFunction($callable);
        $params = $func->getParameters();

        if (empty($params))
            return [];

        foreach ($params as $index => $param) {

            /**
             * Append argument from URI path to parameters
             */
            $obj = $param->getClass();
            if (!$obj instanceof ReflectionClass) {
                if ($param->isArray())
                    $params[$index] = [array_shift($args)];
                else
                    $params[$index] = array_shift($args);
                continue;
            }

            /**
             * Append resolvable object to parameters
             */
            $abs_class_name = $obj->getShortName();
            if (strpos($abs_class_name, 'ServerRequestInterface') !== false)
                $params[$index] = $request;
            elseif (strpos($abs_class_name, 'ResponseInterface') !== false)
                $params[$index] = ResponseFactory::getResponseFactory($request);
            else
                throw new InvalidArgumentException("Unresolvable instance of {$abs_class_name}");
        }

        return $params;
    }
}