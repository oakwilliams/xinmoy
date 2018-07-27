<?php
/*
 * Start
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   05/05/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


require_once __DIR__ . '/../vendor/autoload.php';


use Xinmoy\Lib\Log;
use Xinmoy\Register\Register;
use Xinmoy\Server\Server;


/**
 * Autoload service.
 *
 * @param string $class class
 */
function autoload_service($class) {
    $class = explode('\\', $class);
    $name = array_pop($class);
    $namespace = join('\\', $class);
    if (!preg_match('/^.+Service$/', $name)) {
        return false;
    }

    $code = "
        namespace {$namespace};


        use Xinmoy\\Client\\Service;


        class {$name} extends Service { }
    ";
    eval($code);
    return true;
}


/**
 * Handle exception.
 *
 * @param Exception $e exception
 */
function handle_exception($e) {
    $message = $e->getMessage();
    Log::getInstance()->log($message);
}


/**
 * Start register.
 *
 * @param array $config config
 */
function start_register($config) {
    if (empty($config)) {
        throw new Exception('wrong config');
    }

    if (empty($config['register']['host']) || ($config['register']['port'] < 0)) {
        throw new Exception('wrong host/port');
    }

    $register = new Register($config['register']['host'], $config['register']['port']);
    $register->start();
}


/**
 * Start server.
 *
 * @param array $config config
 */
function start_server($config) {
    if (empty($config)) {
        throw new Exception('wrong config');
    }

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
}


// Register service autoload.
spl_autoload_register('autoload_service');

// Set exception handler.
set_exception_handler('handle_exception');

// Read config.
$config = file_get_contents(__DIR__ . '/../config.json');
$config = json_decode($config, true);
if (empty($config)) {
    throw new Exception('wrong config');
}

if (empty($config['role'])) {
    throw new Exception('wrong role');
}

$function = "start_{$config['role']}";
if (!function_exists($function)) {
    throw new Exception('wrong role');
}

// Start.
$function($config);
