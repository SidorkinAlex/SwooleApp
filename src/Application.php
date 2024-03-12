<?php

namespace Sidalex\SwooleApp;

use Sidalex\CandidateVacancyEstimationGpt\Classes\Validators\ConfigValidatorInterface;

use Sidalex\SwooleApp\Classes\Builder\RoutesCollectionBuilder;
use Sidalex\SwooleApp\Classes\Wrapper\ConfigWrapper;
use Swoole\Server;


class Application
{
    private ConfigWrapper $config;
    private array $routesCollection;

    public function __construct(\stdClass $configPath, array $ConfigValidationList=[])
    {
        try {
            foreach ($ConfigValidationList as $configValidationClassName){
                $validationClass = new $configValidationClassName;
                if ($validationClass instanceof ConfigValidatorInterface){
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
            $controller = new NotFoundController($request, $response);
        } else {
            $controller = $Route_builder->getController($itemRouteCollection, $request, $response);
        }
        $controller->setApplication($this, $server);
        $response = $controller->execute();
        unset($controller);
    }

    public function getConfig(): \stdClass
    {
        return $this->config;
    }


}