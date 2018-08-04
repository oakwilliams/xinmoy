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
     * Servers
     *
     * @property array
     */
    protected $_servers = [];


    /**
     * Set servers.
     *
     * @param array $servers servers
     */
    public function setServers($servers) {
        $this->_servers = $servers;
    }


    /**
     * Get servers.
     *
     * @return array
     */
    public function getServers() {
        return $this->_servers;
    }


    /**
     * onConnect
     *
     * @param Client $client client
     */
    public function onConnect($client) {
        try {
            parent::onConnect($client);

            $this->discoverMany($this->_servers);
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
        if (empty($data['server']) || !isset($data['fd']) || ($data['fd'] < 0) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong server/fd/host/port');
        }

        $has = ServerAddress::getInstance()->has($data['server'], $data['host'], $data['port']);
        ServerAddress::getInstance()->register($data['server'], $data['fd'], $data['host'], $data['port']);
        if (empty($has)) {
            $this->write('register', [
                'server' => $data['server'],
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
        if (empty($data['server']) || !isset($data['fd']) || ($data['fd'] < 0) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong server/fd/host/port');
        }

        ServerAddress::getInstance()->unregister($data['server'], $data['fd'], $data['host'], $data['port']);
        $has = ServerAddress::getInstance()->has($data['server'], $data['host'], $data['port']);
        if (empty($has)) {
            $this->write('unregister', [
                'server' => $data['server'],
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
        if (empty($data['server'])) {
            throw new Exception('wrong server');
        }

        if (!isset($data['addresses'])) {
            $data['addresses'] = [];
        }

        ServerAddress::getInstance()->set($data['server'], $data['addresses']);
        $filtered = ServerAddress::getInstance()->filter($data['server']);
        $this->write('discover', [
            'server' => $data['server'],
            'addresses' => $filtered
        ]);
    }


    /**
     * Discover many.
     *
     * @param array $servers servers
     */
    public function discoverMany($servers) {
        foreach ($this->_discoverMany($servers) as $i) { }
    }


    /*
     * Discover many.
     *
     * @param array $servers servers
     */
    protected function _discoverMany($servers) {
        foreach ($servers as $server) {
            yield $this->send('discover', [
                'server' => $server
            ]);
        }
    }
}
