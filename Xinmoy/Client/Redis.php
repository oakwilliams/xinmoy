<?php
/*
 * Redis
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/01/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


/**
 * Redis
 */
trait Redis {
    /*
     * Redis Master
     *
     * @property array
     */
    protected $_redisMaster = null;


    /*
     * Redis Slaves
     *
     * @property array
     */
    protected $_redisSlaves = [];


    /**
     * Set Redis master.
     *
     * @param array $master master
     */
    public function setRedisMaster($master) {
        $this->_redisMaster = $master;
    }


    /**
     * Get Redis master.
     *
     * @return array
     */
    public function getRedisMaster() {
        return $this->_redisMaster;
    }


    /**
     * Set Redis slaves.
     *
     * @param array $slaves slaves
     */
    public function setRedisSlaves($slaves) {
        $this->_redisSlaves = $slaves;
    }


    /**
     * Get Redis slaves.
     *
     * @return array
     */
    public function getRedisSlaves() {
        return $this->_redisSlaves;
    }


    /**
     * Add Redis connections.
     */
    public function _addRedisConnections() {
        RedisConnection::getInstance()->setMaster($this->_redisMaster);
        RedisConnection::getInstance()->setSlaves($this->_redisSlaves);
    }
}
