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


        $this->_client->connect($this->_host, $this->_port);
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
    public function send($type, $data = []) {
        if (empty($this->_client)) {
            throw new Exception('init failed');
        }

        if (empty($type)) {
            throw new Exception('wrong type');
        }

        $message = json_encode([
            'type' => $type,
            'data' => $data
        ]);
        $this->_client->send($message);
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

        $data = $this->_client->recv();
        Log::getInstance()->log("receive: {$data}");
        return json_decode($data, true);
    }
}
