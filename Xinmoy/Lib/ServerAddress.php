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
    protected $_addresses = null;


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
     * @param string $name name
     * @param int    $fd   fd
     * @param string $host host
     * @param int    $port port
     */
    public function register($name, $fd, $host, $port) {
        if (empty($name) || ($fd < 0) || empty($host) || ($port < 0)) {
            throw new Exception('wrong name/fd/host/port');
        }

        $this->_addresses[$name]["{$host}:{$port}"][$fd] = [
            'name' => $name,
            'fd' => $fd,
            'host' => $host,
            'port' => $port
        ];
    }


    /**
     * Unregister.
     *
     * @param string $name name
     * @param int    $fd   fd
     * @param string $host host
     * @param int    $port port
     */
    public function unregister($name, $fd, $host, $port) {
        if (empty($name) || ($fd < 0) || empty($host) || ($port < 0)) {
            throw new Exception('wrong name/fd/host/port');
        }

        unset($this->_addresses[$name]["{$host}:{$port}"][$fd]);
        if (empty($this->_addresses[$name]["{$host}:{$port}"])) {
            unset($this->_addresses[$name]["{$host}:{$port}"]);
            if (empty($this->_addresses[$name])) {
                unset($this->_addresses[$name]);
            }
        }
    }


    /**
     * Set.
     *
     * @param string $name      name
     * @param array  $addresses addresses
     */
    public function set($name, $addresses) {
        if (empty($name)) {
            throw new Exception('wrong name');
        }

        $this->_addresses[$name] = $addresses;
    }


    /**
     * Discover.
     *
     * @param string $name name
     *
     * @return array
     */
    public function discover($name) {
        if (empty($name)) {
            throw new Exception('wrong name');
        }

        return isset($this->_addresses[$name]) ? $this->_addresses[$name] : null;
    }


    /**
     * Has?
     *
     * @param string $name name
     * @param string $host host
     * @param int    $port port
     *
     * @return bool
     */
    public function has($name, $host, $port) {
        if (empty($name) || empty($host) || ($port < 0)) {
            throw new Exception('wrong name/host/port');
        }

        return !empty($this->_addresses[$name]["{$host}:{$port}"]);
    }


    /**
     * Filter.
     *
     * @param string $name name
     *
     * @return array
     */
    public function filter($name) {
        if (empty($name)) {
            throw new Exception('wrong name');
        }

        if (empty($this->_addresses[$name])) {
            return null;
        }

        $filtered = null;
        foreach ($this->_addresses[$name] as $addresses) {
            if (empty($addresses)) {
                continue;
            }

            foreach ($addresses as $address) {
                $filtered["{$address['host']}:{$address['port']}"] = [
                    'name' => $address['name'],
                    'host' => $address['host'],
                    'port' => $address['port']
                ];
                break;
            }
        }
        return $filtered;
    }
}
