<?php

namespace TestPackage\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * Class User
 * @package TestPackage\Entity
 */
class User
{
    private $id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}