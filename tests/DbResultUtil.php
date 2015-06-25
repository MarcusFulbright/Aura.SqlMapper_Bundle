<?php
namespace Aura\SqlMapper_Bundle;

trait DbResultUtil
{
    public function getBetty()
    {
        return [
            '__root' => [
                (object)[
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2'
                ]
            ],
            'floor' => [$this->getAccountingFloor()],
            'building' => [$this->getBowerStreetBuilding()],
            'building.type' => [$this->resolveBuildingType('NP')],
            'task' => $this->resolveTasks(2),
            'task.type' => [
                $this->resolveTaskType('F'),
                $this->resolveTaskType('M')
            ]
        ];
    }

    public function getEdna()
    {
        return [
            '__root' => [
                (object)[
                'id' => '5',
                'name' => 'Edna',
                'building' => '1',
                'floor' => '2',
                ]
            ],
            'floor' => [$this->getAccountingFloor()],
            'building' => [$this->getBowerStreetBuilding()],
            'building.type' => [$this->resolveBuildingType('NP')],
            'task' => $this->resolveTasks(5),
            'task.type' => [$this->resolveTaskType('M')]
        ];
    }

    public function getHanna()
    {
        return [
            '__root' => [
                (object)[
                'id' => '8',
                'name' => 'Hanna',
                'building' => '2',
                'floor' => '2',
                ]
            ],
            'floor' => [$this->getAccountingFloor()],
            'building' => [$this->getDominionBuilding()],
            'building.type' => [$this->resolveBuildingType('P')],
            'task' => $this->resolveTasks(8),
            'task.type' => [$this->resolveTaskType('M')]
        ];
    }


    public function getKara()
    {
        return [
            '__root' => [
                (object)[
                'id' => '11',
                'name' => 'Kara',
                'building' => '2',
                'floor' => 2,
                ]
            ],
            'floor' => [$this->getAccountingFloor()],
            'building' => [$this->getDominionBuilding()],
            'building.type' => [$this->resolveBuildingType('P')],
            'task' => [],
            'task.type' => []
        ];
    }

    public function getDominionBuilding()
    {
        return (object)[
            'id' => '2',
            'name' => 'Dominion',
            'type' => 'P'
        ];
    }

    public function getBowerStreetBuilding()
    {
        return (object)[
            'id' => '1',
            'name' => 'Bower Street',
            'type' => 'NP'
        ];
    }

    public function getAccountingFloor()
    {
        return (object) [
            'id' => '2',
            'name' => 'Accounting'
        ];
    }

    public function resolveBuildingType($type)
    {
        $rows = [
            [
                'id' => '1',
                'code' => 'NP',
                'decode' => 'Non-Profit'
            ],
            [
                'id' => '2',
                'code' => 'P',
                'decode' => 'For Profit'
            ]
        ];

        foreach ($rows as $row) {
            if ($row['code'] === $type) {
                return (object) $row;
            }
        }
        return null;
    }

    public function resolveTasks($userid)
    {
        $entries = [
            [
                'id' => '1',
                'name' => 'Manage Calendar',
                'type' => 'S',
                'userid' => '3'
            ],
            [
                'id' => '2',
                'name' => 'Plan Potluck',
                'type' => 'P',
                'userid' => '1'
            ],
            [
                'id' => '3',
                'name' => 'Budget Planning',
                'type' => 'F',
                'userid' => '2'
            ],
            [
                'id' => '4',
                'name' => 'Budget Meeting',
                'type' => 'M',
                'userid' => '2'
            ],
            [
                'id' => '5',
                'name' => 'Budget Meeting',
                'type' => 'M',
                'userid' => '5'
            ],
            [
                'id' => '6',
                'name' => 'Budget Meeting',
                'type' => 'M',
                'userid' => '8'
            ]
        ];
        foreach ($entries as $entry) {
            if ($entry['userid'] === (string)$userid) {
                $output[] = (object)$entry;
            }
        }
        if (isset($output)) {
            return $output;
        }
        return [];
    }

    public function resolveTaskType($type)
    {
        $entries = [
            [
                'id' => '1',
                'code' => 'S',
                'decode' => 'Scheduling'
            ],
            [
                'id' => '2',
                'code' => 'P',
                'decode' => 'Party / Event'
            ],
            [
                'id' => '3',
                'code' => 'F',
                'decode' => 'Financials'
            ],
            [
                'id' => '4',
                'code' => 'M',
                'decode' => 'Meeting'
            ]
        ];

        foreach ($entries as $entry) {
            if ($entry['code'] === $type) {
                return (object)$entry;
            }
        }
        return null;
    }

    public function mergeDbResults(array $args, &$output = [])
    {
        foreach ($args as $arg) {
            foreach ($arg as $key => $value) {
                if (isset($output[$key])) {
                    $output[$key] = array_merge($output[$key], $value);
                    $output[$key] = array_unique($output[$key], SORT_REGULAR);
                } else {
                    $output[$key] = $value;
                }
            }
        }
        return $output;
    }
}