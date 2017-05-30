<?php

namespace Paillechat\ApcuSimpleCache;

use Paillechat\ApcuSimpleCache\Exception\ApcuInvalidCacheKeyException;
use Psr\SimpleCache\CacheInterface;

class ApcuCache implements CacheInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $this->assertKeyName($key);

        return apcu_fetch($key) ?:$default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $this->assertKeyName($key);

        return apcu_store($key, $value, (int) $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $this->assertKeyName($key);

        return apcu_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return apcu_clear_cache();
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $this->assertKeyNames($keys);

        $result = apcu_fetch($keys);

        if (!is_null($default) && is_array($result) && count($keys) > count($result)) {
            $notFoundKeys = array_diff($keys, array_keys($result));
            $result = array_merge($result, array_fill_keys($notFoundKeys, $default));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $this->assertKeyNames(array_keys($values));

        $result = apcu_store($values, (int) $ttl);

        return $result === true ? true : (is_array($result) && count($result) == 0 ? true: false);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        $this->assertKeyNames($keys);

        $result = apcu_delete($keys);

        return count($result) === count($keys) ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $this->assertKeyName($key);

        return apcu_exists($key);
    }

    private function assertKeyName($key)
    {
        if (!is_string($key)) {
            throw new ApcuInvalidCacheKeyException();
        }
    }

    private function assertKeyNames(array $keys)
    {
        array_map(function ($value) {
            $this->assertKeyName($value);
        }, $keys);
    }
}
