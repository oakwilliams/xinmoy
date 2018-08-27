<?php
/*
 * Connection
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/04/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Exception;


/**
 * Connection
 */
class Connection {
    /*
     * Instance
     *
     * @static Connection
     */
    protected static $_instance = null;


    /*
     * Connections
     *
     * @property array
     */
    protected $_connections = null;


    /*
     * Currents
     *
     * @property array
     */
    protected $_currents = null;


    /**
     * Get instance.
     *
     * @return Connection
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
     * Register
     *
     * @param string $name name
     * @param string $host host
     * @param string $port port
     */
    public function register($name, $host, $port) {
        if (empty($name) || empty($host) || ($port < 0)) {
            throw new Exception('wrong name/host/port');
        }

        $this->_connections[$name]["{$host}:{$port}"] = [
            'name' => $name,
            'host' => $host,
            'port' => $port
        ];
    }


    /**
     * Unregister.
     *
     * @param string $name name
     * @param string $host host
     * @param int    $port port
     */
    public function unregister($name, $host, $port) {
        if (empty($name) || empty($host) || ($port < 0)) {
            throw new Exception('wrong name/host/port');
        }

        unset($this->_connections[$name]["{$host}:{$port}"]);
    }


    /**
     * Discover.
     *
     * @param string $name      name
     * @param array  $addresses addresses
     */
    public function discover($name, $addresses) {
        if (empty($name)) {
            throw new Exception('wrong name');
        }

        $this->_connections[$name] = $addresses;
    }


    /**
     * Select.
     *
     * @param string $name name
     *
     * @return CallClient
     */
    public function select($name) {
        if (empty($name)) {
            throw new Exception('wrong name');
        }

        if (empty($this->_connections[$name])) {
            throw new Exception('nonexisted name');
        }

        if (!isset($this->_currents[$name]) || ($this->_currents[$name] < 0)) {
            $this->_currents[$name] = -1;
        }

        $this->_currents[$name]++;
        $this->_currents[$name] %= count($this->_connections[$name]);
        $i = 0;
        foreach ($this->_connections[$name] as $connection) {
            if ($i == $this->_currents[$name]) {
                return $connection;
            }

            $i++;
        }
    }
}
