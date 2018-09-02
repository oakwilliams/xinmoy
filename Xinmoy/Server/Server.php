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
            if (!class_exists($class)) {
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
            $this->sendReturn($fd, $return);
        } catch (Exception $e) {
            $this->sendException($fd, $e);
        }
    }


    /**
     * Send result.
     *
     * @param int    $fd      fd
     * @param int    $code    optional, code
     * @param string $message optional, message
     * @param mixed  $return  optional, return
     */
    public function sendResult($fd, $code = 0, $message = 'ok', $return = null) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        $this->send($fd, 'call', [
            'code' => $code,
            'message' => $message,
            'data' => $return
        ]);
    }


    /**
     * Send return.
     *
     * @param int   $fd     fd
     * @param mixed $return optional, return
     */
    public function sendReturn($fd, $return = null) {
        $this->sendResult($fd, 0, 'ok', $return);
    }


    /**
     * Send exception.
     *
     * @param int       $fd fd
     * @param Exception $e  exception
     */
    public function sendException($fd, $e) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        if (empty($e)) {
            $e = new Exception('system error', 1);
        }

        $code = $e->getCode();
        $message = $e->getMessage();

        if (empty($code)) {
            $code = 1;
        }

        $this->sendResult($fd, $code, $message, null);
    }
}
