<?php
/*
 * Redis Client
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/28/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\PECL;


use Exception;
use Redis;

use Xinmoy\Lib\Log;


/**
 * Redis Client
 */
class RedisClient {
    /*
     * Redis
     *
     * @property Redis
     */
    protected $_redis = null;


    /*
     * Host
     *
     * @property string
     */
    protected $_host = '';


    /*
     * Port
     *
     * @property int
     */
    protected $_port = -1;


    /*
     * Password
     *
     * @property string
     */
    protected $_password = '';


    /**
     * Construct.
     *
     * @param string $host     host
     * @param int    $port     optional, port
     * @param string $password optional, password
     */
    public function __construct($host, $port = 6379, string $password = '') {
        if (empty($host) || ($port < 0)) {
            throw new Exception('wrong host/port');
        }

        $this->_host = $host;
        $this->_port = $port;
        $this->_password = $password;
        $this->_redis = new Redis();
        $this->_redis->pconnect($host, $port);
        if (!$this->_redis->auth($password)) {
            throw new Exception('auth failed');
        }
    }


    /**
     * Raw.
     *
     * @param string $command command
     *
     * @return mixed
     */
    public function raw($command, ...$arguments) {
        Log::getInstance()->log('raw: ' . json_encode([
            'command' => $command,
            'arguments' => $arguments
        ]));
        if (empty($command)) {
            throw new Exception('wrong command');
        }

        if (empty($this->_redis)) {
            throw new Exception('init failed');
        }

        $return = $this->_redis->rawCommand($command, ...$arguments);
        Log::getInstance()->log('raw: ' . json_encode([
            'return' => $return
        ]));
        return $return;
    }
}
