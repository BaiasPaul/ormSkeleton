<?php


namespace ReallyOrm\Test\Entity;


use ReallyOrm\Entity\AbstractEntity;

class Quiz extends AbstractEntity
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
     * @var array
     * @ORM questions
     */
    private $questions;

    /**
     * @var array
     * @ORM answers
     */
    private $answers;

    /**
     * @var int
     * @ORM grade
     */
    private $grade;

    /**
     * Quiz constructor.
     * @param string $name
     * @param array $questions
     * @param array $answers
     * @param int $grade
     */
    public function __construct(string $name ='',array $questions=[],array $answers=[], int $grade=0)
    {
        $this->name = $name;
        $this->questions = $questions;
        $this->answers = $answers;
        $this->grade = $grade;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param array $questions
     */
    public function setQuestions(array $questions): void
    {
        $this->questions = $questions;
    }

    /**
     * @param array $answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    /**
     * @param int $grade
     */
    public function setGrade(int $grade): void
    {
        $this->grade = $grade;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
