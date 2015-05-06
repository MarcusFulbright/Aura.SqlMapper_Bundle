<?php
namespace Aura\SqlMapper_Bundle;

class FakeMapper extends AbstractMapper
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
