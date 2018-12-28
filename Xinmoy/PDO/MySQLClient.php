<?php
/*
 * MySQL Client
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/20/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\PDO;


use Exception;
use PDO;

use Xinmoy\Lib\Log;


/**
 * MySQL Client
 */
class MySQLClient {
    /**
     * Max Retry Times
     *
     * @const int
     */
    const MAX_RETRY_TIMES = 3;


    /*
     * PDO
     *
     * @property PDO
     */
    protected $_pdo = null;


    /*
     * Database
     *
     * @property string
     */
    protected $_database = '';


    /*
     * Host
     *
     * @property string
     */
    protected $_host = '';


    /*
     * Port
     *
     * @property int
     */
    protected $_port = -1;


    /*
     * User
     *
     * @property string
     */
    protected $_user = '';


    /*
     * Password
     *
     * @property string
     */
    protected $_password = '';


    /**
     * Construct.
     *
     * @param string $database database
     * @param string $host     optional, host
     * @param int    $port     optional, port
     * @param string $user     optional, user
     * @param string $password optional, password
     */
    public function __construct($database, $host = '127.0.0.1', $port = 3306, $user = '', $password = '') {
        if (empty($database) || empty($host) || ($port < 0)) {
            throw new Exception('wrong database/host/port');
        }

        $this->_database = $database;
        $this->_host = $host;
        $this->_port = $port;
        $this->_user = $user;
        $this->_password = $password;
        $this->_connect();
    }


    /*
     * Connect.
     */
    protected function _connect() {
        if (empty($this->_database) || empty($this->_host) || ($this->_port < 0)) {
            throw new Exception('wrong database/host/port');
        }

        $this->_pdo = new PDO("mysql:dbname={$this->_database};host={$this->_host};port={$this->_port}", $this->_user, $this->_password, [
            PDO::ATTR_PERSISTENT => true
        ]);
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

        if (empty($this->_pdo)) {
            throw new Exception('init failed');
        }

        if ($this->_pdo->inTransaction()) {
            $callback();
        } else {
            $i = 0;
            while ($i < self::MAX_RETRY_TIMES) {
                if ($this->_pdo->beginTransaction()) {
                    break;
                }

                if ($i == self::MAX_RETRY_TIMES - 1) {
                    throw new Exception('begin transaction failed');
                }

                $this->_connect();
                $i++;
            }

            try {
                $callback();
                $this->_pdo->commit();
            } catch (Exception $e) {
                $this->_pdo->rollBack();
                throw $e;
            }
        }
    }


    /**
     * Get last insert id.
     *
     * @return string
     */
    public function getLastInsertId() {
        if (empty($this->_pdo)) {
            throw new Exception('init failed');
        }

        $id = $this->_pdo->lastInsertId();
        Log::getInstance()->log('insert: ' . json_encode([
            'last_insert_id' => $id
        ]));
        return $id;
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
        $statement = $this->_execute($statement, $values);
        if (empty($statement)) {
            throw new Exception('wrong statement');
        }

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        Log::getInstance()->log('query: ' . json_encode([
            'rows' => $rows
        ]));
        return $rows;
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
        $statement = $this->_execute($statement, $values);
        if (empty($statement)) {
            throw new Exception('wrong statement');
        }

        $count = $statement->rowCount();
        Log::getInstance()->log('execute: ' . json_encode([
            'count' => $count
        ]));
        return $count;
    }


    /*
     * Execute.
     *
     * @param string $statement statement
     * @param array  $values    optional, values
     *
     * @return PDOStatement
     */
    protected function _execute($statement, $values = null) {
        Log::getInstance()->log('execute: '. json_encode([
            'statement' => $statement,
            'values' => $values
        ]));
        if (empty($statement)) {
            throw new Exception('wrong statement');
        }

        if (empty($this->_pdo)) {
            throw new Exception('init failed');
        }

        $pdo_statement = null;
        try {
            $i = 0;
            while ($i < self::MAX_RETRY_TIMES) {
                $pdo_statement = $this->_pdo->prepare($statement);
                if ($pdo_statement->execute($values)) {
                    break;
                }

                $error = $pdo_statement->errorInfo();
                if (empty($error)) {
                    break;
                }

                if (($error[1] != 2006) || ($i == self::MAX_RETRY_TIMES - 1)) {
                    throw new Exception($error[2]);
                }

                $this->_connect();
                $i++;
            }
        } catch (Exception $e) {
            handle_exception($e);
            throw new Exception('sql error');
        }
        return $pdo_statement;
    }
}
