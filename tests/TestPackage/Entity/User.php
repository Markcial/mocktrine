<?php

namespace TestPackage\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class User
 * @package TestPackage\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="product")
 */
class User
{
    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\Column(type="string", name="name", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="decimal", name="credit", scale=2)
     */
    protected $credit;

    /**
     * @ORM\ManyToMany(targetEntity="TestPackage\Entity\Role")
     */
    protected $roles;

    public function __construct()
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $credit
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;
    }

    /**
     * @return mixed
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $roles
     * @internal param mixed $role
     */
    public function setRoles(Collection $roles)
    {
        $this->roles = $roles;
    }

    public function addRole($role)
    {
        $this->roles->add($role);
    }
}
