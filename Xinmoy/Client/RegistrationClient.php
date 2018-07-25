<?php
/*
 * Registration Client
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   06/27/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Exception;

use Xinmoy\Swoole\AsyncClient;


/**
 * Registration Client
 */
class RegistrationClient extends AsyncClient {
    /*
     * Server
     *
     * @property string $server server
     */
    protected $_server = '';


    /*
     * Server Port
     *
     * @property int
     */
    protected $_serverPort = 8000;


    /**
     * Set server.
     *
     * @param string $server server
     */
    public function setServer($server) {
        if (empty($server)) {
            throw new Exception('wrong server');
        }

        $this->_server = $server;
    }


    /**
     * Get server.
     *
     * @return string
     */
    public function getServer() {
        return $this->_server;
    }


    /**
     * Set server port.
     *
     * @param int $port optional, port
     */
    public function setServerPort($port = 8000) {
        if ($port < 0) {
            throw new Exception('wrong server port');
        }

        $this->_serverPort = $port;
    }


    /**
     * Get server port.
     *
     * @return int
     */
    public function getServerPort() {
        return $this->_serverPort;
    }


    /**
     * onConnect
     *
     * @param Client $client client
     */
    public function onConnect($client) {
        try {
            parent::onConnect($client);

            if (empty($this->_server)) {
                throw new Exception('wrong server');
            }

            if ($this->_serverPort < 0) {
                throw new Exception('wrong server port');
            }

            $this->send('register', [
                'server' => $this->_server,
                'port' => $this->_serverPort
            ]);
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
    public function onRegister($client, $data) { }


    /**
     * onUnregister
     *
     * @param Client $client client
     * @param array  $data   data
     */
    public function onUnregister($client, $data) { }


    /**
     * onDiscover
     *
     * @param Client $client client
     * @param array  $data   data
     */
    public function onDiscover($client, $data) { }
}
