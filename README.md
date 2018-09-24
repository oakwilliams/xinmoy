# Xinmoy
Microservice Framework Based on Swoole
## Features
* **Call remotely as if on local.**
* It supports MySQL master/slave architecture.
* It supports Redis master/slave architecture.
* It supports CURL.
## Xinmoy App
![](https://github.com/oakwilliams/xinmoy/wiki/Xinmoy%20App.jpg)
## DemoConfig
```
<?php
namespace Demo\Config;


use Xinmoy\Base\BaseConfig;


class DemoConfig extends BaseConfig {
    const TEST = 'config test';
}
```
## DemoError
```
<?php
namespace Demo\Error;


use Xinmoy\Base\BaseError;

use Demo\Lang\ErrorLang;


class DemoError extends BaseError {
    const TEST = [ ErrorLang::DEMO_TEST, 2 ];
}
```
## ErrorLang
```
<?php
namespace Demo\Lang;


use Xinmoy\Base\BaseLang;


class ErrorLang extends BaseLang {
    const DEMO_TEST = 'error test';
}
```
## DemoLang
```
<?php
namespace Demo\Lang;


use Xinmoy\Base\BaseLang;


class DemoLang extends BaseLang {
    const TEST = 'lang test';
}
```
## DemoService
```
<?php
namespace Demo\Service;


use Exception;

use Xinmoy\Base\BaseService;


class DemoService extends BaseService {
    public function test($hello, $world) {
        if (empty($hello) || empty($world)) {
            throw new Exception('wrong hello/world', 2);
        }

        return "{$hello}, {$world}!";
    }


    public static function testStatic($hello, $world) {
        $demo_service = new static();
        return $demo_service->test($hello, $world);
    }
}
```
## DemoController
```
<?php
namespace Demo\Controller;


use Exception;

use Xinmoy\Base\BaseController;

use Demo\Config\DemoConfig
use Demo\Error\DemoError;
use Demo\Lang\DemoLang;
use Demo\Service\DemoService;


class DemoController extends BaseController {
    public function testConfig($data) {
        return [
            'demo' => DemoConfig::TEST()
        ];
    }


    public function testError($data) {
        throw new Exception(...DemoError::TEST());
    }


    public function testLang($data) {
        return [
            'demo' => DemoLang::TEST()
        ];
    }


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


    public function testStatic($data) {
        if (empty($data['hello']) || empty($data['world'])) {
            throw new Exception('wrong hello/world', 2);
        }

        $greeting = DemoService::testStatic($data['hello'], $data['world']);
        return [
            'greeting' => $greeting
        ];
    }
}
```
## Test
```
http://172.17.0.4/Demo/Demo/test
http://172.17.0.4/Demo/Demo/testStatic
```
## Documentation
For more information, please visit [Wiki](https://github.com/oakwilliams/xinmoy/wiki).
