<?php

use PHPUnit\Framework\TestCase;
use ReallyOrm\Test\Hydrator\Hydrator;
use ReallyOrm\Test\Entity\User;
use ReallyOrm\Test\Repository\RepositoryManager;
use ReallyOrm\Test\Repository\UserRepository;

/**
 * Class FunTest.
 *
 * Have fun!
 */
class FunTest extends TestCase
{
    private $pdo;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @var RepositoryManager
     */
    private $repoManager;


    protected function setUp(): void
    {
        parent::setUp();

        $config = require 'db_config.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        $this->repoManager = new RepositoryManager();
        $this->hydrator = new Hydrator($this->repoManager);
        $this->userRepo = new UserRepository($this->pdo, User::class, $this->hydrator);
        $this->repoManager->addRepository($this->userRepo);
    }

//    public function testCreateUser(): void
//    {
//        $user = new User();
//        $user->setName('ciwawa');
//        $user->setEmail('email');
//        $this->repoManager->register($user);
//        $result = $user->save();
//
//        $this->assertEquals(true, $result);
//    }

//    public function testUpdateUser(): void
//    {
//        $user = $this->userRepo->find(1);
//        $user->setEmail('paul@email.com');
//        $this->repoManager->register($user);
//        $result = $user->save();
//
//        $this->assertEquals(true, $result);
//    }

    public function testFind(): void
    {
        /** @var User $user */
        $user = $this->userRepo->find(1);
        $this->assertEquals(1, $user->getId());
    }

    public function testHydrate(): void
    {
        $entitie = new User('jhon', 'jhon@email.com');
        $data = [
            'id' => null,
            'name' => 'jhon',
            'email' => 'jhon@email.com'
        ];
        $result = $this->hydrator->hydrate(User::class, $data);
        $this->assertEquals($entitie, $result);
    }

    public function testExtract(): void
    {
        $user = new User('jhon', 'jhon@email.com');
        $data = [
            'id' => null,
            'name' => 'jhon',
            'email' => 'jhon@email.com'
        ];
        $result = $this->hydrator->extract($user);
        $this->assertEquals($data, $result);
    }

    /**
     * @test
     * @dataProvider findOneByProvider
     */
    public function testFindOneBy($filter): void
    {
        /** @var User $user */
        $user = $this->userRepo->findOneBy($filter);
        $this->assertEquals(1, $user->getId());
    }

    public function findOneByProvider()
    {
        return [
            [
                [
                    'name' => 'paul'
                ]
            ],
            [
                [
                    'name' => 'paul',
                    'email' => 'paul@email.com'
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider findByProvider
     */
    public function testFindBy($filter, $sorts): void
    {
        /** @var User $user */
        $user = $this->userRepo->findBy($filter, $sorts, 0, 1);
        $this->assertEquals(1, $user[0]->getId());
    }

    public function findByProvider()
    {
        return [
            [
                [
                    'name' => 'paul'
                ],
                [
                    'name' => 'DESC'
                ]
            ],
            [
                [
                    'name' => 'paul',
                    'email' => 'paul@email.com'
                ],
                [
                    'name' => 'DESC',
                    'email' => 'ASC'
                ]
            ]
        ];
    }

    /**
     * @test
     */
    public function testInsertOnDuplicateKeyUpdate(): void
    {
        /** @var User $user */

        $user = $this->userRepo->find(2);
        $user->setName("paul");
        $result = $this->userRepo->insertOnDuplicateKeyUpdate($user);

        $this->assertEquals(1, $result);
    }

    public function testDelete()
    {
        $user = $this->userRepo->find(9);
        $result = $this->userRepo->delete($user);

        $this->assertEquals(1, $result);
    }

}
