<?php
/*
 * Base Cache
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/28/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Base;


use Exception;

use Xinmoy\Client\RedisConnection;


/**
 * Base Cache
 */
class BaseCache {
    /**
     * Master
     *
     * @const int
     */
    const MASTER = 0;


    /**
     * Master Slave
     *
     * @const int
     */
    const MASTER_SLAVE = 1;


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
    protected $_slave = null;


    /*
     * Mode
     *
     * @property int
     */
    protected $_mode = -1;


    /**
     * Construct.
     */
    public function __construct() {
        $this->_master = RedisConnection::getInstance()->master();
        $this->_slave = RedisConnection::getInstance()->selectSlave();
        $this->setMode(self::MASTER_SLAVE);
    }


    /**
     * Set mode.
     *
     * @param int $mode mode
     */
    public function setMode($mode) {
        if (!in_array($mode, [ self::MASTER, self::MASTER_SLAVE ])) {
            throw new Exception('wrong mode');
        }

        $this->_mode = $mode;
    }


    /**
     * Get mode.
     *
     * @return int
     */
    public function getMode() {
        return $this->_mode;
    }


    /**
     * Query.
     *
     * @param string $command command
     *
     * @return mixed
     */
    public function query($command, ...$arguments) {
        if (empty($command)) {
            throw new Exception('wrong command');
        }

        $client = null;
        switch ($this->_mode) {
            case self::MASTER:
                $client = $this->_master;
                break;

            case self::MASTER_SLAVE:
                $client = $this->_slave;
                break;

            default:
                throw new Exception('wrong mode');
        }

        if (empty($client['connection'])) {
            throw new Exception('client init failed');
        }

        return $client['connection']->raw($command, ...$arguments);
    }


    /**
     * Execute.
     *
     * @param string $command command
     *
     * @return mixed
     */
    public function execute($command, ...$arguments) {
        if (empty($command)) {
            throw new Exception('wrong command');
        }

        if (empty($this->_master['connection'])) {
            throw new Exception('master init failed');
        }

        return $this->_master['connection']->raw($command, ...$arguments);
    }
}
