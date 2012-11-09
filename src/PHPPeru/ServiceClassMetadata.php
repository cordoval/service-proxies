<?php

namespace PHPPeru;

class ServiceClassMetadata implements ClassMetadataInterface
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
        return $this->serviceClassName;
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
}