<?php
/*
 * Server
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   06/26/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Server;


use Exception;

use Swoole\Process;
use Swoole\Event;

use Xinmoy\Swoole\Server as SwooleServer;
use Xinmoy\Client\RegistrationClient;
use Xinmoy\Client\DiscoveryClient;
use Xinmoy\Client\Discovery;


/**
 * Server
 */
class Server extends SwooleServer {
    use Discovery;


    /*
     * Name
     *
     * @property string
     */
    protected $_name = '';


    /*
     * Register Host
     *
     * @property string
     */
    protected $_registerHost = '';


    /*
     * Register Port
     *
     * @property int
     */
    protected $_registerPort = -1;


    /*
     * Dependencies
     *
     * @property array
     */
    protected $_dependencies = [];


    /**
     * Set name.
     *
     * @param string $name name
     */
    public function setName($name) {
        if (empty($name)) {
            throw new Exception('wrong name');
        }

        $this->_name = $name;
    }


    /**
     * Get name.
     *
     * @return string
     */
    public function getName() {
        return $this->_name;
    }


    /**
     * Set register address.
     *
     * @param string $host host
     * @param int    $port port
     */
    public function setRegisterAddress($host, $port) {
        if (empty($host) || ($port < 0)) {
            throw new Exception('wrong register host/port');
        }

        $this->_registerHost = $host;
        $this->_registerPort = $port;
    }


    /**
     * Get register address.
     *
     * @return array
     */
    public function getRegisterAddress() {
        return [
            'host' => $this->_registerHost,
            'port' => $this->_registerPort
        ];
    }


    /**
     * Set dependencies.
     *
     * @param array $dependencies dependencies
     */
    public function setDependencies($dependencies) {
        $this->_dependencies = $dependencies;
    }


    /**
     * Get dependencies.
     *
     * @return array
     */
    public function getDependencies() {
        return $this->_dependencies;
    }


    /**
     * Start.
     */
    public function start() {
        $this->_addRegistrationProcess();
        $this->_addDiscoveryProcess();

        parent::start();
    }


    /*
     * Add registration process.
     */
    protected function _addRegistrationProcess() {
        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        $process = new Process([ $this, 'onRegistrationProcessAdd' ]);
        $this->_server->addProcess($process);
    }


    /*
     * Add discovery process.
     */
    protected function _addDiscoveryProcess() {
        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        $process = new Process([ $this, 'onDiscoveryProcessAdd' ]);
        $this->setProcess($process);
        $this->_server->addProcess($process);
    }


    /**
     * onWorkerStart
     *
     * @param Server $server    server
     * @param int    $worker_id worker id
     */
    public function onWorkerStart($server, $worker_id) {
        try {
            parent::onWorkerStart($server, $worker_id);

            if ($worker_id != 0) {
                return;
            }

            if (empty($this->_process)) {
                throw new Exception('process init failed');
            }

            Event::add($this->_process->pipe, [ $this, 'onRead' ]);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onCall
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     * @param object $data       data
     */
    public function onCall($server, $fd, $reactor_id, $data) {
        try {
            if (empty($data['namespace']) || empty($data['class']) || empty($data['method'])) {
                throw new Exception('wrong namespace/class/method');
            }

            $class = "{$data['namespace']}\\{$data['class']}";
            if (!class_exists($class)) {
                throw new Exception('undefined class');
            }

            $object = new $class();
            if (!method_exists($object, $data['method'])) {
                throw new Exception('undefined method');
            }

            if (!isset($data['arguments'])) {
                $data['arguments'] = [];
            }

            $return = call_user_func_array([ $object, $data['method'] ], $data['arguments']);
            $this->sendReturn($fd, $return);
        } catch (Exception $e) {
            $this->sendException($fd, $e);
        }
    }


    /**
     * onRegistrationProcessAdd
     *
     * @param Process $process process
     */
    public function onRegistrationProcessAdd($process) {
        try {
            if (empty($this->_registerHost) || ($this->_registerPort < 0)) {
                throw new Exception('wrong register host/port');
            }

            if (empty($this->_name)) {
                throw new Exception('wrong name');
            }

            if ($this->_port < 0) {
                throw new Exception('wrong port');
            }

            $client = new RegistrationClient($this->_registerHost, $this->_registerPort);
            $client->setServerName($this->_name);
            $client->setServerPort($this->_port);
            $client->connect();
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onDiscoveryProcessAdd
     *
     * @param Process $process process
     */
    public function onDiscoveryProcessAdd($process) {
        try {
            if (empty($this->_registerHost) || ($this->_registerPort < 0)) {
                throw new Exception('wrong register host/port');
            }

            $client = new DiscoveryClient($this->_registerHost, $this->_registerPort);
            $client->setProcess($process);
            $client->setDependencies($this->_dependencies);
            $client->connect();
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * Send result.
     *
     * @param int    $fd      fd
     * @param int    $code    optional, code
     * @param string $message optional, message
     * @param mixed  $return  optional, return
     */
    public function sendResult($fd, $code = 0, $message = 'ok', $return = null) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        $this->send($fd, 'call', [
            'code' => $code,
            'message' => $message,
            'data' => $return
        ]);
    }


    /**
     * Send return.
     *
     * @param int   $fd     fd
     * @param mixed $return optional, return
     */
    public function sendReturn($fd, $return = null) {
        $this->sendResult($fd, 0, 'ok', $return);
    }


    /**
     * Send exception.
     *
     * @param int       $fd fd
     * @param Exception $e  exception
     */
    public function sendException($fd, $e) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        if (empty($e)) {
            $e = new Exception('system error', 1);
        }

        $code = $e->getCode();
        $message = $e->getMessage();

        if (empty($code)) {
            $code = 1;
        }

        $this->sendResult($fd, $code, $message, null);
    }
}
