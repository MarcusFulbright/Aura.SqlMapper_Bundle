<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Entity\AbstractEntityMapper;
use Aura\SqlMapper_Bundle\Row\RowGatewayInterface;

class FakeEntityMapper extends AbstractEntityMapper
{
    protected $cols_fields;

    protected $gateway;

    public function getColsFields()
    {
        return $this->cols_fields;
    }

    public function setColsFields(array $cols_fields)
    {
        $this->cols_fields = $cols_fields;
    }

    public function setGateway(RowGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }
}