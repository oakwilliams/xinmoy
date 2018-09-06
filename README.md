# Xinmoy
Microservice Framework Based on Swoole

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
