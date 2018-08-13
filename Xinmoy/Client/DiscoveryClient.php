<?php
/*
 * Discovery Client
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   07/27/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Xinmoy\Swoole\AsyncClient;
use Xinmoy\Swoole\Process;
use Xinmoy\Lib\ServerAddress;


/**
 * Discovery Client
 */
class DiscoveryClient extends AsyncClient {
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


    /**
     * onConnect
     *
     * @param Client $client client
     */
    public function onConnect($client) {
        try {
            parent::onConnect($client);

            $this->discoverMany($this->_dependencies);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }


    /**
     * onRegister
     *
     * @param Client $client client
     * @param array  $data   data
     */
    public function onRegister($client, $data) {
        if (empty($data['name']) || !isset($data['fd']) || ($data['fd'] < 0) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong name/fd/host/port');
        }

        $has = ServerAddress::getInstance()->has($data['name'], $data['host'], $data['port']);
        ServerAddress::getInstance()->register($data['name'], $data['fd'], $data['host'], $data['port']);
        if (empty($has)) {
            $this->write('register', [
                'name' => $data['name'],
                'host' => $data['host'],
                'port' => $data['port']
            ]);
        }
    }


    /**
     * onUnregister
     *
     * @param Client $client client
     * @param array  $data   data
     */
    public function onUnregister($client, $data) {
        if (empty($data['name']) || !isset($data['fd']) || ($data['fd'] < 0) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong name/fd/host/port');
        }

        ServerAddress::getInstance()->unregister($data['name'], $data['fd'], $data['host'], $data['port']);
        $has = ServerAddress::getInstance()->has($data['name'], $data['host'], $data['port']);
        if (empty($has)) {
            $this->write('unregister', [
                'name' => $data['name'],
                'host' => $data['host'],
                'port' => $data['port']
            ]);
        }
    }


    /**
     * onDiscover
     *
     * @param Client $client client
     * @param array  $data   data
     */
    public function onDiscover($client, $data) {
        if (empty($data['name'])) {
            throw new Exception('wrong name');
        }

        if (!isset($data['addresses'])) {
            $data['addresses'] = null;
        }

        ServerAddress::getInstance()->set($data['name'], $data['addresses']);
        $filtered = ServerAddress::getInstance()->filter($data['name']);
        $this->write('discover', [
            'name' => $data['name'],
            'addresses' => $filtered
        ]);
    }


    /**
     * Discover many.
     *
     * @param array $dependencies dependencies
     */
    public function discoverMany($dependencies) {
        foreach ($this->_discoverMany($dependencies) as $i) { }
    }


    /*
     * Discover many.
     *
     * @param array $dependencies dependencies
     */
    protected function _discoverMany($dependencies) {
        if (empty($dependencies)) {
            return;
        }

        foreach ($dependencies as $dependency) {
            yield $this->send('discover', [
                'name' => $dependency
            ]);
        }
    }
}
