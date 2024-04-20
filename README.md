# sidalex/swoole-app

[![Latest Stable Version](http://poser.pugx.org/sidalex/swoole-app/v)](https://packagist.org/packages/sidalex/swoole-app) [![Total Downloads](http://poser.pugx.org/sidalex/swoole-app/downloads)](https://packagist.org/packages/sidalex/swoole-app) [![Latest Unstable Version](http://poser.pugx.org/sidalex/swoole-app/v/unstable)](https://packagist.org/packages/sidalex/swoole-app) [![License](http://poser.pugx.org/sidalex/swoole-app/license)](https://packagist.org/packages/sidalex/swoole-app) [![PHP Version Require](http://poser.pugx.org/sidalex/swoole-app/require/php)](https://packagist.org/packages/sidalex/swoole-app)

## Install

To install, run the following command

```
composer require sidalex/swoole-app
```
для запуска приложения свули необходимо создать скрипт server.php следующего содержания:

```php
<?php
declare(strict_types=1);
require_once "./vendor/autoload.php";
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Constant;
$config = json_decode(file_get_contents('./config.json'));
$http = new Server("0.0.0.0", 9501);
$http->set(
    [
        Constant::OPTION_WORKER_NUM => 2,
        Constant::OPTION_TASK_WORKER_NUM => (swoole_cpu_num()) * 10,
    ]
);

$app = new \Sidalex\SwooleApp\Application($config);
$http->on(
    "start",
    function (Server $http) use ($app) {
        echo "Swoole HTTP server is started.\n";
        $app->initCyclicJobs($http);
    }
);
$http->on(
    "request",
    function (Request $request, Response $response) use ($app,$http) {
        $app->execute($request, $response,$http);
    }
);
$http->on(
    'task',
    function (Server $server, $taskId, $reactorId, $data) use ($app) {
        return $app->taskExecute($server, $taskId, $reactorId, $data);
    }
);
$http->start();
```
переменная $config должна быть \stdClass и может содержать параметры писанные [тут](#config)

Для запуска фоновых процессов, которые должны исполняться периодичски (не от действия пользователя а по планировщику)
реализован интерфейс CyclicJobsInterface более подробное описание его использования [тут](#cyclic-job). Для Автоматического запуска фоновых циклических процессов их необходимо указать в конфиге [тут]().

Для создания Эндпойнов приложения необходимо создать классы [контроллеры](#controller), для каждого эндпойнта необходимо создать свой класс. Либо создать свое правило маршрутизации через [notFoundController](#notfoundcontroller).

Все операции внутри CyclicJobs и controllers должны быть не блокирующими в противном случае вместо прироста производительности вы можете сильно потерять в ней и следующий запрос не будет обработан, пока блокирующая операция не будет выполнена.

Все блокирующие операции необходимо оборачивать в TaskExecutorInterface и выполнять отдельными [Task](#task).
## Config

notFoundController - строка класс с неймспейсом для использования в случае отсутствия роута

controllers - namespace контроллера для который передается приложению.

CyclicJobs - мкассив слассов , которые наследуют интерфейс CyclicJobsInterface которы запускаются при стапрте приложения

## Task

```php

```

## Cyclic Job

```php
class MyCyclicJob implements CyclicJobsInterface
{
    private $application;
    private $server;

    public function __construct(Application $application, Server $server)
    {
        $this->application = $application;
        $this->server = $server;
    }

    public function getTimeSleepSecond(): float
    {
        // Возвращает время задержки в секундах
        return 5.0;
    }

    public function runJob(): void
    {
        $arr = [1,2,3,4,5,6,7,8,9];
        foreach ($arr as $value){
            if($value % 3 == 0)
            {
                echo "example";
            }
        }
    }
}
```


## Controller

```php
пример контроллера с описанием
```

## notFoundController

```php
пример создания своего notFoundController
```