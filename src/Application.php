<?php

namespace Sidalex\SwooleApp;

use Sidalex\CandidateVacancyEstimationGpt\Classes\Validators\ConfigValidatorInterface;

use Sidalex\SwooleApp\Classes\Builder\NotFoundControllerBuilder;
use Sidalex\SwooleApp\Classes\Builder\RoutesCollectionBuilder;
use Sidalex\SwooleApp\Classes\Tasks\Data\TaskDataInterface;
use Sidalex\SwooleApp\Classes\Tasks\TaskResulted;
use Sidalex\SwooleApp\Classes\Utils\Utilities;
use Sidalex\SwooleApp\Classes\Wrapper\ConfigWrapper;
use Swoole\Server;


class Application
{
    private ConfigWrapper $config;
    private array $routesCollection;

    public function __construct(\stdClass $configPath, array $ConfigValidationList = [])
    {
        try {
            foreach ($ConfigValidationList as $configValidationClassName) {
                $validationClass = new $configValidationClassName;
                if ($validationClass instanceof ConfigValidatorInterface) {
                    $validationClass->validate($configPath);
                }
            }
            $this->config = new ConfigWrapper($configPath);
            $Route_builder = new RoutesCollectionBuilder();
            $this->routesCollection = $Route_builder->buildRoutesCollection($this->config);
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit(1);
        }
    }


    public function execute(\Swoole\Http\Request $request, \Swoole\Http\Response $response, Server $server)
    {
        $Route_builder = new RoutesCollectionBuilder();
        $itemRouteCollection = $Route_builder->searchInRoute($request, $this->routesCollection);
        if (empty($itemRouteCollection)) {
            $controller = (new NotFoundControllerBuilder($request, $response, $this->config))->build();
        } else {
            $controller = $Route_builder->getController($itemRouteCollection, $request, $response);
        }
        $controller->setApplication($this, $server);
        $response = $controller->execute();
        unset($controller);
    }

    public function getConfig(): ConfigWrapper
    {
        return $this->config;
    }

    public function taskExecute(\Swoole\Http\Server $server, $taskId, $reactorId, $data): TaskResulted
    {
        if (!($data instanceof TaskDataInterface)) {
            return new TaskResulted('error data is not a TaskDataInterface', false);
        }
        $TaskExecutorClassName = $data->getTaskClassName();
        if (Utilities::classImplementInterface($TaskExecutorClassName, 'Sidalex\SwooleApp\Classes\Tasks\Executors\TaskExecutorInterface')) {
            $TaskExecutorClass = new $TaskExecutorClassName($server, $taskId, $reactorId, $data);
            $result = $TaskExecutorClass->execute();
        } else {
            return new TaskResulted('error task Executor not implemented TaskExecutorInterface', false);
        }
        if (!($result instanceof TaskResulted)) {
            return new TaskResulted('error result is not a TaskResulted', false);
        }
        return $result;
    }

    public function initCyclicJobs(): void
    {
        //todo: create CyclicJobsInterface
        //todo: create CyclicJobsBuilder From config
        //todo: run CyclicJob Objects
    }

}