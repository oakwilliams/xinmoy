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

use Xinmoy\Swoole\Server as SwooleServer;
use Xinmoy\Client\RegistrationClient;
use Xinmoy\Client\DiscoveryClient;


/**
 * Server
 */
class Server extends SwooleServer {
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
     * Servers
     *
     * @property array
     */
    protected $_servers = [];


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
        $this->_server->addProcess($process);
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
                throw new Exception('wrong server port');
            }

            $client = new RegistrationClient($this->_registerHost, $this->_registerPort);
            $client->setServer($this->_name);
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
            $client->setServers($this->_servers);
            $client->connect();
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
    public function onCall($server, $fd, $reactor_id, $data) { }
}
