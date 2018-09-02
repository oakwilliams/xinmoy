<?php
/*
 * Http Server
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/16/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Server;


use Xinmoy\Swoole\HttpServer as SwooleHttpServer;
use Xinmoy\Client\Register;
use Xinmoy\Client\Discovery;


/**
 * Http Server
 */
class HttpServer extends SwooleHttpServer {
    use Register, Discovery;


    /**
     * Start.
     */
    public function start() {
        $this->_addDiscoveryProcess();

        parent::start();
    }
}
