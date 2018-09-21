<?php
/*
 * CURL
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/21/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\CURL;


use Exception;

use Xinmoy\Lib\Log;


/**
 * CURL
 */
class CURL {
    /**
     * Get.
     *
     * @param string $url  url
     * @param array  $data optional, data
     *
     * @return array
     */
    public function get($url, $data = null) {
        Log::getInstance()->log('get: ' . json_encode([
            'url' => $url,
            'data' => $data
        ]));
        if (empty($url)) {
            throw new Exception('wrong url');
        }

        $ch = curl_init();
        $url .= empty($data) ? '' : '?' . http_build_query($data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        Log::getInstance()->log('get: ' . json_encode([
            'response' => $response
        ]));
        return json_decode($response, true);

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
        Log::getInstance()->log('get: ' . json_encode([
            'url' => $url,
            'data' => $data
        ]));
        if (empty($url)) {
            throw new Exception('wrong url');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        Log::getInstance()->log('get: ' . json_encode([
            'response' => $response
        ]));
        return json_decode($response, true);
    }
}
