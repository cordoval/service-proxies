<?php

namespace PHPPeru;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class ServiceClassMetadata implements ClassMetadata
{
    protected $className;
    protected $identifier;

    public function __construct($className, $identifier)
    {
        $this->className = $className;
        $this->identifier = $identifier;
    }

    /**
     * {@inheritDoc}
     */
    function getName()
    {
        return $this->className;
    }

    /**
     * {@inheritDoc}
     */
    function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    function getReflectionClass()
    {
        return new \ReflectionClass($this->className);
    }

    /**
     * {@inheritDoc}
     */
    function isIdentifier($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    function hasField($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    function hasAssociation($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    function isSingleValuedAssociation($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    function isCollectionValuedAssociation($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    function getFieldNames()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    function getIdentifierFieldNames()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    function getAssociationNames()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    function getTypeOfField($fieldName)
    {
        return 'type_of_field_dummy';
    }

    /**
     * {@inheritDoc}
     */
    function getAssociationTargetClass($assocName)
    {
        return 'association_target_class_dummy';
    }

    /**
     * {@inheritDoc}
     */
    function isAssociationInverseSide($assocName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    function getAssociationMappedByTargetField($assocName)
    {
        return 'target_field_dummy';
    }

    /**
     * {@inheritDoc}
     */
    function getIdentifierValues($object)
    {
        return array();
    }
}