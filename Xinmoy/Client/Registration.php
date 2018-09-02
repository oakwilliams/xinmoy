<?php
/*
 * Registration
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/01/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Swoole\Process;


/**
 * Registration
 */
trait Registration {
    /*
     * Name
     *
     * @property string
     */
    protected $_name = '';


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

            if (empty($this->_name) || ($this->_port < 0)) {
                throw new Exception('wrong name/port');
            }

            $client = new RegistrationClient($this->_registerHost, $this->_registerPort);
            $client->setServerName($this->_name);
            $client->setServerPort($this->_port);
            $client->connect();
        } catch (Exception $e) {
            handle_exception($e);
        }
    }
}
