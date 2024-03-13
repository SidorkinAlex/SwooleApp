<?php

namespace Sidalex\CandidateVacancyEstimationGpt\Classes\Validators;

interface ConfigValidatorInterface
{
    /**
     * @param \stdClass $config
     * @throws \Exception
     */
    public function validate(\stdClass $config): void;
}