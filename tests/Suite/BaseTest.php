<?php

namespace tests\Suite;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Mocktrine\EntityManager;
use Mocktrine\Storage\ApcStorage;
use TestPackage\Entity\Role;
use TestPackage\Entity\User;
use TestPackage\Repository\UserRepository;

/**
 * Class BaseTest
 * @package tests\Suite
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected $database;

    public static function setUpBeforeClass()
    {
        //$this->database->getStorage()->save('user:1', $user);
        $entityManager = new EntityManager();
        $entityManager->persist(self::createUser(1,12));
        $entityManager->persist(self::createUser(2,12));
        $entityManager->persist(self::createUser(3,12));
        $entityManager->persist(self::createUser(4,12));
        $entityManager->persist(self::createUser(5,12));
        $entityManager->persist(self::createUser(6,12));
        $entityManager->persist(self::createUser(7,12));
        $entityManager->persist(self::createUser(8,12));
        $entityManager->flush();
    }

    protected static function createUser($id, $credit)
    {
        $newRole = function () {
            $role = new Role();
            $role->setId(rand(1, 5000));
            $role->setName('name'.rand(0, 100000));
            return $role;
        };

        $user = new User();
        $user->setId($id);
        $user->setCredit($credit);
        $user->getRoles()->add($newRole());
        $user->getRoles()->add($newRole());
        return $user;
    }

    public function testPersist()
    {
        $entityManager = new EntityManager();
        $repository = $entityManager->getRepository('\TestPackage\Entity\User');
        $u = $repository->find(4);

        $this->assertTrue($u instanceof User);
        $this->assertEquals($u->getId(), 4);
    }

    public function testCriteria()
    {
        $entityManager = new EntityManager();
        $repository = $entityManager->getRepository('\TestPackage\Entity\User');
        $results = $repository->findBy(array('credit' => 12));
        $singleOne = $repository->findOneBy(array('credit' => 12));
        foreach ($results as $r) {
            $this->assertEquals($r->getCredit(), 12);
        }
        $this->assertEquals($singleOne->getCredit(), 12);
    }

    public function testTest()
    {
        $this->assertTrue(true);
    }
/*
    public function testBasicMirroring()
    {
        $server = new Server();
        $pool = $server->getPool();
        $pool->put('test', 12);
        $resource = $pool->getSharedResource();
        $pool2 = new Pool(new MemoryResource($resource));
        $this->assertSame(12, $pool2->get('test'));

        $pool->put('test2', 20);
        $this->assertSame(20, $pool2->get('test2'));
    }*/

    public function testNewEntityRepository()
    {
        $entityManager = new EntityManager();
        $repository = $entityManager->getRepository('\TestPackage\Entity\User');
        $user = $repository->find(1);

        $this->assertTrue($user instanceof User);
        $this->assertSame($user->getId(), 1);
    }
}
