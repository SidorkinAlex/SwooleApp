<?php

namespace Sidalex\SwooleApp\Classes\Utils;

class Utilities
{
    public static function classImplementInterface($className,$interfaceName):bool {
        $interfaceCollection = class_implements($className);
        if (in_array($interfaceName,$interfaceCollection)) {
            return true;
        } else {
            return false;
        }
    }

}