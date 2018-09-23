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

use Xinmoy\Swoole\Server as SwooleServer;
use Xinmoy\Client\Register;
use Xinmoy\Client\Registration;
use Xinmoy\Client\Discovery;
use Xinmoy\Client\MySQL;
use Xinmoy\Client\Redis;


/**
 * Server
 */
class Server extends SwooleServer {
    use Register, Registration, Discovery, MySQL, Redis;


    /**
     * Start.
     */
    public function start() {
        $this->_addRegistrationProcess();
        $this->_addDiscoveryProcess();
        $this->_addMySQLConnections();
        $this->_addRedisConnections();

        parent::start();
    }


    /**
     * onCall
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     * @param object $data       data
     */
    public function onCall($server, $fd, $reactor_id, $data) {
        try {
            if (empty($data['namespace']) || empty($data['class']) || empty($data['method'])) {
                throw new Exception('wrong namespace/class/method');
            }

            $class = "{$data['namespace']}\\{$data['class']}";
            if (!$this->classExists($class)) {
                throw new Exception('undefined class');
            }

            $object = new $class();
            if (!method_exists($object, $data['method'])) {
                throw new Exception('undefined method');
            }

            if (!isset($data['arguments'])) {
                $data['arguments'] = [];
            }

            $return = $object->{$data['method']}(...$data['arguments']);
            $this->sendReturn($fd, 'call', $return);
        } catch (Exception $e) {
            $this->sendException($fd, 'call', $e);
        }
    }


    /**
     * onCallStatic
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     * @param object $data       data
     */
    public function onCallStatic($server, $fd, $reactor_id, $data) {
        try {
            if (empty($data['namespace']) || empty($data['class']) || empty($data['method'])) {
                throw new Exception('wrong namespace/class/method');
            }

            $class = "{$data['namespace']}\\{$data['class']}";
            if (!$this->classExists($class)) {
                throw new Exception('undefined class');
            }

            if (!isset($data['arguments'])) {
                $data['arguments'] = [];
            }

            $return = $class::{$data['method']}(...$data['arguments']);
            $this->sendReturn($fd, 'callstatic', $return);
        } catch (Exception $e) {
            $this->sendException($fd, 'callstatic', $e);
        }
    }


    /*
     * Class exists?
     *
     * @param string $class class
     *
     * @return bool
     */
    protected function classExists($class) {
        if (empty($class)) {
            throw new Exception('wrong class');
        }

        $class = preg_replace('/\\\\/', '/', $class);
        return file_exists(__DIR__ . "/../../{$class}.php");
    }


    /**
     * Send result.
     *
     * @param int    $fd      fd
     * @param string $type    type
     * @param int    $code    optional, code
     * @param string $message optional, message
     * @param mixed  $return  optional, return
     */
    public function sendResult($fd, $type, $code = 0, $message = 'ok', $return = null) {
        if (($fd < 0) || empty($type)) {
            throw new Exception('wrong fd/type');
        }

        $this->send($fd, $type, [
            'code' => $code,
            'message' => $message,
            'data' => $return
        ]);
    }


    /**
     * Send return.
     *
     * @param int    $fd     fd
     * @param string $type   type
     * @param mixed  $return optional, return
     */
    public function sendReturn($fd, $type, $return = null) {
        $this->sendResult($fd, $type, 0, 'ok', $return);
    }


    /**
     * Send exception.
     *
     * @param int       $fd   fd
     * @param string    $type type
     * @param Exception $e    exception
     */
    public function sendException($fd, $type, $e) {
        if (($fd < 0) || empty($type)) {
            throw new Exception('wrong fd/type');
        }

        if (empty($e)) {
            $e = new Exception('system error', 1);
        }

        $code = $e->getCode();
        $message = $e->getMessage();

        if (empty($code)) {
            $code = 1;
        }

        $this->sendResult($fd, $type, $code, $message, null);
    }
}
