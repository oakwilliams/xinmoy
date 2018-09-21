<?php
/*
 * Base Http
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/21/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Base;


use Exception;

use Xinmoy\Client\CURL;


/**
 * Base Http
 */
class BaseHttp {
    /*
     * Client
     *
     * @property CURL
     */
    protected $_client = null;


    /**
     * Construct.
     */
    public function __construct() {
        $this->_client = new CURL();
    }


    /**
     * Get.
     *
     * @param string $url  url
     * @param array  $data optional, data
     *
     * @return array
     */
    public function get($url, $data = null) {
        if (empty($url)) {
            throw new Exception('wrong url');
        }

        if (empty($this->_client)) {
            throw new Exception('curl init failed');
        }

        return $this->_client->get($url, $data);
    }


    /**
     * Post.
     *
     * @param string $url  url
     * @param array  $data optional, data
     *
     * @return array
     */
    public function post($url, $data = null) {
        if (empty($url)) {
            throw new Exception('wrong url');
        }

        if (empty($this->_client)) {
            throw new Exception('curl init failed');
        }

        return $this->_client->post($url, $data);
    }
}
