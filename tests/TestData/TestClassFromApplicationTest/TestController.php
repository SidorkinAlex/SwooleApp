<?php

use Sidalex\SwooleApp\Classes\Controllers\AbstractController;
use Sidalex\SwooleApp\Classes\Controllers\Route;

#[Route(uri: '/api/v100500/test1', method: 'POST')]
class TestController extends AbstractController
{

    public function execute(): \Swoole\Http\Response
    {
        return $this->response;
    }
}