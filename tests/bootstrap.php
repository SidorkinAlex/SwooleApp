<?php
require_once './vendor/autoload.php';
require_once './tests/TestData/TestClassFromApplicationTest/TestController.php';
require_once './tests/TestData/TestClassFromApplicationTest/TestController2.php';
require_once './tests/TestData/TestClassFromApplicationTest/TestNotValidRoutController.php';
if(!class_exists('\Swoole\Http\Request'))
{
    require_once './tests/TestData/Swoole/Request.php';
}
