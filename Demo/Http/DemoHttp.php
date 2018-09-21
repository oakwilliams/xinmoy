<?php
/*
 * Demo Http
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/21/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Demo\Http;


use Xinmoy\Base\BaseHttp;


/**
 * Demo Http
 */
class DemoHttp extends BaseHttp {
    /**
     * Test get.
     *
     * @param array $data data
     *
     * @return array
     */
    public function testGet($data) {
        return $this->get('http://172.17.0.4/Demo/Demo/test', $data);
    }


    /**
     * Test post.
     *
     * @param array $data data
     *
     * @return array
     */
    public function testPost($data) {
        return $this->post('http://172.17.0.4/Demo/Demo/test', $data);
    }
}
