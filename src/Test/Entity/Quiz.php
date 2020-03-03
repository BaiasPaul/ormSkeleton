<?php


namespace ReallyOrm\Test\Entity;


class Quiz
{
    /**
     * @var int
     * @ORM id
     */
    private $id;

    /**
     * @var string
     * @ORM id
     */
    private $name;

    /**
     * @var array
     * @ORM id
     */
    private $questions;

    /**
     * @var array
     * @ORM id
     */
    private $answers;

    /**
     * @var int
     * @ORM id
     */
    private $grade;

    /**
     * @var int
     * @ORM id
     */
    private $userId;

    public function __construct($name,$questions,$answers,$grade,$userId)
    {
        $this->name = $name;
        $this->questions = $questions;
        $this->answers = $answers;
        $this->grade = $grade;
        $this->userId = $userId;
    }

    public function getId()
    {
        return $this->id;
    }
}
