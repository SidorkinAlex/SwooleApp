<?php

namespace Sidalex\SwooleApp\Classes\Initiation;

abstract class AbstractContainerInitiator implements StateContainerInitiationInterface
{

    protected string $key;
    protected mixed $result;

    abstract public function init(\Sidalex\SwooleApp\Application $param): void;


    public function getKey(): string
    {
        return $this->key;
    }

    public function getResultInitiation(): mixed
    {
        return $this->result;
    }
}