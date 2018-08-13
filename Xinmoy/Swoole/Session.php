<?php
/*
 * Session
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   05/03/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Swoole;


use Exception;


/**
 * Session
 */
class Session {
    /*
     * Instance
     *
     * @static Session
     */
    protected static $_instance = null;


    /*
     * Sessions
     *
     * @property array
     */
    protected $_sessions = null;


    /**
     * Get instance.
     *
     * @return Session
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
     * Create.
     *
     * @param int $fd fd
     */
    public function create($fd) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        $this->_sessions[$fd] = null;
    }


    /**
     * Destroy.
     *
     * @param int $fd fd
     */
    public function destroy($fd) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        unset($this->_sessions[$fd]);
    }


    /**
     * Set.
     *
     * @param int    $fd    fd
     * @param string $key   key
     * @param mixed  $value value
     */
    public function set($fd, $key, $value) {
        if (($fd < 0) || empty($key)) {
            throw new Exception('wrong fd/key');
        }

        $this->_sessions[$fd][$key] = $value;
    }


    /**
     * Get.
     *
     * @param int    $fd  fd
     * @param string $key key
     *
     * @return mixed
     */
    public function get($fd, $key) {
        if (($fd < 0) || empty($key)) {
            throw new Exception('wrong fd/key');
        }

        return isset($this->_sessions[$fd][$key]) ? $this->_sessions[$fd][$key] : null;
    }
}
