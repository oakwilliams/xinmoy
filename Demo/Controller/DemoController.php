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


    /**
     * Select a greeting.
     *
     * @param array $data data
     *
     * @return array
     */
    public function selectGreeting($data) {
        if (empty($data['id'])) {
            throw new Exception('wrong id', 2);
        }

        $demo_service = new DemoService();
        $greeting = $demo_service->selectGreeting($data['id']);
        return [
            'greeting' => $greeting
        ];
    }


    /**
     * Insert a greeting.
     *
     * @param array $data data
     *
     * @return array
     */
    public function insertGreeting($data) {
        if (empty($data['hello']) || empty($data['world'])) {
            throw new Exception('wrong hello/world', 2);
        }

        $demo_service = new DemoService();
        $greeting_id = $demo_service->insertGreeting([
            'hello' => $data['hello'],
            'world' => $data['world'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return [
            'greeting_id' => $greeting_id
        ];
    }


    /**
     * Update a greeting.
     *
     * @param array $data data
     *
     * @return array
     */
    public function updateGreeting($data) {
        if (empty($data['id']) || empty($data['hello']) || empty($data['world'])) {
            throw new Exception('wrong id/hello/world', 2);
        }

        $demo_service = new DemoService();
        $demo_service->updateGreeting($data['id'], [
            'hello' => $data['hello'],
            'world' => $data['world']
        ]);
    }


    /**
     * Delete a greeting.
     *
     * @param array $data data
     *
     * @return array
     */
    public function deleteGreeting($data) {
        if (empty($data['id'])) {
            throw new Exception('wrong id', 2);
        }

        $demo_service = new DemoService();
        $demo_service->deleteGreeting($data['id']);
    }


    /**
     * Switch a greeting.
     *
     * @param array $data data
     *
     * @return array
     */
    public function switchGreeting($data) {
        if (empty($data['id'])) {
            throw new Exception('wrong id', 2);
        }

        $demo_service = new DemoService();
        $demo_service->switchGreeting($data['id']);
    }


    /**
     * Set.
     *
     * @param array $data data
     *
     * @return array
     */
    public function set($data) {
        if (empty($data['demo'])) {
            throw new Exception('wrong demo', 2);
        }

        $demo_service = new DemoService();
        $demo_service->set($data['demo']);
    }


    /**
     * Get.
     *
     * @param array $data data
     *
     * @return array
     */
    public function get($data) {
        $demo_service = new DemoService();
        $demo = $demo_service->get();
        return [
            'demo' => $demo
        ];
    }


    /**
     * Triple.
     *
     * @param array $data data
     *
     * @return array
     */
    public function triple($data) {
        $demo_service = new DemoService();
        $demo_service->triple();
    }
}
