<?php

namespace Paillechat\ApcuSimpleCache\Exception;

use Psr\SimpleCache\InvalidArgumentException;

class ApcuInvalidCacheKeyException extends \Exception implements InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('Invalid cache key, only strings allowed!');
    }
}
