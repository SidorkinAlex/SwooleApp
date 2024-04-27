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

для запуска приложения необходимо создать stdClass с набором свойств(далее описаны более поробно)

целевым использованиие считается инициация конфигурационных данных из файла json смоттри пример server.php
```php
$config = json_decode(file_get_contents('./config.json'));
```

### Список параметров конфига

```php
$config = new stdClass();
$config->notFoundController = 'appNameSpaceMyApp\MyNotFoundController';
$config->controllers = [
    'appNameSpaseMyApp\MyFirstControllerNamespace',
    'appNameSpaseMyApp\MySecondControllerNamespace',
    'appNameSpaseMyApp\MyThreeControllerNamespace',
];
$config->CyclicJobs =[
    'appNameSpaseMyApp\MyFirstCyclicJobsClass',
    'appNameSpaseMyApp\MySecondCyclicJobsClass',
    'appNameSpaseMyApp\MyThreeCyclicJobsClass',
];
```

notFoundController - строка класс с классом , который обрабатывает роуты не найденные по стандартному флоу, данный клас должен имплементировать Sidalex\SwooleApp\Classes\Controllers\ControllerInterface 

controllers - массив namespace в которых рекурсивно удет осуществляться поиск классов Контроллеров(имплементирующих интерфейс Sidalex\SwooleApp\Classes\Controllers\ControllerInterface) так же для реализации класса контроллера можно испрользовать наследование AbstractController более подробно [тут](#controller).

CyclicJobs - мкассив слассов , которые имплементируют интерфейс CyclicJobsInterface которы запускаются при стапрте приложения и выполняются циклично раз в определенный интервал времени подробнее [тут](#cyclic-job)

## Task

Задачи это процессы , которые будут выполнены вне асинхронного процесса исполнения и могут быть вызваны в любой части приложения.

для урощения работы с задачами и универсализации вывода в фреймворке добавлена сандартизация данного процесса для его использования при старте swoole server необходимо инициировать блок

Если данный блок не Инициировать то фреймворк с классом BasicTaskData и интерфейсом TaskDataInterface работать не будет.
```php
$http->on(
    'task',
    function (Server $server, $taskId, $reactorId, $data) use ($app) {
        return $app->taskExecute($server, $taskId, $reactorId, $data);
    }
);
```

Только в данных процессах может содержаться блокирующие операции.

Методы:
```php
Swoole\Server->task(Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface $data, int $dstWorkerId = -1, callable $finishCallback = null)
```
$data - класс имплементирующий Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface по умолчанию фреймворк редлагает использовать класс BasicTaskData

$dstWorkerId - Идентификационный номер рабочего процесса. Если этот параметр не был передан, сервер swoole выберет для вас случайный и незанятый рабочий процесс.

$finishCallback -  солбэк который будет выполнен перед завершением Task
Ниже пример колбэка
```php
 function (OpenSwoole\Server $server, $task_id, $data)
    {
        echo "Task Callback: ";
        var_dump($task_id, $data);
    });

```
## BasicTaskData

```php
#Sidalex\SwooleApp\Classes\Tasks\Data\BasicTaskData
$taskData = new BasicTaskData('Sidalex\TestSwoole\Tasks\TestTaskExecutor', ['test' => 'test1']);
```
В котором в конструкторе передается 2 параметра

1 параметр ('Sidalex\TestSwoole\Tasks\TestTaskExecutor') - это название класса, который будет создан в в задаче для исполнения долже имплементировать интерфейс TaskExecutorInterface



```php
$taskData = new BasicTaskData('Sidalex\TestSwoole\Tasks\TestTaskExecutor', ['test' => 'test1']);
        /**
         * @var $taskResult TaskResulted
         */
        $taskResult =  $this->server->taskwait($taskData);
        var_export($taskResult->getResult());
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