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
    protected $_heartbeatCheckInterval = 10;


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

        $this->_client->on('connect', [ $this, 'onConnect' ]);
        $this->_client->on('receive', [ $this, 'onReceive' ]);
        $this->_client->on('error', [ $this, 'onError' ]);
        $this->_client->on('close', [ $this, 'onClose' ]);
        $this->_client->connect($this->_host, $this->_port);
    }


    /*
     * Reconnect.
     */
    protected function _reconnect() {
        if (empty($this->_client)) {
            throw new Exception('init failed');
        }

        if (empty($this->_host) || ($this->_port < 0)) {
            throw new Exception('wrong host/port');
        }

        $client = $this->_client;
        $host = $this->_host;
        $port = $this->_port;
        Timer::after(1000, function() use ($client, $host, $port) {
            $client->connect($host, $port);
        });
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

            $self = $this;
            $this->_timerId = Timer::tick($this->_heartbeatCheckInterval * 1000, function() use ($self) {
                $self->send('ping');
            });
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onReceive
     *
     * @param Client $client client
     * @param string $data   data
     */
    public function onReceive($client, $data) {
        try {
            Log::getInstance()->log("receive: $data");
            $data = json_decode($data, true);
            if (empty($data)) {
                throw new Exception('wrong data');
            }

            if (empty($data['type'])) {
                throw new Exception('wrong type');
            }

            $method = "on{$data['type']}";
            if (!method_exists($this, $method)) {
                throw new Exception('wrong type');
            }

            if (!isset($data['data'])) {
                $data['data'] = [];
            }

            $this->{$method}($client, $data['data']);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onPing
     *
     * @param Client $client client
     * @param array  $data   data
     */
    public function onPing($client, $data) { }


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
            if ($this->_timerId >= 0) {
                Timer::clear($this->_timerId);
            }

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
    public function send($type, $data = []) {
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
        $this->_client->send($message);
        Log::getInstance()->log("send: $message");
    }
}
