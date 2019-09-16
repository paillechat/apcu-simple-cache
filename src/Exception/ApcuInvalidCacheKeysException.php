<?php

namespace Paillechat\ApcuSimpleCache\Exception;

use Psr\SimpleCache\InvalidArgumentException;

class ApcuInvalidCacheKeysException extends \Exception implements InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('Cache keys neither an array nor a Traversable!');
    }
}
