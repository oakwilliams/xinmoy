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

use Xinmoy\Base\BaseService;

use Demo\Model\GreetingModel;
use Demo\Cache\DemoCache;
use Demo\Http\DemoHttp;


/**
 * Demo Service
 */
class DemoService extends BaseService {
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


    /**
     * Test static.
     *
     * @param string $hello hello
     * @param string $world world
     *
     * @return string
     */
    public static function testStatic($hello, $world) {
        $demo_service = new static();
        return $demo_service->test($hello, $world);
    }


    /**
     * Select.
     *
     * @param int    $id     id
     * @param string $fields optional, fields
     *
     * @return array
     */
    public function selectGreeting($id, $fields = '*') {
        $greeting_model = new GreetingModel();
        return $greeting_model->select($id, $fields);
    }


    /**
     * Insert a greeting.
     *
     * @param array $greeting greeting
     *
     * @return int
     */
    public function insertGreeting($greeting) {
        $greeting_model = new GreetingModel();
        return $greeting_model->insert($greeting);
    }


    /**
     * Update a greeting.
     *
     * @param int   $id       id
     * @param array $greeting greeting
     *
     * @return int
     */
    public function updateGreeting($id, $greeting) {
        $greeting_model = new GreetingModel();
        return $greeting_model->update($id, $greeting);
    }


    /**
     * Delete a greeting.
     *
     * @param int $id id
     *
     * @return int
     */
    public function deleteGreeting($id) {
        $greeting_model = new GreetingModel();
        return $greeting_model->delete($id);
    }


    /**
     * Switch a greeting.
     *
     * @param int $id id
     */
    public function switchGreeting($id) {
        // Select a greeting.
        $greeting_model = new GreetingModel();
        $greeting_model->setMode(GreetingModel::MASTER);
        $greeting = $greeting_model->select($id, 'hello, world');
        if (empty($greeting)) {
            throw new Exception('wrong greeting');
        }

        // Update a greeting.
        $greeting_model->transact(function() use ($greeting_model, $id, $greeting) {
            $greeting_model->transact(function() use ($greeting_model, $id, $greeting) {
                $greeting_model->update($id, [
                    'hello' => $greeting['world']
                ]);
                $greeting_model->update($id, [
                    'world' => $greeting['hello']
                ]);
            });
        });
    }


    /**
     * Set.
     *
     * @param string $demo demo
     *
     * @return bool
     */
    public function set($demo) {
        $demo_cache = new DemoCache();
        $demo_cache->set($demo);
    }


    /**
     * Get.
     *
     * @return string
     */
    public function get() {
        $demo_cache = new DemoCache();
        return $demo_cache->get();
    }


    /**
     * Triple.
     */
    public function triple() {
        $demo_cache = new DemoCache();
        $demo_cache->setMode(DemoCache::MASTER);
        $demo = $demo_cache->get();
        $demo_cache->set("{$demo}{$demo}{$demo}");
    }


    /**
     * Test get.
     *
     * @param array $data data
     *
     * @return array
     */
    public function testGet($data) {
        $demo_http = new DemoHttp();
        return $demo_http->testGet($data);
    }


    /**
     * Test post.
     *
     * @param array $data data
     *
     * @return array
     */
    public function testPost($data) {
        $demo_http = new DemoHttp();
        return $demo_http->testPost($data);
    }
}
