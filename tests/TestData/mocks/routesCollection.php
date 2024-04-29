<?php
return [
    [
        'route_pattern_list' => ['','api', 'v1', 'test1',],
        'parameters_fromURI' => [],
        'method' => 'POST',
        'ControllerClass' => 'TestController1',
    ],
    [
        'route_pattern_list' => ['','api', 'v2', 'collections',"*",'get'],
        'parameters_fromURI' => [ 4 => 'collectionName'],
        'method' => 'POST',
        'ControllerClass' => 'TestController2',
    ],
    [
        'route_pattern_list' => ['','api', 'v3', 'collections',"*",'get'],
        'parameters_fromURI' => [ 4 => 'collectionName'],
        'method' => 'POST',
        'ControllerClass' => 'TestController3',
    ],
    [
        'route_pattern_list' => ['','api', 'v3', 'collectionsList','Accounts','statuses'],
        'parameters_fromURI' => [],
        'method' => 'GET',
        'ControllerClass' => 'TestController6',
    ],
    [
        'route_pattern_list' => ['','api', 'v3', 'collectionsList','Accounts'],
        'parameters_fromURI' => [],
        'method' => 'GET',
        'ControllerClass' => 'TestController5',
    ],
    [
        'route_pattern_list' => ['','api', 'v3', 'collectionsList',],
        'parameters_fromURI' => [],
        'method' => 'GET',
        'ControllerClass' => 'TestController4',
    ],

];