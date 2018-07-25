<?php
/*
 * Log
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   07/25/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Lib;


/**
 * Log
 */
class Log {
    /*
     * Instance
     *
     * @static Log
     */
    protected static $_instance = null;


    /**
     * Get instance.
     *
     * @return Log
     */
    public static function getInstance() {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /*
     * Construct.
     */
    protected function __construct() { }


    /**
     * Log.
     *
     * @param string $message message
     */
    public function log($message) {
        if (empty($message)) {
            return;
        }

        echo "{$message}\n";
    }
}
