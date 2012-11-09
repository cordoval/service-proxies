<?php

namespace PHPPeru;

/**
 * Contract for a Service layer ClassMetadata class to implement.
 *
 * @author  Luis Cordova Sosa <cordoval@gmail.com>
 * @author  Fernando Paredes Garcia <fernando@develcuy.com>
 */
interface ClassMetadataInterface
{
    /**
     * Get fully-qualified class name of this service class.
     *
     * @return string
     */
    function getName();

    /**
     * Gets the service identifier.
     *
     * @return string
     */
    function getIdentifier();

    /**
     * Gets the ReflectionClass instance for this service class.
     *
     * @return \ReflectionClass
     */
    function getReflectionClass();
}
