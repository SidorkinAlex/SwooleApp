<?php

namespace Sidalex\SwooleApp\Classes\Controllers;

use Sidalex\SwooleApp\Application;
use Swoole\Http\Server;

class NotFoundController implements ControllerInterface
{

    private \Swoole\Http\Request $request;
    private \Swoole\Http\Response $responce;
    /**
     * @var array|string[]
     */
    private array $uri_params;

    public function __construct(\Swoole\Http\Request $request, \Swoole\Http\Response $response, array $uri_params=[])
    {
        $this->request = $request;
        $this->responce = $response;
        $this->uri_params = $uri_params;
    }

    public function execute(): \Swoole\Http\Response
    {
        $this->responce->setStatusCode(404);
        $this->responce->setHeader('Content-Type', 'application/json');
        $this->responce->end(json_encode(
            [
                'codeStatus' => '404',
                'text' => 'Page not found'
            ]
        ));
        return $this->responce;
    }

    public function setApplication(Application $application, Server $server)
    {

    }
}