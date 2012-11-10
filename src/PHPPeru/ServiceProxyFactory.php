<?php

namespace PHPPeru;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\ProxyGenerator;

/**
 * This factory is used to create proxy objects for services at runtime.
 *
 * @author Luis Cordova Sosa <cordoval@gmail.com>
 * @author Fernando Paredes Garcia <fernando@develcuy.com>
 */
class ServiceProxyFactory
{
    /**
     * @var ProxyGenerator the proxy generator responsible for creating the proxy classes/files.
     */
    private $proxyGenerator;

    /**
     * @var string
     */
    private $proxyNamespace;

    /**
     * @var string
     */
    private $proxyDir;

    /**
     * Initializes a new instance of the <tt>ProxyFactory</tt>.
     *
     * @param string $proxyDir        The directory to use for the proxy classes. It must exist.
     * @param string $proxyNamespace  The namespace to use for the proxy classes.
     */
    public function __construct($proxyDir, $proxyNamespace)
    {
        $this->proxyDir = $proxyDir;
        $this->proxyNamespace = $proxyNamespace;
    }

    /**
     * Gets a reference proxy instance for the service of the given type and identified by
     * the given identifier.
     *
     * @param  string $className
     * @param  mixed  $identifier
     *
     * @return object
     */
    public function getProxy($className, $identifier, $container)
    {
        $fqn = ClassUtils::generateProxyClassName($className, $this->proxyNamespace);

        if ( ! class_exists($fqn, false)) {
            $generator = $this->getProxyGenerator();
            $fileName = $generator->getProxyFileName($className);
            $classMetadata = new ServiceClassMetadata($className, $identifier);
            $generator->generateProxyClass($classMetadata);

            require $fileName;
        }

        $initializer = function (Proxy $proxy) use ($container, $identifier) {
            $proxy->__setInitializer(function () {});
            $proxy->__setCloner(function () {});

            if ($proxy->__isInitialized()) {
                return;
            }

            $properties = $proxy->__getLazyLoadedPublicProperties();

            foreach ($properties as $propertyName => $property) {
                if (!isset($proxy->$propertyName)) {
                    $proxy->$propertyName = $properties[$propertyName];
                }
            }

            $proxy->__setInitialized(true);

            if (method_exists($proxy, '__wakeup')) {
                $proxy->__wakeup();
            }


            // we want to avoid using reflection for performance
            $heavyObject = $container[reset($identifier)];
            $reflClass = new \ReflectionClass($heavyObject);

            foreach ($reflClass->getProperties() as $reflProperty) {
                $reflProperty->setAccessible(true);
                $reflProperty->setValue($proxy, $reflProperty->getValue($heavyObject));
            }
        };

        $cloner = function (Proxy $proxy) {
            if ($proxy->__isInitialized()) {
                return;
            }

            $proxy->__setInitialized(true);
            $proxy->__setInitializer(function (){});

            return;
        };

        return new $fqn($initializer, $cloner, $identifier);
    }

    /**
     * @param ProxyGenerator $proxyGenerator
     */
    public function setProxyGenerator(ProxyGenerator $proxyGenerator)
    {
        $this->proxyGenerator = $proxyGenerator;
    }

    /**
     * @return ProxyGenerator
     */
    public function getProxyGenerator()
    {
        if (null === $this->proxyGenerator) {
            $this->proxyGenerator = new ProxyGenerator($this->proxyDir, $this->proxyNamespace);
            $this->proxyGenerator->setPlaceholder('<wakeupImpl>', '');
            $this->proxyGenerator->setPlaceholder('<cloneImpl>', '');
            $this->proxyGenerator->setPlaceholder('<sleepImpl>', '');
        }

        return $this->proxyGenerator;
    }
}