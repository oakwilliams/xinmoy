<?php
/*
 * Register
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   05/02/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Register;


use Exception;

use Xinmoy\Swoole\Server;
use Xinmoy\Swoole\Session;
use Xinmoy\Swoole\Group;
use Xinmoy\Lib\ServerAddress;


/**
 * Register
 */
class Register extends Server {
    /**
     * onClose
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     */
    public function onClose($server, $fd, $reactor_id) {
        try {
            $type = Session::getInstance()->get($fd, 'type');
            if ($type == 'server') {
                $name = Session::getInstance()->get($fd, 'name');
                $host = Session::getInstance()->get($fd, 'host');
                $port = Session::getInstance()->get($fd, 'port');

                if (empty($name) || empty($host) || ($port < 0)) {
                    throw new Exception('wrong name/host/port');
                }

                ServerAddress::getInstance()->unregister($name, $fd, $host, $port);
                $this->sendToGroup($name, 'unregister', [
                    'name' => $name,
                    'fd' => $fd,
                    'host' => $host,
                    'port' => $port
                ]);
            }
            Group::getInstance()->leaveAll($fd);

            parent::onClose($server, $fd, $reactor_id);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $this->sendError($fd, $message);
        }
    }


    /**
     * onRegister.
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     * @param object $data       data
     */
    public function onRegister($server, $fd, $reactor_id, $data) {
        if (empty($data['name']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong name/port');
        }

        $connection = $server->connection_info($fd);
        if (empty($connection)) {
            throw new Exception('nonexisted connection');
        }

        Session::getInstance()->set($fd, 'type', 'server');
        Session::getInstance()->set($fd, 'name', $data['name']);
        Session::getInstance()->set($fd, 'host', $connection['remote_ip']);
        Session::getInstance()->set($fd, 'port', $data['port']);
        Group::getInstance()->join($fd, $data['name']);
        ServerAddress::getInstance()->register($data['name'], $fd, $connection['remote_ip'], $data['port']);
        $this->sendToGroup($data['name'], 'register', [
            'name' => $data['name'],
            'fd' => $fd,
            'host' => $connection['remote_ip'],
            'port' => $data['port']
        ]);
    }


    /**
     * onDiscover.
     *
     * @param Server $server     server
     * @param int    $fd         fd
     * @param int    $reactor_id reactor id
     * @param object $data       data
     */
    public function onDiscover($server, $fd, $reactor_id, $data) {
        if (empty($data['name'])) {
            throw new Exception('wrong name');
        }

        Group::getInstance()->join($fd, $data['name']);
        $addresses = ServerAddress::getInstance()->discover($data['name']);
        $this->send($fd, 'discover', [
            'name' => $data['name'],
            'addresses' => $addresses
        ]);
    }
}
