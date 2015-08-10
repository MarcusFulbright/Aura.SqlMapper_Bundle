<?php
namespace Aura\SqlMapper_Bundle\EntityMediation;

class PlaceHolder
{
    /** @var callable */
    protected $value;

    /**
     * Constructor.
     *
     * @param callable $value
     *
     */
    public function __construct(callable $value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        $value = $this->value;
        return $value();
    }

    /** @return callable */
    public function getValue()
    {
        return $this->value;
    }

    /** @param callable $value */
    public function setValue($value)
    {
        $this->value = $value;
    }
}