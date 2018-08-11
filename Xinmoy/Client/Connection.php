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
    protected $_connections = [];


    /*
     * Currents
     *
     * @property array
     */
    protected $_currents = [];


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
     * @param string $server server
     * @param string $host   host
     * @param string $port   port
     */
    public function register($server, $host, $port) {
        if (empty($server) || empty($host) || ($port < 0)) {
            throw new Exception('wrong server/host/port');
        }

        $this->_connections[$server]["{$host}:{$port}"] = [
            'server' => $server,
            'host' => $host,
            'port' => $port
        ];
    }


    /**
     * Unregister.
     *
     * @param string $server server
     * @param string $host   host
     * @param int    $port   port
     */
    public function unregister($server, $host, $port) {
        if (empty($server) || empty($host) || ($port < 0)) {
            throw new Exception('wrong server/host/port');
        }

        unset($this->_connections[$server]["{$host}:{$port}"]);
    }


    /**
     * Discover.
     *
     * @param string $server    server
     * @param array  $addresses addresses
     */
    public function discover($server, $addresses) {
        if (empty($server)) {
            throw new Exception('wrong server');
        }

        $this->_connections[$server] = $addresses;
    }


    /**
     * Select.
     *
     * @param string $server server
     *
     * @return CallClient
     */
    public function select($server) {
        if (empty($server)) {
            throw new Exception('wrong server');
        }

        if (empty($this->_connections[$server])) {
            throw new Exception('nonexisted server');
        }

        if (!isset($this->_currents[$server]) || ($this->_currents[$server] < 0)) {
            $this->_currents[$server] = 0;
        }

        $this->_currents[$server] += 1;
        $this->_currents[$server] %= count($this->_connections[$server]);
        $i = 0;
        foreach ($this->_connections[$server] as $connection) {
            if ($i == $this->_currents[$server]) {
                return $connection;
            }

            $i++;
        }
    }
}
