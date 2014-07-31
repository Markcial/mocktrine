<?php

namespace TestPackage\Repository;

use Mocktrine\Repository;

/**
 * Class UserRepository
 * @package TestPackage\Repository
 */
class UserRepository extends Repository
{
    public function __construct()
    {
        parent::__construct('TestPackage\Entity\User');
    }
}