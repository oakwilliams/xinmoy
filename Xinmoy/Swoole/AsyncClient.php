<?php
/*
 * Async Client
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   06/27/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Swoole;


use Exception;

use Swoole\Client;
use Swoole\Timer;

use Xinmoy\Lib\Log;


/**
 * Async Client
 */
class AsyncClient {
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
     * Timer ID
     *
     * @property int
     */
    protected $_timerId = -1;


    /*
     * Heartbeat Check Interval
     *
     * @property int
     */
    protected $_heartbeatCheckInterval = 0;


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
        $this->_client = new Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->_client->set([
            'open_length_check' => true,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4
        ]);
        $this->_client->on('connect', [ $this, 'onConnect' ]);
        $this->_client->on('receive', [ $this, 'onReceive' ]);
        $this->_client->on('error', [ $this, 'onError' ]);
        $this->_client->on('close', [ $this, 'onClose' ]);
        $this->setHeartbeatCheckInterval(10);
    }


    /**
     * Set heartbeat check interval.
     *
     * @param int $heartbeat_check_interval heartbeat check interval
     */
    public function setHeartbeatCheckInterval(int $heartbeat_check_interval) {
        if ($heartbeat_check_interval <= 0) {
            throw new Exception('wrong heartbeat check interval');
        }

        $this->_heartbeatCheckInterval = $heartbeat_check_interval;
    }


    /**
     * Get heartbeat check interval.
     *
     * @return int
     */
    public function getHeartbeatCheckInterval() {
        return $this->_heartbeatCheckInterval;
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


    /*
     * Reconnect.
     */
    protected function _reconnect() {
        Timer::after(1000, [ $this, 'onReconnect' ]);
    }


    /**
     * onHeartbeatSend
     */
    public function onHeartbeatSend() {
        try {
            $this->send('ping');
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onConnect
     *
     * @param Client $client client
     */
    public function onConnect($client) {
        try {
            if ($this->_heartbeatCheckInterval <= 0) {
                throw new Exception('wrong heartbeat check interval');
            }

            $this->_timerId = Timer::tick($this->_heartbeatCheckInterval * 1000, [ $this, 'onHeartbeatSend' ]);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onReconnect
     */
    public function onReconnect() {
        try {
            if (empty($this->_client)) {
                throw new Exception('init failed');
            }

            if (empty($this->_host) || ($this->_port < 0)) {
                throw new Exception('wrong host/port');
            }

            $this->_client->connect($this->_host, $this->_port);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onReceive
     *
     * @param Client $client  client
     * @param string $message message
     */
    public function onReceive($client, $message) {
        try {
            $message = substr($message, 4);
            Log::getInstance()->log("receive: {$message}");
            $message = json_decode($message, true);
            if (empty($message)) {
                throw new Exception('wrong message');
            }

            if (empty($message['type'])) {
                throw new Exception('wrong type');
            }

            $method = "on{$message['type']}";
            if (!method_exists($this, $method)) {
                return;
            }

            if (!isset($message['data'])) {
                $message['data'] = null;
            }

            $this->{$method}($client, $message['data']);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onError
     *
     * @param Client $client client
     */
    public function onError($client) {
        try {
            // Reconnect.
            $this->_reconnect();

            $error = socket_strerror($client->errCode);
            throw new Exception($error);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onClose
     *
     * @param Client $client client
     */
    public function onClose($client) {
        try {
            Timer::clear($this->_timerId);
            $this->_reconnect();
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * Send.
     *
     * @param string $type type
     * @param array  $data optional ,data
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
}
