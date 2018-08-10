<?php
/*
 * Demo Service
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/09/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Demo\Service;


use Exception;


/**
 * Demo Service
 */
class DemoService {
    /**
     * Test.
     *
     * @param string $hello hello
     * @param string $world world
     *
     * @return string
     */
    public function test($hello, $world) {
        if (empty($hello) || empty($world)) {
            throw new Exception('wrong hello/world', 2);
        }

        return "{$hello}, {$world}!";
    }
}
