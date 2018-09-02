<?php
/*
 * MySQL
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/01/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


/**
 * MySQL
 */
trait MySQL {
    /*
     * MySQL Master
     *
     * @property array
     */
    protected $_mysqlMaster = null;


    /*
     * MySQL Slaves
     *
     * @property array
     */
    protected $_mysqlSlaves = [];


    /**
     * Set MySQL master.
     *
     * @param array $master master
     */
    public function setMySQLMaster($master) {
        $this->_mysqlMaster = $master;
    }


    /**
     * Get MySQL master.
     *
     * @return array
     */
    public function getMySQLMaster() {
        return $this->_mysqlMaster;
    }


    /**
     * Set MySQL slaves.
     *
     * @param array $slaves slaves
     */
    public function setMySQLSlaves($slaves) {
        $this->_mysqlSlaves = $slaves;
    }


    /**
     * Get MySQL slaves.
     *
     * @return array
     */
    public function getMySQLSlaves() {
        return $this->_mysqlSlaves;
    }


    /**
     * Add MySQL connections.
     */
    public function _addMySQLConnections() {
        MySQLConnection::getInstance()->setMaster($this->_mysqlMaster);
        MySQLConnection::getInstance()->setSlaves($this->_mysqlSlaves);
    }
}
