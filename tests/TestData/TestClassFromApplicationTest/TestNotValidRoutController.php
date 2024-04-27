<?php

use Sidalex\SwooleApp\Classes\Controllers\AbstractController;
use Sidalex\SwooleApp\Classes\Controllers\Route;

#[Route(uri: 'nonslash/api/v2/{test_name}/v5', method: 'POST')]
class TestNotValidRoutController extends AbstractController
{

    public function execute(): \Swoole\Http\Response
    {
        return $this->response;
    }
}