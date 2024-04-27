<?php

namespace Sidalex\SwooleApp\Classes\Validators;

use _PHPStan_7961f7ae1\Nette\Neon\Exception;

class ValidatorUriArr
{

    /**
     * @throws \Exception
     */
    public function validate(array $url_arr): array
    {
        if($url_arr[0] != ''){
            throw new \Exception('uri in Route is not valid. uri must started from "/" symbol',1);
    }
        return $url_arr;
    }
}