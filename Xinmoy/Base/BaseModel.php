<?php
/*
 * Base Model
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/22/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Base;


use Exception;

use Xinmoy\Client\MySQLConnection;


/**
 * Base Model
 */
class BaseModel {
    /*
     * Master
     *
     * @const int
     */
    const MASTER = 0;


    /*
     * Master Slave
     *
     * @const int
     */
    const MASTER_SLAVE = 1;


    /*
     * Master
     *
     * @property array
     */
    protected $_master = null;


    /*
     * Slave
     *
     * @property array
     */
    protected $_slave = null;


    /*
     * Mode
     *
     * @property int
     */
    protected $_mode = -1;


    /*
     * Table
     *
     * @property string
     */
    protected $_table = '';


    /*
     * Primary Key
     *
     * @property string
     */
    protected $_primaryKey = '';


    /**
     * Construct.
     */
    public function __construct() {
        $this->_master = MySQLConnection::getInstance()->master();
        $this->_slave = MySQLConnection::getInstance()->selectSlave();
        $this->setMode(self::MASTER_SLAVE);
        $this->_table = $this->_getDefaultTable();
        $this->_primaryKey = '`id`';
    }


    /**
     * Set mode.
     *
     * @param int $mode mode
     */
    public function setMode($mode) {
        if (!in_array($mode, [ self::MASTER, self::MASTER_SLAVE ])) {
            throw new Exception('wrong mode');
        }

        $this->_mode = $mode;
    }


    /**
     * Get mode.
     *
     * @return int
     */
    public function getMode() {
        return $this->_mode;
    }


    /*
     * Get default table.
     *
     * @return string
     */
    protected function _getDefaultTable() {
        $model = get_called_class();
        $model = explode('\\', $model);
        $class = array_pop($model);
        $matches = [];
        if (!preg_match('/^(.+)Model$/', $class, $matches)) {
            throw new Exception('wrong class');
        }
        $table = $matches[1];
        $table = preg_replace('/([a-z0-9])([A-Z])/', '${1}_${2}', $table);
        $table = strtolower($table);
        return "`{$table}`";
    }


    /**
     * Transact.
     *
     * @param function $callback callback
     */
    public function transact($callback) {
        if (empty($callback)) {
            throw new Exception('wrong callback');
        }

        if (empty($this->_master['connection'])) {
            throw new Exception('master init failed');
        }

        $this->_master['connection']->transact($callback);
    }


    /**
     * Get last insert id.
     *
     * @return string
     */
    public function getLastInsertId() {
        if (empty($this->_master['connection'])) {
            throw new Exception('master init failed');
        }

        return $this->_master['connection']->getLastInsertId();
    }


    /**
     * Query.
     *
     * @param string $statement statement
     * @param array  $values    optional, values
     *
     * @return array
     */
    public function query($statement, $values = null) {
        if (empty($statement)) {
            throw new Exception('wrong statement');
        }

        $client = null;
        switch ($this->_mode) {
            case self::MASTER:
                $client = $this->_master;
                break;

            case self::MASTER_SLAVE:
                $client = $this->_slave;
                break;

            default:
                throw new Exception('wrong mode');
        }

        if (empty($client['connection'])) {
            throw new Exception('client init failed');
        }

        return $client['connection']->query($statement, $values);
    }


    /**
     * Execute.
     *
     * @param string $statement statement
     * @param array  $values    optional, values
     *
     * @return int
     */
    public function execute($statement, $values = null) {
        if (empty($statement)) {
            throw new Exception('wrong statement');
        }

        if (empty($this->_master['connection'])) {
            throw new Exception('master init failed');
        }

        return $this->_master['connection']->execute($statement, $values);
    }


    /**
     * Select.
     *
     * @param int    $id     id
     * @param string $fields optional, fields
     *
     * @return array
     */
    public function select($id, $fields = '*') {
        if (($id <= 0) || empty($fields)) {
            throw new Exception('wrong id/fields');
        }

        if (empty($this->_table) || empty($this->_primaryKey)) {
            throw new Exception('wrong table/primary key');
        }

        $primary_key = preg_replace('/`/', '', $this->_primaryKey);
        $rows = $this->query("SELECT {$fields} FROM {$this->_table} WHERE {$this->_primaryKey} = :{$primary_key}", [
            ":{$primary_key}" => $id
        ]);
        return empty($rows) ? null : reset($rows);
    }


    /**
     * Insert.
     *
     * @param array $row row
     *
     * @return int
     */
    public function insert($row) {
        if (empty($row)) {
            throw new Exception('wrong row');
        }

        if (empty($this->_table)) {
            throw new Exception('wrong table');
        }

        $columns = array_keys($row);
        $columns = array_map(function($column) {
            return "`{$column}`";
        }, $columns);
        $columns = join(', ', $columns);
        foreach ($row as $key => $value) {
            $row[":{$key}"] = $value;
            unset($row[$key]);
        }
        $keys = array_keys($row);
        $keys = join(', ', $keys);
        $count = $this->execute("INSERT {$this->_table} ({$columns}) VALUE ({$keys})", $row);
        return empty($count) ? 0 : $this->getLastInsertId();
    }


    /**
     * Update.
     *
     * @param int   $id  id
     * @param array $row row
     *
     * @return int
     */
    public function update($id, $row) {
        if (($id <= 0) || empty($row)) {
            throw new Exception('wrong id/row');
        }

        if (empty($this->_table) || empty($this->_primaryKey)) {
            throw new Exception('wrong table/primary key');
        }

        $columns = array_keys($row);
        $pairs = array_map(function($column) {
            return "`{$column}` = :{$column}";
        }, $columns);
        $pairs = join(', ', $pairs);
        $primary_key = preg_replace('/`/', '', $this->_primaryKey);
        $row[$primary_key] = $id;
        foreach ($row as $key => $value) {
            $row[":{$key}"] = $value;
            unset($row[$key]);
        }
        return $this->execute("UPDATE {$this->_table} SET {$pairs} WHERE {$this->_primaryKey} = :{$primary_key}", $row);
    }


    /**
     * Delete.
     *
     * @param int $id id
     *
     * @return int
     */
    public function delete($id) {
        if ($id <= 0) {
            throw new Exception('wrong id');
        }

        if (empty($this->_table) || empty($this->_primaryKey)) {
            throw new Exception('wrong table/primary key');
        }

        $primary_key = preg_replace('/`/', '', $this->_primaryKey);
        return $this->execute("DELETE FROM {$this->_table} WHERE {$this->_primaryKey} = :{$primary_key}", [
            ":{$primary_key}" => $id
        ]);
    }
}
