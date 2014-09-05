<?php

namespace Mocktrine\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMException;
use Mocktrine\EntityManager;

/**
 * Class ClassMetadataFactory
 * @package Mocktrine\Mapping
 */
class ClassMetadataFactory extends AbstractClassMetadataFactory
{
    protected $annotationReader;
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $targetPlatform;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    private $driver;

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    protected function getAnnotationReader()
    {
        if (is_null($this->annotationReader)) {
            $reader = new AnnotationReader();

            $this->annotationReader = new CachedReader(
                $reader,
                new ArrayCache()
            );
        }

        return $this->annotationReader;
    }

    /**
     * {@inheritDoc}.
     */
    protected function initialize()
    {
        $reader = $this->getAnnotationReader();
        $this->driver = new AnnotationDriver($reader);
        $this->initialized = true;
    }


    /**
     * {@inheritDoc}
     */
    protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents)
    {
        /* @var $class ClassMetadata */
        /* @var $parent ClassMetadata */
        if ($parent) {
            $class->setInheritanceType($parent->inheritanceType);
            $class->setDiscriminatorColumn($parent->discriminatorColumn);
            $class->setIdGeneratorType($parent->generatorType);
            $this->addInheritedFields($class, $parent);
            $this->addInheritedRelations($class, $parent);
            $class->setIdentifier($parent->identifier);
            $class->setVersioned($parent->isVersioned);
            $class->setVersionField($parent->versionField);
            $class->setDiscriminatorMap($parent->discriminatorMap);
            $class->setLifecycleCallbacks($parent->lifecycleCallbacks);
            $class->setChangeTrackingPolicy($parent->changeTrackingPolicy);

            if ( ! empty($parent->customGeneratorDefinition)) {
                $class->setCustomGeneratorDefinition($parent->customGeneratorDefinition);
            }

            if ($parent->isMappedSuperclass) {
                $class->setCustomRepositoryClass($parent->customRepositoryClassName);
            }
        }

        // Invoke driver
        try {
            $this->driver->loadMetadataForClass($class->getName(), $class);
        } catch (\ReflectionException $e) {
            throw MappingException::reflectionFailure($class->getName(), $e);
        }

        // If this class has a parent the id generator strategy is inherited.
        // However this is only true if the hierarchy of parents contains the root entity,
        // if it consists of mapped superclasses these don't necessarily include the id field.
        if ($parent && $rootEntityFound) {
            if ($parent->isIdGeneratorSequence()) {
                $class->setSequenceGeneratorDefinition($parent->sequenceGeneratorDefinition);
            } elseif ($parent->isIdGeneratorTable()) {
                $class->tableGeneratorDefinition = $parent->tableGeneratorDefinition;
            }

            if ($parent->generatorType) {
                $class->setIdGeneratorType($parent->generatorType);
            }

            if ($parent->idGenerator) {
                $class->setIdGenerator($parent->idGenerator);
            }
        }

        if ($parent && $parent->isInheritanceTypeSingleTable()) {
            $class->setPrimaryTable($parent->table);
        }

        if ($parent && $parent->containsForeignIdentifier) {
            $class->containsForeignIdentifier = true;
        }

        if ($parent && !empty($parent->namedQueries)) {
            $this->addInheritedNamedQueries($class, $parent);
        }

        if ($parent && !empty($parent->namedNativeQueries)) {
            $this->addInheritedNamedNativeQueries($class, $parent);
        }

        if ($parent && !empty($parent->sqlResultSetMappings)) {
            $this->addInheritedSqlResultSetMappings($class, $parent);
        }

        if ($parent && !empty($parent->entityListeners) && empty($class->entityListeners)) {
            $class->entityListeners = $parent->entityListeners;
        }

        $class->setParentClasses($nonSuperclassParents);

        if ( $class->isRootEntity() && ! $class->isInheritanceTypeNone() && ! $class->discriminatorMap) {
            $this->addDefaultDiscriminatorMap($class);
        }

        $this->validateRuntimeMetadata($class, $parent);
    }

    /**
     * Validate runtime metadata is correctly defined.
     *
     * @param ClassMetadata               $class
     * @param ClassMetadataInterface|null $parent
     *
     * @return void
     *
     * @throws MappingException
     */
    protected function validateRuntimeMetadata($class, $parent)
    {
        if ( ! $class->reflClass ) {
            // only validate if there is a reflection class instance
            return;
        }

        $class->validateIdentifier();
        $class->validateAssociations();
        $class->validateLifecycleCallbacks($this->getReflectionService());

        // verify inheritance
        if ( ! $class->isMappedSuperclass && !$class->isInheritanceTypeNone()) {
            if ( ! $parent) {
                if (count($class->discriminatorMap) == 0) {
                    throw MappingException::missingDiscriminatorMap($class->name);
                }
                if ( ! $class->discriminatorColumn) {
                    throw MappingException::missingDiscriminatorColumn($class->name);
                }
            } else if ($parent && !$class->reflClass->isAbstract() && !in_array($class->name, array_values($class->discriminatorMap))) {
                // enforce discriminator map for all entities of an inheritance hierarchy, otherwise problems will occur.
                throw MappingException::mappedClassNotPartOfDiscriminatorMap($class->name, $class->rootEntityName);
            }
        } else if ($class->isMappedSuperclass && $class->name == $class->rootEntityName && (count($class->discriminatorMap) || $class->discriminatorColumn)) {
            // second condition is necessary for mapped superclasses in the middle of an inheritance hierarchy
            throw MappingException::noInheritanceOnMappedSuperClass($class->name);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className, new DefaultNamingStrategy());
    }

    /**
     * Adds a default discriminator map if no one is given
     *
     * If an entity is of any inheritance type and does not contain a
     * discriminator map, then the map is generated automatically. This process
     * is expensive computation wise.
     *
     * The automatically generated discriminator map contains the lowercase short name of
     * each class as key.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     *
     * @throws MappingException
     */
    private function addDefaultDiscriminatorMap(ClassMetadata $class)
    {
        $allClasses = $this->driver->getAllClassNames();
        $fqcn = $class->getName();
        $map = array($this->getShortName($class->name) => $fqcn);

        $duplicates = array();
        foreach ($allClasses as $subClassCandidate) {
            if (is_subclass_of($subClassCandidate, $fqcn)) {
                $shortName = $this->getShortName($subClassCandidate);

                if (isset($map[$shortName])) {
                    $duplicates[] = $shortName;
                }

                $map[$shortName] = $subClassCandidate;
            }
        }

        if ($duplicates) {
            throw MappingException::duplicateDiscriminatorEntry($class->name, $duplicates, $map);
        }

        $class->setDiscriminatorMap($map);
    }

    /**
     * Gets the lower-case short name of a class.
     *
     * @param string $className
     *
     * @return string
     */
    private function getShortName($className)
    {
        if (strpos($className, "\\") === false) {
            return strtolower($className);
        }

        $parts = explode("\\", $className);
        return strtolower(end($parts));
    }

    /**
     * Adds inherited fields to the subclass mapping.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     *
     * @return void
     */
    private function addInheritedFields(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->fieldMappings as $mapping) {
            if ( ! isset($mapping['inherited']) && ! $parentClass->isMappedSuperclass) {
                $mapping['inherited'] = $parentClass->name;
            }
            if ( ! isset($mapping['declared'])) {
                $mapping['declared'] = $parentClass->name;
            }
            $subClass->addInheritedFieldMapping($mapping);
        }
        foreach ($parentClass->reflFields as $name => $field) {
            $subClass->reflFields[$name] = $field;
        }
    }

    /**
     * Adds inherited association mappings to the subclass mapping.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     *
     * @return void
     *
     * @throws MappingException
     */
    private function addInheritedRelations(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->associationMappings as $field => $mapping) {
            if ($parentClass->isMappedSuperclass) {
                if ($mapping['type'] & ClassMetadata::TO_MANY && !$mapping['isOwningSide']) {
                    throw MappingException::illegalToManyAssociationOnMappedSuperclass($parentClass->name, $field);
                }
                $mapping['sourceEntity'] = $subClass->name;
            }

            //$subclassMapping = $mapping;
            if ( ! isset($mapping['inherited']) && ! $parentClass->isMappedSuperclass) {
                $mapping['inherited'] = $parentClass->name;
            }
            if ( ! isset($mapping['declared'])) {
                $mapping['declared'] = $parentClass->name;
            }
            $subClass->addInheritedAssociationMapping($mapping);
        }
    }

    /**
     * Adds inherited named queries to the subclass mapping.
     *
     * @since 2.2
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     *
     * @return void
     */
    private function addInheritedNamedQueries(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->namedQueries as $name => $query) {
            if ( ! isset ($subClass->namedQueries[$name])) {
                $subClass->addNamedQuery(array(
                    'name'  => $query['name'],
                    'query' => $query['query']
                ));
            }
        }
    }

    /**
     * Adds inherited named native queries to the subclass mapping.
     *
     * @since 2.3
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     *
     * @return void
     */
    private function addInheritedNamedNativeQueries(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->namedNativeQueries as $name => $query) {
            if ( ! isset ($subClass->namedNativeQueries[$name])) {
                $subClass->addNamedNativeQuery(array(
                    'name'              => $query['name'],
                    'query'             => $query['query'],
                    'isSelfClass'       => $query['isSelfClass'],
                    'resultSetMapping'  => $query['resultSetMapping'],
                    'resultClass'       => $query['isSelfClass'] ? $subClass->name : $query['resultClass'],
                ));
            }
        }
    }

    /**
     * Adds inherited sql result set mappings to the subclass mapping.
     *
     * @since 2.3
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     *
     * @return void
     */
    private function addInheritedSqlResultSetMappings(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->sqlResultSetMappings as $name => $mapping) {
            if ( ! isset ($subClass->sqlResultSetMappings[$name])) {
                $entities = array();
                foreach ($mapping['entities'] as $entity) {
                    $entities[] = array(
                        'fields'                => $entity['fields'],
                        'isSelfClass'           => $entity['isSelfClass'],
                        'discriminatorColumn'   => $entity['discriminatorColumn'],
                        'entityClass'           => $entity['isSelfClass'] ? $subClass->name : $entity['entityClass'],
                    );
                }

                $subClass->addSqlResultSetMapping(array(
                    'name'          => $mapping['name'],
                    'columns'       => $mapping['columns'],
                    'entities'      => $entities,
                ));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName)
    {
        return $this->em->getConfiguration()->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * Checks whether the class metadata is an entity.
     *
     * This method should return false for mapped superclasses or embedded classes.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return boolean
     */
    protected function isEntity(\Doctrine\Common\Persistence\Mapping\ClassMetadata $class)
    {
        // TODO: Implement isEntity() method.
    }

    /**
     * Wakes up reflection after ClassMetadata gets unserialized from cache.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     * @param ReflectionService $reflService
     *
     * @return void
     */
    protected function wakeupReflection(\Doctrine\Common\Persistence\Mapping\ClassMetadata $class, ReflectionService $reflService)
    {
        // TODO: Implement wakeupReflection() method.
    }

    /**
     * Initializes Reflection after ClassMetadata was constructed.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     * @param ReflectionService $reflService
     *
     * @return void
     */
    protected function initializeReflection(\Doctrine\Common\Persistence\Mapping\ClassMetadata $class, ReflectionService $reflService)
    {
        // TODO: Implement initializeReflection() method.
    }
}