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
    /**
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
        return $this->_call('call', $namespace, $class, $method, $arguments);
    }


    /**
     * Call static.
     *
     * @param string $namespace
     * @param string $class
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function callStatic($namespace, $class, $method, $arguments) {
        return $this->_call('callstatic', $namespace, $class, $method, $arguments);
    }


    /*
     * Call.
     *
     * @param string $type
     * @param string $namespace
     * @param string $class
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function _call($type, $namespace, $class, $method, $arguments) {
        if (empty($type) || empty($namespace) || empty($class) || empty($method)) {
            throw new Exception('wrong type/namespace/class/method');
        }

        $this->send($type, [
            'namespace' => $namespace,
            'class' => $class,
            'method' => $method,
            'arguments' => $arguments
        ]);
        $data = $this->receive();
        if (empty($data)) {
            throw new Exception('wrong data');
        }

        if (empty($data['type']) || ($data['type'] != $type)) {
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
