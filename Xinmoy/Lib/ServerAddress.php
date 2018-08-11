<?php
/*
 * Server Address
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   05/03/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Lib;


use Exception;


/**
 * Server Address
 */
class ServerAddress {
    /*
     * Instance
     *
     * @static ServerAddress
     */
    protected static $_instance = null;


    /*
     * Addresses
     *
     * @property array
     */
    protected $_addresses = [];


    /**
     * Get instance.
     *
     * @return ServerAddress
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
     * Register.
     *
     * @param string $server server
     * @param int    $fd     fd
     * @param string $host   host
     * @param int    $port   port
     */
    public function register($server, $fd, $host, $port) {
        if (empty($server) || ($fd < 0) || empty($host) || ($port < 0)) {
            throw new Exception('wrong server/fd/host/port');
        }

        $this->_addresses[$server]["{$host}:{$port}"][$fd] = [
            'server' => $server,
            'fd' => $fd,
            'host' => $host,
            'port' => $port
        ];
    }


    /**
     * Unregister.
     *
     * @param string $server server
     * @param int    $fd     fd
     * @param string $host   host
     * @param int    $port   port
     */
    public function unregister($server, $fd, $host, $port) {
        if (empty($server) || ($fd < 0) || empty($host) || ($port < 0)) {
            throw new Exception('wrong server/fd/host/port');
        }

        unset($this->_addresses[$server]["{$host}:{$port}"][$fd]);
        if (empty($this->_addresses[$server]["{$host}:{$port}"])) {
            unset($this->_addresses[$server]["{$host}:{$port}"]);
        }
    }


    /**
     * Set.
     *
     * @param string $server    server
     * @param array  $addresses addresses
     */
    public function set($server, $addresses) {
        if (empty($server)) {
            throw new Exception('wrong server');
        }

        $this->_addresses[$server] = $addresses;
    }


    /**
     * Discover.
     *
     * @param string $server server
     *
     * @return array
     */
    public function discover($server) {
        if (empty($server)) {
            throw new Exception('wrong server');
        }

        return isset($this->_addresses[$server]) ? $this->_addresses[$server] : null;
    }


    /**
     * Has?
     *
     * @param string $server server
     * @param string $host   host
     * @param int    $port   port
     *
     * @return bool
     */
    public function has($server, $host, $port) {
        if (empty($server) || empty($host) || ($port < 0)) {
            throw new Exception('wrong server/host/port');
        }

        return !empty($this->_addresses[$server]["{$host}:{$port}"]);
    }


    /**
     * Filter.
     *
     * @param string $server server
     *
     * @return array
     */
    public function filter($server) {
        if (empty($server)) {
            throw new Exception('wrong server');
        }

        $filtered = [];
        foreach ($this->_addresses[$server] as $addresses) {
            foreach ($addresses as $address) {
                $filtered["{$address['host']}:{$address['port']}"] = [
                    'server' => $address['server'],
                    'host' => $address['host'],
                    'port' => $address['port']
                ];
                break;
            }
        }
        return $filtered;
    }
}
