<?php

namespace Sidalex\SwooleApp\Classes\Tasks\Data;

interface TaskDataInterface
{
    /** returns the name of the class that will be created and called to run it should implement the TaskExecutorInterface interface
     *
     * @return string
     */
    public function getTaskClassName(): string;

    /**
     * @return array<mixed> in this array external params from the task
     */
    public function getStorage(): array;

}