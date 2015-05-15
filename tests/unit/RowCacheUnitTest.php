<?php
namespace Aura\SqlMapper_Bundle\unit;

use Aura\SqlMapper_Bundle\RowCache;

class RowCacheUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RowCache
     */
    protected $cache;

    public function setUp()
    {
        $this->cache = new RowCache('id');
        $this->reflection = new \ReflectionClass($this->cache);
    }

    /**
     *
     * Returns an accessible ReflectionMethod by name.
     *
     * @param string $name The name of the method
     *
     * @return \ReflectionMethod
     *
     */
    protected function getProtectedMethod($name)
    {
        $method = $this->reflection->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     *
     * Returns an accessible ReflectionMethod by name.
     *
     * @param string $name The name of the method
     *
     * @return \ReflectionProperty
     *
     */
    protected function getProtectedProperty($name)
    {
        $property = $this->reflection->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     *
     * Create a quick object with an id and data property for testing.
     *
     * @param int $id The value of the ->id property.
     *
     * @param string $data The value of the ->data property.
     *
     * @return \StdClass The row object.
     *
     */
    protected function newRow($id, $data)
    {
        $object = new \StdClass();
        $object->id = $id;
        $object->data = $data;
        return $object;
    }

    protected function getCacheInstance($row)
    {
        $method = $this->getProtectedMethod('queryCacheInstances');
        $results = $method->invoke($this->cache, 'id', $row->id, false);
        return $results->results ? $results->results[0] : null;
    }

    // Public methods

    public function testStoringAndRetrievingRows()
    {
        $row = $this->newRow(14, 'row');
        $this->cache->set($row);

        $retrieved = $this->cache->get(14);
        $this->assertEquals($row, $retrieved);
    }

    public function testRetrievingNonexistentRowsShouldReturnNull()
    {
        $this->assertEquals(null, $this->cache->get(15));
    }

    public function testOverridingRows()
    {
        $row = $this->newRow(14, 'row');
        $this->cache->set($row);

        $rowTwo = $this->newRow(14, 'updated');
        $this->cache->set($rowTwo);

        $retrieved = $this->cache->get(14);
        $this->assertEquals($rowTwo, $retrieved);
        $this->assertNotEquals($row, $retrieved);
    }

    public function testGetCachedData()
    {
        $row = $this->newRow(14, 'row');
        $this->cache->set($row);
        $rowTwo = $this->newRow(14, 'updated');

        $this->assertEquals($row, $this->cache->getCachedData($rowTwo));
    }

    public function testQueryCache()
    {
        $rowone   = $this->newRow(5, 'test');
        $rowtwo   = $this->newRow(4, 'noTest');
        $rowthree = $this->newRow(3, 'test');

        $this->cache->set($rowone);
        $this->cache->set($rowtwo);
        $this->cache->set($rowthree);

        $results = $this->cache->queryCache('data', 'test');
        $shouldMatch = new \stdClass();
        $shouldMatch->results = array(
            $rowone,
            $rowthree
        );
        $shouldMatch->ids = array(5, 3);

        $this->assertEquals($results, $shouldMatch);
    }

    // Protected / internals

    /**
     * @todo Throw a better exception here.
     * @expectedException \Exception
     */
    public function testValidation()
    {
        $method = $this->getProtectedMethod('validateRow');
        $row = new \StdClass();
        $method->invoke($this->cache, $row);
    }

    public function testIsAlive()
    {
        // Get these protected methods
        $method = $this->getProtectedMethod('isAlive');
        $property = $this->getProtectedProperty('time_to_live');
        $cache = $this->getProtectedProperty('cache')->getValue($this->cache);

        // Set time to live to one second
        $property->setValue($this->cache, 1);

        // Create row and add it to the cache.
        $row = $this->newRow(1, 'data');
        $this->cache->set($row);

        // Get the actual cached instance
        $cached = $this->getCacheInstance($row);

        // Is it still alive?
        $this->assertTrue($method->invoke($this->cache, $cached));

        // Move timestamp back two seconds
        $cache[$cached] = time()-2;

        // Is it now dead?
        $this->assertFalse($method->invoke($this->cache, $cached));
    }

    // Complex situations
    public function testMultiCache()
    {
        $rowOne   = $this->newRow(1, 'once');
        $rowTwo   = $this->newRow(2, 'twice');
        $rowThree = $this->newRow(3, 'three times');
        $rowFour  = $this->newRow(4, 'a lady.');
        $rowFive  = $this->newRow(5, 'Uncached');

        $this->cache->set($rowOne);
        $this->cache->set($rowTwo);
        $this->cache->set($rowThree);
        $this->cache->set($rowFour);

        $this->assertEquals($rowTwo, $this->cache->get(2));
        $this->assertEquals($rowFour, $this->cache->getCachedData($rowFour));
        $this->assertEquals(null, $this->cache->getCachedData($rowFive));
        $this->assertEquals(null, $this->cache->get(5));

        $rowFourUpdated = $this->newRow(4, 'a foxy lady.');
        $this->assertEquals($rowFour, $this->cache->getCachedData($rowFourUpdated));
        $this->cache->set($rowFourUpdated);
        $this->assertEquals($rowFourUpdated, $this->cache->getCachedData($rowFour));
    }

    public function testCacheExpire()
    {
        $this->getProtectedProperty('time_to_live')->setValue($this->cache, 1);
        $cache = $this->getProtectedProperty('cache')->getValue($this->cache);

        $rowOne   = $this->newRow(1, 'I will expire.');
        $this->cache->set($rowOne);

        $cached = $this->getCacheInstance($rowOne);

        $this->assertEquals($rowOne, $cached);
        $this->assertEquals(1, count($cache));

        $cache[$cached] = time() - 1;

        $this->assertNull($this->cache->get(1));
        $this->assertEquals(0, count($cache));
    }

}
