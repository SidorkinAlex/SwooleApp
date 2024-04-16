<?php

namespace Sidalex\SwooleApp\Classes\CyclicJobs;

use Sidalex\SwooleApp\Application;
use Sidalex\SwooleApp\Classes\Utils\Utilities;
use Sidalex\SwooleApp\Classes\Wrapper\ConfigWrapper;

class CyclicJobsBuilder
{
    private array $listClassName = [];

    public function __construct(ConfigWrapper $configWrapper)
    {
        $listClassName = $configWrapper->getConfigFromKey('CyclicJobs');
        if (!is_null($listClassName) && is_array($listClassName)) {
            $this->initListClassName($listClassName);
        } elseif (!is_array($listClassName)) {
            //todo : fatal log write information
        }
    }

    private function initListClassName(array $listClassName): void
    {
        foreach ($listClassName as $className) {
            if (
                Utilities::classImplementInterface(
                    $className,
                    "Sidalex\SwooleApp\Classes\CyclicJobs\CyclicJobsInterface"
                )
            ) {
                $this->listClassName[] = $className;
            } else {
                //todo: add log fatal information
            }
        }
    }

    /**
     * @param Application $application
     * @return array<CyclicJobsInterface>
     */
    public function buildCyclicJobs(Application $application): array
    {
        $cyclicJobs = [];
        foreach ($this->listClassName as $className) {
            $cyclicJobs[] = new $className($application);
        }
        return $cyclicJobs;
    }
}