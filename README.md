# sidalex/swoole-app

[![Latest Stable Version](http://poser.pugx.org/sidalex/swoole-app/v)](https://packagist.org/packages/sidalex/swoole-app) [![Total Downloads](http://poser.pugx.org/sidalex/swoole-app/downloads)](https://packagist.org/packages/sidalex/swoole-app) [![Latest Unstable Version](http://poser.pugx.org/sidalex/swoole-app/v/unstable)](https://packagist.org/packages/sidalex/swoole-app) [![License](http://poser.pugx.org/sidalex/swoole-app/license)](https://packagist.org/packages/sidalex/swoole-app) [![PHP Version Require](http://poser.pugx.org/sidalex/swoole-app/require/php)](https://packagist.org/packages/sidalex/swoole-app)

[en] | [ru](#sidalexswoole-app-фреймворк-для-работы-со-swoole)


# sidalex/swoole-app фреймворк для работы со swoole
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
Переменная $config должна быть \stdClass и может содержать параметры писанные [тут](#config)

Для запуска фоновых процессов, которые должны исполняться периодичски (не от действия пользователя а по планировщику)
реализован интерфейс CyclicJobsInterface более подробное описание его использования [тут](#cyclic-job). Для Автоматического запуска фоновых циклических процессов их необходимо указать в конфиге [тут]().

Для создания Эндпойнов приложения необходимо создать классы [контроллеры](#controller), для каждого эндпойнта необходимо создать свой класс. Либо создать свое правило маршрутизации через [notFoundController](#notfoundcontroller).

Все операции внутри CyclicJobs и controllers должны быть не блокирующими в противном случае вместо прироста производительности вы можете сильно потерять в ней и следующий запрос не будет обработан, пока блокирующая операция не будет выполнена.

Все блокирующие операции необходимо оборачивать в TaskExecutorInterface и выполнять отдельными [Task](#task).
## Config

Для запуска приложения необходимо создать stdClass с набором свойств(далее описаны более поробно)

Целевым использованиие считается инициация конфигурационных данных из файла json смоттри пример server.php
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

для запуска Task необходимо сосздать объект класса BasicTaskData или создать собственный имплементирующий интерфейс TaskDataInterface
более подробно [тут](#basictaskdata)

Методы:
task - запуск задачи без ожидание ее завершения

taskwait - запуск ззадачи с ожитанием завершения и получения результата. ожидание результата происходит неблокирующим образом. задачи можно запускать из контоллера.

```php
Swoole\Server->task(Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface $data, int $dstWorkerId = -1, callable $finishCallback = null)
```
$data - класс имплементирующий Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface по умолчанию фреймворк редлагает использовать класс BasicTaskData подробнее [тут](#basictaskdata)

$dstWorkerId - Идентификационный номер рабочего процесса. Если этот параметр не был передан, сервер swoole выберет для вас случайный и незанятый рабочий процесс.

$finishCallback -  солбэк который будет выполнен перед завершением Task не обязательный параметр

```php
$result = Swoole\Server->taskwait((Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface $data, float $timeout = 0.5, int $dstWorkerId = -1) :TaskResulted
```
$data - класс имплементирующий Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface по умолчанию фреймворк редлагает использовать класс BasicTaskData подробнее [тут](#basictaskdata)

$timeout - время ожидания завершения задачи в секундах, эта функция не будет возвращаться до тех пор, пока задача не будет завершена, или если истечет время ожидания, то по истечении этого времени будет возвращено значение false. Минимальное значение - 1 мс.

$dstWorkerId - Идентификационный номер рабочего процесса. Если этот параметр не был передан, сервер swoole выберет для вас случайный и незанятый рабочий процесс.

$result - результат выполнения Task должен являться TaskResulted подробнее [тут](#taskresulted)

## BasicTaskData

```php
#Sidalex\SwooleApp\Classes\Tasks\Data\BasicTaskData
$taskData = new BasicTaskData('Sidalex\TestSwoole\Tasks\TestTaskExecutor', ['test' => 'test1']);
```
В котором в конструкторе передается 2 параметра

1 параметр ('Sidalex\TestSwoole\Tasks\TestTaskExecutor') - это название класса, который будет создан в задаче для исполнения. Он должен имплементировать интерфейс TaskExecutorInterface

2 параметр ( ['test' => 'test1'] ) - это массив с данными контекста, который необходим для исполнения логики содержащейся в классе задачи ('Sidalex\TestSwoole\Tasks\TestTaskExecutor' для примера)

Пример применения например в контроллере или в Cyclic Job:

```php
$taskData = new BasicTaskData('Sidalex\TestSwoole\Tasks\TestTaskExecutor', ['test' => 'test1']);
        /**
         * @var $taskResult TaskResulted
         */
        $taskResult =  $this->server->taskwait($taskData);
        var_export($taskResult->getResult());
```
## TaskResulted

Описание: Класс TaskResulted представляет собой результат выполнения задачи. Он содержит информацию о успешном выполнении задачи и её результате.Copy

Свойства:

$success - приватное свойство, содержащее информацию о успешном выполнении задачи.
$result - приватное свойство, содержащее результат выполнения задачи.


__construct(mixed $inData, bool $success = true) - конструктор класса, принимающий входные данные и информацию о успешном выполнении задачи.
getResult(): mixed - метод, возвращающий результат выполнения задачи. Может выбрасывать исключение типа TaskException.Copy
isSuccess(): bool - метод, возвращающий информацию о успешном выполнении задачи.

Пример:
```php
$taskData = new BasicTaskData('Sidalex\TestSwoole\Tasks\TestTaskExecutor', ['test' => 'test1']);
        /**
         * @var $taskResult TaskResulted
         */
        $taskResult =  $this->server->taskwait($taskData);
        var_export($taskResult->getResult());
```

## Cyclic Job

Это код, который требует периодического исполнения. С помощью этого механизма можно с определенной периодичностью запускать скрипты. Замена cron.

Для инициализации Cyclic Job необходимо объявить в конфигурационном файле параметр CyclicJobs

Пример инициализации config через stdClass :

```php
$config = new stdClass();
$config->CyclicJobs =[
"Sidalex\TestSwoole\CyclicJobs\TestCyclicJobs",
];
```
Пример инициализации config через json файл:

```json
{
  "CyclicJobs": [
    "Sidalex\\TestSwoole\\CyclicJobs\\TestCyclicJobs"
  ]
}
```

Пример класса для Cyclic Job
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

Любой класс указанный в конфиге должен имплементировать интерфейс CyclicJobsInterface

getTimeSleepSecond - возвращает время в секундах периодичность с которой будет запускаться метод runJob

runJob - метод, который содержит в себе полезную нагрузку в разрезе бизнес логики. Это код содержащий основную бизнес логику которая должна выполняться циклически.

## Controller
Для создания новых роутов используется класс Контроллер.  Для добавления нового роута необходимо создать класс имплементирующий интерфейс ControllerInterface и добавить в файл конфигурации неймспейс в котором содержится этот класс в конфиг с ключом controllers подробнее [тут](#controller)

Так же в фреймворке есть специальный абстрактный класс AbstractController который может упростить создание класса Контроллера.

Для того что бы создать Роут у класса контроллера необходимо указать Атрибут следующего вида:


```php
#[\Sidalex\SwooleApp\Classes\Controllers\Route(uri: '/api/{v1}/get_resume',method:"POST")]
class TestController extends AbstractController
{
```

или

```php
use Sidalex\SwooleApp\Classes\Controllers\Route;

#[Route(uri: '/api/{v1}/get_resume',method:"POST")]
class TestController extends AbstractController
{
```

uri - это параметр, который определяет лоя какого роута будет использован данный контроллер

Критически важно что бы атрибут был указан первым у данного класса.



## notFoundController

```php
пример создания своего notFoundController
```