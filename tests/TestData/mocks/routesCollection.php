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
];