<?php
/*
 * Sync Client
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/06/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */

namespace Xinmoy\Swoole;


use Swoole\Client;

use Xinmoy\Lib\Log;


/**
 * Sync Client
 */
class SyncClient {
    /*
     * Client
     *
     * @property Client
     */
    protected $_client = null;


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
     * Timeout
     *
     * @property float
     */
    protected $_timeout = 3;


    /**
     * Construct.
     *
     * @param string $host host
     * @param int    $port port
     */
    public function __construct($host, $port) {
        if (empty($host) || ($port < 0)) {
            throw new Exception('wrong host/port');
        }

        $this->_host = $host;
        $this->_port = $port;
        $this->_client = new Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $this->_client->set([
            'open_length_check' => true,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4
        ]);
    }


    /**
     * Set timeout.
     *
     * @param float $timeout timeout
     */
    public function setTimeout($timeout) {
        if ($timeout <= 0) {
            throw new Exception('wrong timeout');
        }

        $this->_timeout = $timeout;
    }


    /**
     * Get timeout.
     *
     * @return float
     */
    public function getTimeout() {
        return $this->_timeout;
    }


    /**
     * Connect.
     */
    public function connect() {
        if (empty($this->_client)) {
            throw new Exception('init failed');
        }

        if (empty($this->_host) || ($this->_port < 0)) {
            throw new Exception('wrong host/port');
        }

        if ($this->_timeout <= 0) {
            throw new Exception('wrong timeout');
        }

        $this->_client->connect($this->_host, $this->_port, $this->_timeout);
    }


    /**
     * Close.
     */
    public function close() {
        if (empty($this->_client)) {
            throw new Exception('init failed');
        }

        $this->_client->close();
    }


    /**
     * Send.
     *
     * @param string $type type
     * @param array  $data optional, data
     */
    public function send($type, $data = null) {
        if (empty($type)) {
            throw new Exception('wrong type');
        }

        if (empty($this->_client)) {
            throw new Exception('init failed');
        }

        $message = json_encode([
            'type' => $type,
            'data' => $data
        ]);
        $len = strlen($message);
        $len = sprintf("%'08x", $len);
        $len = hex2bin($len);
        $this->_client->send("{$len}{$message}");
        Log::getInstance()->log("send: {$message}");
    }


    /**
     * Receive.
     *
     * @return array
     */
    public function receive() {
        if (empty($this->_client)) {
            throw new Exception('init failed');
        }

        $message = $this->_client->recv();
        $message = substr($message, 4);
        Log::getInstance()->log("receive: {$message}");
        return json_decode($message, true);
    }
}
