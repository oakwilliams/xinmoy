<?php
/*
 * Server
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   05/03/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Swoole;


use Exception;

use Swoole\Server as SwooleServer;

use Xinmoy\Lib\Log;


/**
 * Server
 */
class Server {
    /*
     * Swoole Server
     *
     * @property SwooleServer
     */
    protected $_server = null;


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
     * Heartbeat Idle Time
     *
     * @property int
     */
    protected $_heartbeatIdleTime = 20;


    /**
     * Construct.
     *
     * @param string $host optional, host
     * @param int    $port optional, port
     */
    public function __construct($host = '127.0.0.1', $port = 8000) {
        if (empty($host) || ($port < 0)) {
            throw new Exception('wrong host/port');
        }

        $this->_host = $host;
        $this->_port = $port;
        $this->_server = new SwooleServer($host, $port, SWOOLE_BASE, SWOOLE_SOCK_TCP);
    }


    /**
     * Set heartbeat idle time.
     *
     * @param int $heartbeat_idle_time heartbeat idle time
     */
    public function setHeartbeatIdleTime($heartbeat_idle_time) {
        if ($heartbeat_idle_time <= 0) {
            throw new Exception('wrong heartbeat idle time');
        }

        $this->_heartbeatIdleTime = $heartbeat_idle_time;
    }


    /**
     * Get heartbeat idle time.
     *
     * @return int
     */
    public function getHeartbeatIdleTime() {
        return $this->_heartbeatIdleTime;
    }


    /**
     * Start.
     */
    public function start() {
        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        if ($this->_heartbeatIdleTime <= 0) {
            throw new Exception('wrong heartbeat idle time');
        }

        $this->_server->set([
            'heartbeat_idle_time' => $this->_heartbeatIdleTime
        ]);
        $this->_server->on('workerstart', [ $this, 'onWorkerStart' ]);
        $this->_server->on('connect', [ $this, 'onConnect' ]);
        $this->_server->on('receive', [ $this, 'onReceive' ]);
        $this->_server->on('close', [ $this, 'onClose' ]);
        $this->_server->start();
    }


    /**
     * onWorkerStart
     *
     * @param Server $server    server
     * @param int    $worker_id worker id
     */
    public function onWorkerStart($server, $worker_id) {
        try {
            if ($worker_id != 0) {
                return;
            }

            $server->tick(1000, [ $this, 'onHeartbeatCheck' ]);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onHeartbeatCheck
     */
    public function onHeartbeatCheck() {
        try {
            if (empty($this->_server)) {
                throw new Exception('init failed');
            }

            $fds = $this->_server->heartbeat(false);
            $this->closeMany($fds);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onConnect
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     */
    public function onConnect($server, $fd, $reactor_id) {
        try {
            Session::getInstance()->create($fd);
        } catch (Exception $e) {
            $this->sendError($fd, $e->getMessage());
        }
    }


    /**
     * onReceive
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     * @param string $data       data
     */
    public function onReceive($server, $fd, $reactor_id, string $data) {
        try {
            Log::getInstance()->log("receive: {$data}");
            $data = json_decode($data, true);
            if (empty($data)) {
                throw new Exception('wrong data');
            }

            if (empty($data['type'])) {
                throw new Exception('wrong type');
            }

            $method = "on{$data['type']}";
            if (!method_exists($this, $method)) {
                return;
            }

            if (!isset($data['data'])) {
                $data['data'] = [];
            }

            $this->{$method}($server, $fd, $reactor_id, $data['data']);
        } catch (Exception $e) {
            $this->sendError($fd, $e->getMessage());
        }
    }


    /**
     * onClose
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     */
    public function onClose($server, $fd, $reactor_id) {
        try {
            Session::getInstance()->destroy($fd);
        } catch (Exception $e) {
            $this->sendError($fd, $e->getMessage());
        }
    }


    /**
     * onPing
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     * @param object $data       data
     */
    public function onPing($server, $fd, $reactor_id, $data) {
        $this->send($fd, 'ping');
    }


    /**
     * Close.
     *
     * @param int $fd fd
     */
    public function close($fd) {
        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        $this->_server->close($fd);
    }


    /**
     * Close many.
     *
     * @param array $fds fds
     */
    public function closeMany($fds) {
        foreach ($this->_closeMany($fds) as $i) { }
    }


    /*
     * Close many.
     *
     * @param array $fds fds
     */
    protected function _closeMany($fds) {
        foreach ($fds as $fd) {
            yield $this->close($fd);
        }
    }


    /**
     * Send.
     *
     * @param int    $fd   fd
     * @param string $type type
     * @param array  $data optional, data
     */
    public function send($fd, $type, $data = []) {
        if (($fd < 0) || empty($type)) {
            throw new Exception('wrong fd/type');
        }

        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        $message = json_encode([
            'type' => $type,
            'data' => $data
        ]);
        $this->_server->send($fd, $message);
        Log::getInstance()->log("send: {$message}");
    }


    /**
     * Send error.
     *
     * @param int    $fd      fd
     * @param string $message message
     */
    public function sendError($fd, $message) {
        try {
            if (($fd < 0) || empty($message)) {
                throw new Exception('wrong fd/message');
            }

            $this->send($fd, 'error', [ 'message' => $message ]);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * Send to group.
     *
     * @param string $group group
     * @param string $type  type
     * @param array  $data  optional, data
     */
    public function sendToGroup($group, $type, $data = []) {
        foreach ($this->_sendToGroup($group, $type, $data) as $i) { }
    }


    /*
     * Send to group.
     *
     * @param string $group group
     * @param string $type  type
     * @param array  $data  optional, data
     */
    protected function _sendToGroup($group, $type, $data = []) {
        if (empty($group) || empty($type)) {
            throw new Exception('wrong group/type');
        }

        $members = Group::getInstance()->getMembers($group);
        foreach ($members as $member) {
            yield $this->send($member, $type, $data);
        }
    }
}
