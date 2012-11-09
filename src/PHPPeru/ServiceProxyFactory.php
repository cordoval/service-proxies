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
        $this->proxyDir     = $proxyDir;
        $this->proxyNs      = $proxyNamespace;
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
    public function getProxy($className, $identifier)
    {
        $fqn = ClassUtils::generateProxyClassName($className, $this->proxyNs);

        if ( ! class_exists($fqn, false)) {
            $generator = $this->getProxyGenerator();
            $fileName = $generator->getProxyFileName($className);
            $classMetadata = new ServiceClassMetaData($className, $identifier);
            $generator->generateProxyClass($classMetadata);

            require $fileName;
        }

        $entityPersister = $this->uow->getEntityPersister($className);

        $initializer = function (Proxy $proxy) use ($entityPersister, $identifier) {
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

            if (null === $entityPersister->load($identifier, $proxy)) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
        };

        $cloner = function (Proxy $proxy) use ($entityPersister, $identifier) {
            if ($proxy->__isInitialized()) {
                return;
            }

            $proxy->__setInitialized(true);
            $proxy->__setInitializer(function () {});
            $class = $entityPersister->getClassMetadata();
            $original = $entityPersister->load($identifier);

            if (null === $original) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }

            foreach ($class->getReflectionClass()->getProperties() as $reflectionProperty) {
                $propertyName = $reflectionProperty->getName();

                if ($class->hasField($propertyName) || $class->hasAssociation($propertyName)) {
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($proxy, $reflectionProperty->getValue($original));
                }
            }
        };

        return new $fqn($initializer, $cloner, $identifier);
    }

    /**
     * Generates proxy classes for all given classes.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata[] $classes The classes (ClassMetadata instances)
     *                                                                      for which to generate proxies.
     * @param string $proxyDir The target directory of the proxy classes. If not specified, the
     *                      directory configured on the Configuration of the EntityManager used
     *                      by this factory is used.
     * @return int Number of generated proxies.
     */
    public function generateProxyClasses(array $classes, $proxyDir = null)
    {
        $generated = 0;

        foreach ($classes as $class) {
            /* @var $class \Doctrine\ORM\Mapping\ClassMetadataInfo */
            if ($class->isMappedSuperclass || $class->getReflectionClass()->isAbstract()) {
                continue;
            }

            $generator = $this->getProxyGenerator();

            $proxyFileName = $generator->getProxyFileName($class->getName(), $proxyDir);
            $generator->generateProxyClass($class, $proxyFileName);
            $generated += 1;
        }

        return $generated;
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
            $this->proxyGenerator = new ProxyGenerator($this->proxyDir, $this->proxyNs);
            $this->proxyGenerator->setPlaceholder('<baseProxyInterface>', 'Doctrine\ORM\Proxy\Proxy');
        }

        return $this->proxyGenerator;
    }
}