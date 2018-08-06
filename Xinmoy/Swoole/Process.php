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
    public function write($type, $data = []) {
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
}
