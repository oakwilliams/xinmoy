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
        if (empty($data['server']) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong server/host/port');
        }

        Connection::getInstance()->register($data['server'], $data['host'], $data['port']);
    }


    /**
     * onUnregister
     *
     * @param array $data data
     */
    public function onUnregister($data) {
        if (empty($data['server']) || empty($data['host']) || !isset($data['port']) || ($data['port'] < 0)) {
            throw new Exception('wrong server/host/port');
        }

        Connection::getInstance()->unregister($data['server'], $data['host'], $data['port']);
    }


    /**
     * onDiscover
     *
     * @param array $data data
     */
    public function onDiscover($data) {
        if (empty($data['server'])) {
            throw new Exception('wrong server');
        }

        if (!isset($data['addresses'])) {
            $data['addresses'] = [];
        }

        Connection::getInstance()->discover($data['server'], $data['addresses']);
    }
}
