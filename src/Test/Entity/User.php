<?php


namespace ReallyOrm\Test\Entity;


use ReallyOrm\Entity\AbstractEntity;
use ReallyOrm\Repository\RepositoryManagerInterface;

class User extends AbstractEntity
{

    /**
     * @var int
     * @ID
     * @ORM id
     */
    private $id;

    /**
     * @var string
     * @ORM name
     */
    private $name;

    /**
     * @var string
     * @ORM email
     */
    private $email;

    public function __construct($name = '', $email = '')
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getId()
    {
        return $this->id;
    }
}