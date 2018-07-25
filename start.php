<?php
/*
 * Start
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   05/05/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


require_once 'vendor/autoload.php';


use Xinmoy\Lib\Log;
use Xinmoy\Register\Register;
use Xinmoy\Server\Server;


/**
 * Handle exception.
 *
 * @param Exception $e exception
 */
function handle_exception($e) {
    $message = $e->getMessage();
    Log::getInstance()->log($message);
}


// Set exception handler.
set_exception_handler('handle_exception');

// Read config.
$config = file_get_contents('config.json');
$config = json_decode($config, true);
if (empty($config)) {
    throw new Exception('wrong config');
}

// Start.
if (empty($config['role'])) {
    throw new Exception('wrong role');
}

switch ($config['role']) {
    // Start register.
    case 'register':
    if (empty($config['register']['host']) || ($config['register']['port'] < 0)) {
        throw new Exception('wrong host/port');
    }

    $register = new Register($config['register']['host'], $config['register']['port']);
    $register->start();
    break;

    // Start server.
    case 'server':
        if (empty($config['server']['host']) || ($config['server']['port'] < 0)) {
            throw new Exception('wrong server host/port');
        }

        if (empty($config['server']['name'])) {
            throw new Exception('wrong name');
        }

        if (empty($config['register']['host']) || ($config['register']['port'] < 0)) {
            throw new Exception('wrong register host/port');
        }

        $server = new Server($config['server']['host'], $config['server']['port']);
        $server->setName($config['server']['name']);
        $server->setRegisterAddress($config['register']['host'], $config['register']['port']);
        $server->start();
        break;

    default:
        break;
}
