<?php
/*
 * Call Client
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/06/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Client;


use Exception;

use Xinmoy\Swoole\SyncClient;


/**
 * Call Client
 */
class CallClient extends SyncClient {
    /*
     * Call.
     *
     * @param string $namespace
     * @param string $class
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function call($namespace, $class, $method, $arguments) {
        if (empty($namespace) || empty($class) || empty($method)) {
            echo 'test';
            throw new Exception('wrong namespace/class/method');
        }

        $this->send('call', [
            'namespace' => $namespace,
            'class' => $class,
            'method' => $method,
            'arguments' => $arguments
        ]);
        $data = $this->receive();
        if (empty($data)) {
            throw new Exception('wrong data');
        }

        if (empty($data['type']) || ($data['type'] != 'call')) {
            throw new Exception('wrong type');
        }

        if (!isset($data['data']['code']) || !isset($data['data']['message'])) {
            throw new Exception('wrong code/message');
        }

        if (!empty($data['data']['code'])) {
            throw new Exception($data['data']['message'], $data['data']['code']);
        }

        if (!isset($data['data']['data'])) {
            $data['data']['data'] = null;
        }

        return $data['data']['data'];
    }
}
