<?php
/*
 * Base Constant
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/23/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Base;


use Exception;
use ReflectionClass;


/**
 * Base Constant
 */
class BaseConstant {
    /**
     * Call static.
     *
     * @param string $method    method
     * @param array  $arguments arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments) {
        $class = get_called_class();
        $class = new ReflectionClass($class);
        if (!$class->hasConstant($method)) {
            throw new Exception('undefined constant');
        }

        return $class->getConstant($method);
    }
}
