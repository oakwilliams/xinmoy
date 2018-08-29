<?php
/*
 * Redis Connection
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/21/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Exception;


/**
 * Redis Connection
 */
class RedisConnection {
    /*
     * Instance
     *
     * @static RedisConnection
     */
    protected static $_instance = null;


    /*
     * Master
     *
     * @property array
     */
    protected $_master = null;


    /*
     * Slave
     *
     * @property array
     */
    protected $_slaves = [];


    /*
     * Current
     *
     * @property int
     */
    public $_current = -1;


    /**
     * Get instance.
     *
     * @return RedisConnection
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
     * Set master.
     *
     * @param array $master master
     */
    public function setMaster($master) {
        if (empty($master)) {
            return;
        }

        $this->_master = $this->_create($master);
    }


    /**
     * Set slaves.
     *
     * @param array $slaves slaves
     */
    public function setSlaves($slaves) {
        if (empty($slaves)) {
            return;
        }

        if (!isset($this->_master['password'])) {
            $this->_master['password'] = '';
        }

        foreach ($slaves as $slave) {
            $slave['password'] = $this->_master['password'];
            $this->_slaves[] = $this->_create($slave);
        }
    }


    /*
     * Create.
     *
     * @param array $config config
     *
     * @return array
     */
    protected function _create($config) {
        if (empty($config['host']) || (!isset($config['port'])) || ($config['port'] < 0)) {
            throw new Exception('wrong host/port');
        }

        if (!isset($config['password'])) {
            $config['password'] = '';
        }

        return [
            'host' => $config['host'],
            'port' => $config['port'],
            'password' => $config['password'],
            'connection' => new RedisClient($config['host'], $config['port'], $config['password'])
        ];
    }


    /**
     * Master.
     *
     * @return array
     */
    public function master() {
        return $this->_master;
    }


    /**
     * Select slave.
     *
     * @return array
     */
    public function selectSlave() {
        if (empty($this->_slaves)) {
            return $this->master();
        }

        if ($this->_current < 0) {
            $this->_current = -1;
        }

        $this->_current++;
        $this->_current %= count($this->_slaves);
        return $this->_slaves[$this->_current];
    }
}
