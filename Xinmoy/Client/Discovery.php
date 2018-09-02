<?php
/*
 * Discovery
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/04/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Swoole\Process as SwooleProcess;
use Swoole\Event;

use Xinmoy\Swoole\Process;


/**
 * Discovery
 */
trait Discovery {
    use Process;


    /*
     * Dependencies
     *
     * @property array
     */
    protected $_dependencies = [];


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


    /*
     * Add discovery process.
     */
    protected function _addDiscoveryProcess() {
        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        $process = new SwooleProcess([ $this, 'onDiscoveryProcessAdd' ]);
        $this->setProcess($process);
        $this->_server->addProcess($process);
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
     * onRegister
     *
     * @param array $data data
     */
    public function onRegister($data) {
        if (empty($data['name']) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong name/host/port');
        }

        Connection::getInstance()->register($data['name'], $data['host'], $data['port']);
    }


    /**
     * onUnregister
     *
     * @param array $data data
     */
    public function onUnregister($data) {
        if (empty($data['name']) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong name/host/port');
        }

        Connection::getInstance()->unregister($data['name'], $data['host'], $data['port']);
    }


    /**
     * onDiscover
     *
     * @param array $data data
     */
    public function onDiscover($data) {
        if (empty($data['name'])) {
            throw new Exception('wrong name');
        }

        if (!isset($data['addresses'])) {
            $data['addresses'] = null;
        }

        Connection::getInstance()->discover($data['name'], $data['addresses']);
    }
}
