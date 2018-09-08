# Xinmoy
Microservice Framework Based on Swoole
## Features
* **Call remotely as if on local.**
* It supports MySQL master/slave architecture.
* It supports Redis master/slave architecture.
## Xinmoy App
![](https://github.com/oakwilliams/xinmoy/wiki/Xinmoy%20App.jpg)
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
}
```
## DemoController
```
<?php
namespace Demo\Controller;


use Exception;

use Xinmoy\Base\BaseController;

use Demo\Service\DemoService;


class DemoController extends BaseController {
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
```
## Test
```
http://172.17.0.4/Demo/Demo/test
```
## Documentation
For more information, please visit [Wiki](https://github.com/oakwilliams/xinmoy/wiki).
