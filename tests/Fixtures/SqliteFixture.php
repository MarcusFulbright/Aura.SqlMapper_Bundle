<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures;

class SqliteFixture
{
    protected $create_table = "CREATE TABLE aura_test_table (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        name     VARCHAR(50) NOT NULL UNIQUE,
        building INTEGER,
        floor    INTEGER
    )";

    protected $create_building = "CREATE TABLE aura_test_building (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        name     VARCHAR(50) NOT NULL UNIQUE,
        type     VARCHAR(2) NOT NULL
    )";

    protected $create_buildingref = "CREATE TABLE aura_test_building_typeref (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        code     VARCHAR(2) NOT NULL UNIQUE,
        decode   VARCHAR(50) NOT NULL UNIQUE
    )";

    protected $create_floor = "CREATE TABLE aura_test_floor (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        name     VARCHAR(50) NOT NULL UNIQUE
    )";
    
    protected $create_task = "CREATE TABLE aura_test_task (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        userid   INTEGER NOT NULL,
        name     VARCHAR(50) NOT NULL,
        type     VARCHAR(2) NOT NULL
    )";

    protected $create_taskref = "CREATE TABLE aura_test_task_typeref (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        code     VARCHAR(2) NOT NULL UNIQUE,
        decode   VARCHAR(50) NOT NULL UNIQUE
    )";

    public $connection;

    public $table;

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function exec()
    {
        $this->createTables();
        $this->fillUserTable();
        $this->fillBuildingTable();
        $this->fillBuildingTypeRefTable();
        $this->fillFloorTable();
        $this->fillTaskTable();
        $this->fillTaskTypeRefTable();
    }

    protected function createTables()
    {
        $table_queries = [
            $this->create_table,
            $this->create_building,
            $this->create_buildingref,
            $this->create_floor,
            $this->create_task,
            $this->create_taskref
        ];

        foreach ($table_queries as $sql) {
            $this->connection->query($sql);
        }
    }

    protected function fillUserTable()
    {
        $data = [
            ['Anna',  1, 1],
            ['Betty', 1, 2],
            ['Clara', 1, 3],
            ['Donna', 1, 1],
            ['Edna',  1, 2],
            ['Fiona', 1, 3],
            ['Gina',  null, 1],
            ['Hanna', 2, 2],
            ['Ione',  2, 3],
            ['Julia', 2, 1],
            ['Kara',  2, 2],
            ['Lana',  2, 3],
        ];

        $stm = "INSERT INTO aura_test_table (name, building, floor)
                VALUES (:name, :building, :floor)";

        foreach ($data as $vals) {
            list($name, $building, $floor) = $vals;
            $this->connection->perform($stm, [
                'name' => $name,
                'building' => $building,
                'floor' => $floor
            ]);
        }
    }

    protected function fillBuildingTable()
    {
        $data = [
            ['Bower Street', 'NP'],
            ['Dominion',     'P']
        ];

        $stm = "INSERT INTO aura_test_building (name, type)
                VALUES (:name, :type)";

        foreach ($data as $vals) {
            list($name, $type) = $vals;
            $this->connection->perform($stm, [
                'name' => $name,
                'type' => $type
            ]);
        }
    }

    protected function fillBuildingTypeRefTable()
    {
        $data = [
            ['NP', 'Non-Profit'],
            ['P',  'For Profit']
        ];

        $stm = "INSERT INTO aura_test_building_typeref (code, decode)
                VALUES (:code, :decode)";

        foreach ($data as $vals) {
            list($code, $decode) = $vals;
            $this->connection->perform($stm, [
                'code' => $code,
                'decode' => $decode
            ]);
        }
    }

    protected function fillFloorTable()
    {
        $data = [
            ['Reception'],
            ['Accounting'],
            ['Marketing']
        ];

        $stm = "INSERT INTO aura_test_floor (name)
                VALUES (:name)";

        foreach ($data as $name) {
            $this->connection->perform($stm, [
                'name' => $name
            ]);
        }
    }

    protected function fillTaskTable()
    {
        $data = [
            ['Manage Calendar',   'S', 1],
            ['Plan Potluck',      'P', 1],
            ['Budget Planning',   'F', 2],
            ['Budget Meeting',    'M', 2],
            ['Budget Meeting',    'M', 5],
            ['Budget Meeting',    'M', 8]
        ];

        $stm = "INSERT INTO aura_test_task (name, type, userid)
                VALUES (:name, :type, :userid)";

        foreach ($data as $vals) {
            list($name, $type, $userid) = $vals;
            $this->connection->perform($stm, [
                'name' => $name,
                'type' => $type,
                'userid' => $userid
            ]);
        }
    }

    protected function fillTaskTypeRefTable()
    {
        $data = [
            ['S', 'Scheduling'],
            ['P', 'Party / Event'],
            ['F', 'Financials'],
            ['M', 'Meeting']
        ];

        $stm = "INSERT INTO aura_test_task_typeref (code, decode)
                VALUES (:code, :decode)";

        foreach ($data as $vals) {
            list($code, $decode) = $vals;
            $this->connection->perform($stm, [
                'code' => $code,
                'decode' => $decode
            ]);
        }
    }
}
