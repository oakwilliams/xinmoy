<?php
/*
 * Http Server
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/16/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Server;


use Exception;

use Swoole\Process;
use Swoole\Event;

use Xinmoy\Swoole\HttpServer as SwooleHttpServer;
use Xinmoy\Client\DiscoveryClient;
use Xinmoy\Client\Discovery;


/**
 * Http Server
 */
class HttpServer extends SwooleHttpServer {
    use Discovery;


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
        $this->_addDiscoveryProcess();

        parent::start();
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
}
