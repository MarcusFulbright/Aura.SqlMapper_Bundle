<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

use Aura\SqlMapper_Bundle\Row\AbstractRowGateway;

class FakeGateway extends AbstractRowGateway
{
    protected $table = 'aura_test_table';

    protected $primary_col = 'id';

    public function getTable()
    {
        return $this->table;
    }

    public function getPrimaryCol()
    {
        return $this->primary_col;
    }

    public function setInfo($table, $primary_col)
    {
        $this->table = $table;
        $this->primary_col = $primary_col;
    }
}