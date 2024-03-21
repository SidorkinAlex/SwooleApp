<?php

namespace Sidalex\SwooleApp\Classes\Controllers;

use Sidalex\SwooleApp\Application;
use Swoole\Http\Server;

class NotFoundController implements ControllerInterface
{

    private \Swoole\Http\Request $request;
    private \Swoole\Http\Response $response;
    /**
     * @var array|string[]
     */
    private array $uri_params;
    private Application $application;
    private Server $server;
    /**
     * @var array|string[]
     */
    private array $errors_message;

    public function __construct(\Swoole\Http\Request $request, \Swoole\Http\Response $response, array $uri_params=[])
    {
        $this->request = $request;
        $this->response = $response;
        $this->errors_message =$uri_params;
    }

    public function execute(): \Swoole\Http\Response
    {
        $errorBuilder = new ErrorResponseBuilder($this->response);
        return $errorBuilder->errorResponse($this->errors_message['message']);
    }

    public function setApplication(Application $application, Server $server)
    {
        $this->application = $application;
        $this->server = $server;
    }
}