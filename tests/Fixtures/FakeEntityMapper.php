<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Entity\AbstractEntityMapper;

class FakeEntityMapper extends AbstractEntityMapper
{
    protected $cols_fields = [
        'id'      => 'id',
        'name'    => 'firstName',
        'building' => 'buildingNumber',
        'floor' => 'floor'
    ];

    public function getColsFields()
    {
        return $this->cols_fields;
    }

    public function setColsFields(array $cols_fields)
    {
        $this->cols_fields = $cols_fields;
    }
}
