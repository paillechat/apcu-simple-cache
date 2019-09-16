<?php

namespace Paillechat\ApcuSimpleCache;

use Paillechat\ApcuSimpleCache\Exception\ApcuInvalidCacheKeyException;
use Paillechat\ApcuSimpleCache\Exception\ApcuInvalidCacheKeysException;
use Psr\SimpleCache\CacheInterface;

class ApcuCache implements CacheInterface
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var int
     */
    private $defaultLifetime;

    public function __construct($namespace = '', $defaultLifetime = 0)
    {
        $this->namespace = $namespace;
        $this->defaultLifetime = $defaultLifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $this->assertKeyName($key);
        $key = $this->buildKeyName($key);

        $value = apcu_fetch($key, $success);
        if ($success === false) {
            return $default;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $this->assertKeyName($key);
        $key = $this->buildKeyName($key);

        $ttl = is_null($ttl) ? $this->defaultLifetime : $ttl;

        return apcu_store($key, $value, (int)$ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $this->assertKeyName($key);
        $key = $this->buildKeyName($key);

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
        $keys = $this->convertKeysToArray($keys);
        $this->assertKeyNames($keys);
        $keys = $this->buildKeyNames($keys);

        $result = apcu_fetch($keys);

        if (!is_null($default) && is_array($result) && count($keys) > count($result)) {
            $notFoundKeys = array_diff($keys, array_keys($result));
            $result = array_merge($result, array_fill_keys($notFoundKeys, $default));
        }

        $mappedResult = [];

        foreach ($result as $key => $value) {
            $key = preg_replace("/^$this->namespace/", '', $key);

            $mappedResult[$key] = $value;
        }

        return $mappedResult;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $values = $this->convertKeysToArray($values);
        $this->assertKeyNames(array_keys($values));

        $mappedByNamespaceValues = [];

        foreach ($values as $key => $value) {
            $mappedByNamespaceValues[$this->buildKeyName($key)] = $value;
        }

        $ttl = is_null($ttl) ? $this->defaultLifetime : $ttl;

        $result = apcu_store($mappedByNamespaceValues, (int)$ttl);

        return $result === true ? true : (is_array($result) && count($result) == 0 ? true : false);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        $keys = $this->convertKeysToArray($keys);
        $this->assertKeyNames($keys);
        $keys = $this->buildKeyNames($keys);

        $result = apcu_delete($keys);

        return count($result) === count($keys) ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $this->assertKeyName($key);
        $key = $this->buildKeyName($key);

        return (bool)apcu_exists($key);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function buildKeyName($key)
    {
        return $this->namespace . $key;
    }

    /**
     * @param string[] $keys
     *
     * @return string[]
     *
     * @throws ApcuInvalidCacheKeysException
     */
    private function buildKeyNames(array $keys)
    {
        return array_map(function ($key) {
            return $this->buildKeyName($key);
        }, $keys);
    }

    /**
     * @param mixed $key
     *
     * @throws ApcuInvalidCacheKeyException
     */
    private function assertKeyName($key)
    {
        if (!is_scalar($key) || is_bool($key)) {
            throw new ApcuInvalidCacheKeyException();
        }
    }

    /**
     * @param string[] $keys
     *
     * @throws ApcuInvalidCacheKeyException
     */
    private function assertKeyNames(array $keys)
    {
        array_map(function ($value) {
            $this->assertKeyName($value);
        }, $keys);
    }

    /**
     * @param string[]|object|\Traversable $keys
     *
     * @throws ApcuInvalidCacheKeysException
     */
    private function convertKeysToArray($keys)
    {
        if (!$this->isIterableKeys($keys)) {
            throw new ApcuInvalidCacheKeysException();
        }

        return is_array($keys) ? $keys : iterator_to_array($keys);
    }

    /**
     * @param string[]|object|\Traversable $keys
     *
     * @return bool
     */
    private function isIterableKeys($keys)
    {
        if (function_exists('is_iterable')) {
            return is_iterable($keys);
        }

        return is_array($keys) || (is_object($keys) && ($keys instanceof \Traversable));
    }
}
