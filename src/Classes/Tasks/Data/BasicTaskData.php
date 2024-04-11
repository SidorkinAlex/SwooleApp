<?php

namespace Sidalex\SwooleApp\Classes\Tasks\Data;

class BasicTaskData implements TaskDataInterface
{
    protected string $className='';
    protected array $storage = [];

    /**
     * @param string $className
     * @param array $storage
     */
    public function __construct(string $className='', array $storage = [])
    {
        $this->className = $className;
        $this->storage = $storage;
    }


    public function getTaskClassName(): string
    {
        return $this->className;
    }

    public function getStorage(): array
    {
        return $this->storage;
    }
}