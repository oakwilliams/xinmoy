<?php
/*
 * Register
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/01/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Exception;


/**
 * Register
 */
trait Register {
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
}
