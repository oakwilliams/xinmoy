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
     * Server Name
     *
     * @property string
     */
    protected $_serverName = '';


    /*
     * Server Port
     *
     * @property int
     */
    protected $_serverPort = 8000;


    /**
     * Set server name.
     *
     * @param string $server_name server name
     */
    public function setServerName($server_name) {
        if (empty($server_name)) {
            throw new Exception('wrong server name');
        }

        $this->_serverName = $server_name;
    }


    /**
     * Get server name.
     *
     * @return string
     */
    public function getServerName() {
        return $this->_serverName;
    }


    /**
     * Set server port.
     *
     * @param int $server_port optional, server port
     */
    public function setServerPort($server_port = 8000) {
        if ($server_port < 0) {
            throw new Exception('wrong server port');
        }

        $this->_serverPort = $server_port;
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

            if (empty($this->_serverName) || ($this->_serverPort < 0)) {
                throw new Exception('wrong server name/port');
            }

            $this->send('register', [
                'name' => $this->_serverName,
                'port' => $this->_serverPort
            ]);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }
}
