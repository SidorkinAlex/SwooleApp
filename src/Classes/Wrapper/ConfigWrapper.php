<?php

namespace Sidalex\SwooleApp\Classes\Wrapper;

class ConfigWrapper
{
    private \stdClass $configRepository;

    /**
     * @param \stdClass $configRepository
     */
    public function __construct(\stdClass $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    public function getConfigFromKey(string $key): mixed
    {
        if (isset($this->configRepository->{$key})) {
            return $this->configRepository->{$key};
        }
        return null;
    }

    /**
     * @param array<string> $properties ['host', 'port']
     * @return array<mixed> ['host' => 'hostValue', 'port' => 'portValue']
     */
    public function getConfigFromArray(array $properties): array
    {
        $configList = [];
        foreach ($properties as $configKey) {
            if (isset($this->configRepository->{$configKey})) {
                $configList[$configKey] = $this->configRepository->{$configKey};
            } else {
                $configList[$configKey] = null;
            }
        }
        return $configList;
    }


}