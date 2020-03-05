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

    /**
     * User constructor.
     * @param string $name
     * @param string $email
     */
    public function __construct(string $name = '', string $email = '')
    {
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}