<?php
namespace Aura\SqlMapper_Bundle\Tests\Fixtures\Entities;

class TaskType 
{
    protected $id;

    protected $code;

    protected $decode;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getDecode()
    {
        return $this->decode;
    }

    /**
     * @param mixed $decode
     */
    public function setDecode($decode)
    {
        $this->decode = $decode;
    }
}