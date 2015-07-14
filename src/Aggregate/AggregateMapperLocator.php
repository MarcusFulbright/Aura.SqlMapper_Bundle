<?php
namespace Aura\SqlMapper_Bundle\Aggregate;

class AggregateMapperLocator implements \ArrayAccess, \Countable, \Iterator
{

    /**
     *
     * The array of Aggregate Mappers
     *
     * @var AggregateMapperInterface[]
     *
     */
    private $aggregate_mappers = array();

    /**
     *
     * The array_keys. This is how we iterate.
     *
     * @var string[]
     *
     */
    private $mapper_names = array();

    /**
     *
     * Current index referenced in the Iterator interface.
     *
     * @var int
     *
     */
    private $position = 0;

    /**
     *
     * Constructor
     *
     * @param mixed $members Either a single member, an array of members, or null to initialize with no members.
     *
     */
    public function __construct($members = null)
    {
        $this->position = 0;
        if ($members != null) {
            $this->initializeMembers($members);
        }
    }

    /**
     *
     * Whether or not the provided member is valid for this locator
     *
     * @param  mixed $member
     *
     * @return bool
     *
     */
    protected function isValidMember($member) {
        return $member instanceof AggregateMapperInterface;
    }

    /**
     *
     * Add the provided member, or array of members, to the members array
     *
     * @param mixed $members
     *
     */
    protected function initializeMembers($members)
    {
        $members = is_array($members) ? $members : array($members);
        foreach ($members as $key => $member) {
            $this[$key] = $member;
        }
    }

    /**
     *
     * From the ArrayAccess interface. Forwards to the Array class.
     *
     * @param  int $offset
     *
     * @return mixed
     *
     */
    public function offsetExists($offset)
    {
        return isset($this->aggregate_mappers[$offset]);
    }

    /**
     *
     * From the ArrayAccess interface. Filters for calls and forwards to the Array Class.
     *
     * @param  int $offset
     *
     * @param  mixed $member
     *
     * @throws \InvalidArgumentException If isValidMember returns false.
     *
     * @return void
     *
     */
    public function offsetSet($offset, $member)
    {
        if ($this->isValidMember($member) === false) {
            throw new \InvalidArgumentException(
                'AggregateMapperLocator can only manage members that implement AggregateMapperInterface'
            );
        }
        $this->aggregate_mappers[$offset] = $member;
        $this->mapper_names = array_keys($this->aggregate_mappers);
    }

    /**
     *
     * From the ArrayAccess interface. Forwards to the Array class.
     *
     * @param  int  $offset
     *
     * @return mixed
     *
     */
    public function offsetGet($offset)
    {
        return $this->aggregate_mappers[$offset];
    }

    /**
     *
     * From the ArrayAccess interface Forwards to the Array class.
     *
     * @param  int    $offset
     *
     * @return void
     *
     */
    public function offsetUnset($offset)
    {
        unset($this->aggregate_mappers[$offset]);
        $this->mapper_names = array_keys($this->aggregate_mappers);
    }

    /**
     *
     * From the Countable interface, forwards to count().
     *
     * @return int
     *
     */
    public function count()
    {
        return count($this->mapper_names);
    }

    /**
     *
     * From the Iterator interface
     *
     * @return void
     *
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     *
     * From the Iterator interface
     *
     * @return mixed
     *
     */
    public function current()
    {
        return $this[$this->getOffsetFromPosition($this->position)];
    }

    /**
     *
     * From the Iterator interface
     *
     * @return int
     *
     */
    public function key()
    {
        return $this->getOffsetFromPosition($this->position);
    }

    /**
     *
     * From the Iterator interface
     *
     * @return void
     *
     */
    public function next()
    {
        $this->position++;
    }

    /**
     *
     * From the Iterator interface
     *
     * @return boolean
     *
     */
    public function valid()
    {
        return isset($this->mapper_names[$this->position]);
    }

    /**
     *
     * Converts the numeric position to the string key.
     *
     * @param int $position
     *
     * @return string|int
     *
     */
    private function getOffsetFromPosition($position) {
        return $this->mapper_names[$position];
    }
}
