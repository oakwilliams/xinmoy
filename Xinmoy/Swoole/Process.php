<?php
/*
 * Process
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   08/03/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Swoole;


use Exception;

use Xinmoy\Lib\Log;


/**
 * Process
 */
trait Process {
    /*
     * Process
     *
     * @property Process
     */
    protected $_process = null;


    /**
     * Set process.
     *
     * @param Process $process process
     */
    public function setProcess($process) {
        if (empty($process)) {
            throw new Exception('process init failed');
        }

        $this->_process = $process;
    }


    /**
     * Get process.
     *
     * @return Process
     */
    public function getProcess() {
        return $this->_process;
    }


    /**
     * Write.
     *
     * @param string $type type
     * @param array  $data optional, data
     */
    public function write($type, $data = null) {
        if (empty($type)) {
            throw new Exception('wrong type');
        }

        if (empty($this->_process)) {
            throw new Exception('process init failed');
        }

        $message = json_encode([
            'type' => $type,
            'data' => $data
        ]);
        $this->_process->write($message);
        Log::getInstance()->log("write: {$message}");
    }


    /**
     * onRead
     */
    public function onRead() {
        try {
            if (empty($this->_process)) {
                throw new Exception('process init failed');
            }

            $message = $this->_process->read();
            Log::getInstance()->log("read: {$message}");
            $message = json_decode($message, true);
            if (empty($message)) {
                throw new Exception('wrong message');
            }

            if (empty($message['type'])) {
                throw new Exception('wrong type');
            }

            $method = "on{$message['type']}";
            if (!method_exists($this, $method)) {
                return;
            }

            if (!isset($message['data'])) {
                $message['data'] = null;
            }

            $this->{$method}($message['data']);
        } catch (Exception $e) {
            handle_exception($e);
        }
    }
}
