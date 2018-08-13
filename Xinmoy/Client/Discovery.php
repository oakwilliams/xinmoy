<?php
/*
 * Discovery
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/04/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Xinmoy\Swoole\Process;


/**
 * Discovery
 */
trait Discovery {
    use Process;


    /**
     * onRegister
     *
     * @param array $data data
     */
    public function onRegister($data) {
        if (empty($data['name']) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong name/host/port');
        }

        Connection::getInstance()->register($data['name'], $data['host'], $data['port']);
    }


    /**
     * onUnregister
     *
     * @param array $data data
     */
    public function onUnregister($data) {
        if (empty($data['name']) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong name/host/port');
        }

        Connection::getInstance()->unregister($data['name'], $data['host'], $data['port']);
    }


    /**
     * onDiscover
     *
     * @param array $data data
     */
    public function onDiscover($data) {
        if (empty($data['name'])) {
            throw new Exception('wrong name');
        }

        if (!isset($data['addresses'])) {
            $data['addresses'] = null;
        }

        Connection::getInstance()->discover($data['name'], $data['addresses']);
    }
}
