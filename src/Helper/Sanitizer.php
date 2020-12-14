<?php
/**
 * Sanitizer
 *
 * @package    Hanseong
 * @subpackage Route
 * @category   Sanitizer
 *
 * @author     Tim Jong Olesen <tim@olesen.be>
 * @copyright  Copyright (c) 2020, Tim Jong Olesen
 * @link       https://github.com/longtimejones/hanseong-route/
 */

declare(strict_types=1);

namespace Hanseong\Route\Helper;

use function array_map;
use function filter_var_array;

class Sanitizer
{
    /**
     * Sanitizes dispatching URI path
     *
     * @param array<int, string> $args
     *
     * @return array<int, string>
     *
     * @access public
     *
     * @static
     */
    public static function sanitizeArguments(array $args): array
    {
        $args = array_map('urldecode', $args);
        $args = array_map('trim', $args);
        $args = filter_var_array($args, FILTER_SANITIZE_URL);

        return (array) $args;
    }
}