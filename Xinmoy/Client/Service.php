<?php
/*
 * Service
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   07/26/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


/**
 * Service
 */
class Service {
    /*
     * Client
     *
     * @property SyncClient
     */
    protected $_client = null;


    /*
     * Namespace
     *
     * @property string
     */
    protected $_namespace = '';


    /*
     * Class
     *
     * @property string
     */
    protected $_class = '';


    /**
     * Construct.
     */
    public function __construct() {
        // Namespace/Class
        $service = get_called_class();
        $service = explode('\\', $service);
        $this->_class = array_pop($service);
        $this->_namespace = join('\\', $service);

        // Client
        $connection = Connection::getInstance()->select($this->_namespace);
        if (empty($connection)) {
            throw new Exception('nonexisted connection');
        }

        if (empty($connection['host']) || !isset($connection['port']) || ($connection['port'] < 0)) {
            throw new Exception('wrong host/port');
        }

        $this->_client = new CallClient($connection['host'], $connection['port']);
        $this->_client->connect();
    }


    /**
     * Destruct.
     */
    public function __destruct() {
        if (empty($this->_client)) {
            throw new Exception('client init failed');
        }

        $this->_client->close();
    }


    /**
     * Call.
     *
     * @param string $method    method
     * @param array  $arguments arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments) {
        if (empty($this->_client)) {
            throw new Exception('client init failed');
        }

        if (empty($this->_namespace) || empty($this->_class)) {
            throw new Exception('wrong namespace/class');
        }

        return $this->_client->call($this->_namespace, $this->_class, $method, $arguments);
    }
}
