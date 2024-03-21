<?php

namespace Sidalex\SwooleApp\Classes\Controllers;

use Attribute;

#[Attribute]
class Route
{
    private string $uri;
    private string $method;

    /**
     * @param string $uri
     * @param string $method
     */
    public function __construct(string $uri, string $method)
    {
        $this->uri = $uri;
        $this->method = $method;
    }


}