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
     * Start.
     */
    public function start() {
        $this->_addRegistrationProcess();

        parent::start();
    }


    /*
     * Add registration process.
     */
    protected function _addRegistrationProcess() {
        if (empty($this->_registerHost) || ($this->_registerPort < 0)) {
            throw new Exception('wrong register host/port');
        }

        if (empty($this->_name)) {
            throw new Exception('wrong name');
        }

        if ($this->_port < 0) {
            throw new Exception('wrong server port');
        }

        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        $register_host = $this->_registerHost;
        $register_port = $this->_registerPort;
        $name = $this->_name;
        $port = $this->_port;
        $process = new Process(function() use ($register_host, $register_port, $name, $port) {
            $client = new RegistrationClient($register_host, $register_port);
            $client->setServer($name);
            $client->setServerPort($port);
            $client->connect();
        });
        $this->_server->addProcess($process);
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