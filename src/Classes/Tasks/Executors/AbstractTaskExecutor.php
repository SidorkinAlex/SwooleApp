<?php

namespace Sidalex\SwooleApp\Classes\Tasks\Executors;

use Sidalex\SwooleApp\Application;
use Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface;
use Sidalex\SwooleApp\Classes\Tasks\TaskResulted;

abstract class AbstractTaskExecutor implements TaskExecutorInterface
{
    protected \Swoole\Http\Server $server;
    protected $taskId;
    protected $reactorId;
    /**
     * @var mixed[]
     */
    protected array $dataStorage;
    protected Application $app;


    public function __construct(\Swoole\Http\Server $server, $taskId, $reactorId, TaskDataInterface $data, Application $app)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->reactorId = $reactorId;
        $this->dataStorage = $data->getStorage();
        $this->app = $app;
    }

    abstract public function execute(): TaskResulted;
}