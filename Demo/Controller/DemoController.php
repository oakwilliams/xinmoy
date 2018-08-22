<?php
/*
 * Demo Controller
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/19/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Demo\Controller;


use Exception;

use Xinmoy\Base\BaseController;

use Demo\Service\DemoService;


/**
 * Demo Controller
 */
class DemoController extends BaseController {
    /**
     * Test.
     *
     * @param array $data            data
     * @param array $request_cookie  optional, request cookie
     * @param array $response_cookie optional, response cookie
     *
     * @return array
     */
    public function test($data, $request_cookie = null, &$response_cookie = null) {
        if (empty($data['hello']) || empty($data['world'])) {
            throw new Exception('wrong hello/world', 2);
        }

        $demo_service = new DemoService();
        $greeting = $demo_service->test($data['hello'], $data['world']);
        $response_cookie = $request_cookie;
        return [
            'greeting' => $greeting
        ];
    }
}
