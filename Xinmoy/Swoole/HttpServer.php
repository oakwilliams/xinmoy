<?php
/*
 * Http Server
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/14/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Swoole;


use Exception;

use Swoole\Http\Server;

use Xinmoy\Lib\Log;


/**
 * Http Server
 */
class HttpServer {
    /*
     * Server
     *
     * @property Server
     */
    protected $_server = null;


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


    /**
     * Construct.
     *
     * @param string $host optional, host
     * @param int    $port optional, port
     */
    public function __construct($host = '0.0.0.0', $port = 80) {
        if (empty($host) || ($port < -1)) {
            throw new Exception('wrong host/port');
        }

        $this->_host = $host;
        $this->_port = $port;
        $this->_server = new Server($host, $port, SWOOLE_BASE, SWOOLE_SOCK_TCP);
    }


    /**
     * Start.
     */
    public function start() {
        if (empty($this->_server)) {
            throw new Exception('init failed');
        }

        $this->_server->on('workerstart', [ $this, 'onWorkerStart' ]);
        $this->_server->on('request', [ $this, 'onRequest' ]);
        $this->_server->start();
    }


    /**
     * onWorkerStart
     *
     * @param Server $server    server
     * @param int    $worker_id worker id
     */
    public function onWorkerStart($server, $worker_id) { }


    /**
     * onRequest
     *
     * @param Request  $request  request
     * @param Response $response response
     */
    public function onRequest($request, $response) {
        try {
            $data = null;
            switch ($request->server['request_method']) {
                case 'GET':
                    $data = $request->get;
                    break;

                case 'POST':
                    $data = $request->post;
                    break;

                default:
                    throw new Exception('wrong request method');
            }

            Log::getInstance()->log('request: ' . json_encode([
                'request_method' => $request->server['request_method'],
                'path_info' => $request->server['path_info'],
                'cookie' => $request->cookie,
                'data' => $data
            ]));
            $path_info = explode('/', $request->server['path_info']);
            if (count($path_info) != 4) {
                throw new Exception('wrong path info');
            }

            $class = "{$path_info[1]}\\Controller\\{$path_info[2]}Controller";
            if (!class_exists($class)) {
                throw new Exception('wrong class');
            }

            $object = new $class();
            if (!method_exists($object, $path_info[3])) {
                throw new Exception('wrong method');
            }

            $cookie = null;
            $return = $object->{$path_info[3]}($data, $request->cookie, $cookie);
            $this->respondCookie($response, $cookie);
            $this->respondReturn($response, $return);
        } catch (Exception $e) {
            $this->respondException($response, $e);
        }
    }


    /**
     * Respond cookie.
     *
     * @param Response $response response
     * @param array    $cookie   optional, cookie
     */
    public function respondCookie($response, $cookie = null) {
        foreach ($this->_respondCookie($response, $cookie) as $i) { }
    }


    /*
     * Respond cookie.
     *
     * @param Response $response response
     * @param array    $cookie   optional, cookie
     */
    protected function _respondCookie($response, $cookie = null) {
        if (empty($response)) {
            throw new Exception('response init failed');
        }

        if (empty($cookie)) {
            return;
        }

        foreach ($cookie as $key => $value) {
            yield $response->cookie($key, $value, 0, '/', '', false, true);
        }
        Log::getInstance()->log('response: ' . json_encode([
            'cookie' => $cookie
        ]));
    }


    /**
     * Respond result.
     *
     * @param Response $response response
     * @param int      $code     optional, code
     * @param string   $message  optional, message
     * @param array    $return   optional, return
     */
    public function respondResult($response, $code = 0, $message = 'ok', $return = null) {
        if (empty($response)) {
            throw new Exception('response init failed');
        }

        $response->header('Content-Type', 'application/json');
        $message = json_encode([
            'code' => $code,
            'message' => $message,
            'data' => $return
        ]);
        $response->end($message);
        Log::getInstance()->log("response: {$message}");
    }


    /**
     * Respond return.
     *
     * @param Response $response response
     * @param array    $return   optional, return
     */
    public function respondReturn($response, $return = null) {
        $this->respondResult($response, 0, 'ok', $return);
    }


    /**
     * Respond exception.
     *
     * @param Response  $response response
     * @param Exception $e        exception
     */
    public function respondException($response, $e) {
        if (empty($response)) {
            throw new Exception('response init failed');
        }

        if (empty($e)) {
            $e = new Exception('system error', 1);
        }

        $code = $e->getCode();
        $message = $e->getMessage();

        if (empty($code)) {
            $code = 1;
        }

        $this->respondResult($response, $code, $message, null);
    }
}
