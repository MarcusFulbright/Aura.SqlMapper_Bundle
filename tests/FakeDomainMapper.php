<?php
namespace Aura\SqlMapper_Bundle;

/**
 * [summary].
 *
 *[description]
 */
class FakeDomainMapper extends AbstractDomainMapper
{
    /**
     * @return array
     */
    public function getPropertyMap()
    {
        return array(
            'createdBy'  => 'user',
            'modifiedBy' => 'user',
            'phones' => 'phone[]',
            'id' => 'account',
            'name' => 'account',
            'createdByID' => 'account',
            'modifiedByID' => 'account'
        );
    }

    public function getMapperMap()
    {
        return array(
            'user' => array(
                'createdBy' => false,
                'modifiedBy' => false,
            ),
            'phone' => array(
                'phones' => true
            ),
            'account' => array(
                'id',
                'name'
            )
      );
    }

    public function getJoin()
    {
        return array(
            'createdBy' => array(
                'rootProperty' => 'createdByID',
                'targetProperty' => 'id',
                'owner' => true
            ),
            'modifiedBy' => array(
                'rootProperty' => 'modifiedByID',
                'targetProperty' => 'id',
                'owner' => true
            ),
            'phones' => array(
                'rootProperty' => 'id',
                'targetProperty' => 'AccountID',
                'owner' => false
            )
        );
    }
}