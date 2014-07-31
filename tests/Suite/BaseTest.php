<?php

namespace tests\Suite;

use TestPackage\Entity\User;
use TestPackage\Repository\UserRepository;

/**
 * Class BaseTest
 * @package tests\Suite
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {

        $this->database = new \Mocktrine\Pool();
        $user = new User();
        $this->database->getStorage()->save('user:1', $user);
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
        $repository = new UserRepository();
        $user = $repository->find(1);

        $this->assertTrue($user instanceof User);
        $this->assertSame($user->getId(), 1);
    }
}