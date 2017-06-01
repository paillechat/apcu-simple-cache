<?php

namespace Paillechat\ApcuSimpleCache\Tests;

use Paillechat\ApcuSimpleCache\ApcuCache;

class ApcuCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var ApcuCache */
    private $cache;

    protected function setUp()
    {
        $this->cache = new ApcuCache('test_');
    }

    public function testBasicallyFunctions()
    {
        $foo = new \stdClass();
        $foo->bar = 'baz';

        $bar = new \stdClass();
        $bar->baz = 'foo';

        $baz = new \stdClass();
        $baz->bar = 'foo';

        $this->assertEquals(true, $this->cache->set('foo', $foo));
        $this->assertEquals(true, $this->cache->set('bar', $bar));

        $this->assertEquals($foo, $this->cache->get('foo'));
        $this->assertEquals($bar, $this->cache->get('bar'));
        // when not found, return false
        $this->assertEquals(false, $this->cache->get('baz'));
        // when not found, but has default value
        $this->assertEquals($baz, $this->cache->get('baz', $baz));

        // delete values
        $this->assertEquals(true, $this->cache->delete('bar'));
        $this->assertEquals(false, $this->cache->delete('baz'));
        $this->assertEquals(true, $this->cache->has('foo'));
        $this->assertEquals($foo, $this->cache->get('foo'));
        $this->assertEquals(false, $this->cache->has('bar'));

        // clear and check that data is empty
        $this->assertEquals(true, $this->cache->clear());
        $this->assertEquals(false, $this->cache->has('foo'));
        $this->assertEquals(false, $this->cache->has('bar'));

        // override value
        $this->cache->set('foo', $foo);
        $this->assertEquals($foo, $this->cache->get('foo'));
        $this->cache->set('foo', $bar);
        $this->assertEquals($bar, $this->cache->get('foo'));
    }

    public function testMultipleFunctions()
    {
        $foo = new \stdClass();
        $foo->bar = 'baz';

        $bar = new \stdClass();
        $bar->baz = 'foo';

        $baz = new \stdClass();
        $baz->bar = 'foo';

        $data = [
            'foo_1' => $foo,
            'bar_1' => $bar,
            'baz_1' => $baz,
        ];

        $dataDefault = [
            'foo_1' => $foo,
            'bar_2' => $bar,
            'baz_2' => $bar,
        ];

        $this->assertEquals(true, $this->cache->setMultiple($data));

        $this->assertEquals(true, $this->cache->has('foo_1'));
        $this->assertEquals(true, $this->cache->has('bar_1'));
        $this->assertEquals(true, $this->cache->has('baz_1'));

        $this->assertEquals($data, $this->cache->getMultiple(['foo_1', 'bar_1', 'baz_1']));

        // when not found, return empty array
        $this->assertEquals([], $this->cache->getMultiple(['foo_3', 'bar_3', 'baz_3']));

        // when part was found
        $this->assertEquals(['foo_1' => $foo], $this->cache->getMultiple(['foo_1', 'bar_3', 'baz_3']));

        // when has not found keys, but has default value
        $this->assertEquals($dataDefault, $this->cache->getMultiple(['foo_1', 'bar_2', 'baz_2'], $bar));

        // delete existed value
        $this->assertEquals(true, $this->cache->deleteMultiple(['baz_1']));
        $this->assertEquals(false, $this->cache->has('baz_1'));

        // delete none existed value
        $this->assertEquals(false, $this->cache->deleteMultiple(['baz_10']));

        // delete when one existed and one none existed
        $this->assertEquals(true, $this->cache->deleteMultiple(['bar_1', 'baz_10']));

        // clear and check that data is empty
        $this->assertEquals(true, $this->cache->clear());
        $this->assertEquals([], $this->cache->getMultiple(['foo_1', 'bar_1', 'baz_1']));
    }

    /**
     * @dataProvider dataForTestGetWithInvalidKey
     * @expectedException \Paillechat\ApcuSimpleCache\Exception\ApcuInvalidCacheKeyException
     *
     * @param mixed $key
     */
    public function testGetWithInvalidKey($key)
    {
        $this->cache->get($key);
    }

    public function dataForTestGetWithInvalidKey()
    {
        return [
            'null' => [null],
            'object' => [new \stdClass()],
            'array' => [[]],
            'bool' => [false],
            'resource' => [fopen('/tmp/test.1', 'w+')],
        ];
    }

    /**
     * @dataProvider dataForTestSetAndGetWithAllowedKeys
     *
     * @param $key
     */
    public function testSetAndGetWithAllowedKeys($key)
    {
        $this->cache->set($key, 'foo');
        $this->assertEquals('foo', $this->cache->get($key));
    }

    public function dataForTestSetAndGetWithAllowedKeys()
    {
        return [
            [5],
            [1.1],
            ['bar'],
        ];
    }

    public function testWhenApcuUnavailable()
    {
        ini_set('apc.enable_cli', 0);

        new ApcuCache();
    }
}
