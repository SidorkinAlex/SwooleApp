<?php

namespace Sidalex\SwooleApp\Classes\Wrapper;

class StateContainerWrapper
{
    private \stdClass $stateRepository;

    /**
     * @param \stdClass $stateRepository
     */
    public function __construct(\stdClass $stateRepository)
    {
        $this->stateRepository = $stateRepository;
    }


    public function getContainer(string $key): mixed
    {
        if (isset($this->stateRepository->{$key})){
            return $this->stateRepository->{$key};
        }
        return null;
    }


}