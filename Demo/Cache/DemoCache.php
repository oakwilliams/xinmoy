<?php
/*
 * Demo Cache
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/29/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Demo\Cache;


use Xinmoy\Base\BaseCache;


/**
 * Demo Cache
 */
class DemoCache extends BaseCache {
    /**
     * Set.
     *
     * @param string $demo demo
     *
     * @return bool
     */
    public function set($demo) {
        return $this->execute('SET', 'demo', $demo);
    }


    /**
     * Get.
     *
     * @return string
     */
    public function get() {
        return $this->query('GET', 'demo');
    }
}
