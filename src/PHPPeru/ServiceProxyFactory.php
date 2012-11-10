<?php

namespace PHPPeru;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

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
            $classMetadata = new ServiceClassMetadata($className, $identifier);
            $generator = $this->getProxyGenerator($classMetadata);
            $fileName = $generator->getProxyFileName($className);
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
    public function getProxyGenerator($classMetadata)
    {
        if (null === $this->proxyGenerator) {
            $methods = $this->generateMethods($classMetadata);
            $this->proxyGenerator = new ProxyGenerator($this->proxyDir, $this->proxyNamespace);
            $this->proxyGenerator->setProxyClassTemplate($this->getWrappedTemplate());
            $this->proxyGenerator->setPlaceholder('<methods>', $methods);
        }

        return $this->proxyGenerator;
    }

    public function getWrappedTemplate()
    {
        return <<<EOT
<?php

namespace <namespace>;

class <proxyClassName> extends <className>
{
    protected \$__wrappedObject__;

    private \$__container__;
    private \$__serviceId__;

    public function __construct(\$container, \$serviceId)
    {
        \$this->__container__ = \$container;
        \$this->__serviceId__ = \$serviceId;
    }

    public function __load()
    {
        if (!\$this->__wrappedObject__) {
            \$this->__wrappedObject__ = \$this->__container__[\$this->__serviceId__];
        }
    }

    <methods>
}
EOT;

    }

    /**
     * Generates decorated methods by picking those available in the parent class
     *
     * @param  ClassMetadata $class
     *
     * @return string
     */
    public function generateMethods(ClassMetadata $class)
    {
        $methods = '';
        $methodNames = array();
        $reflectionMethods = $class->getReflectionClass()->getMethods(\ReflectionMethod::IS_PUBLIC);
        $skippedMethods = array(
            '__sleep'   => true,
            '__clone'   => true,
            '__wakeup'  => true,
            '__get'     => true,
            '__set'     => true,
        );

        foreach ($reflectionMethods as $method) {
            $name = $method->getName();

            if (
                $method->isConstructor()
                || isset($skippedMethods[strtolower($name)])
                || isset($methodNames[$name])
                || $method->isFinal()
                || $method->isStatic()
                || ! $method->isPublic()
            ) {
                continue;
            }

            $methodNames[$name] = true;
            $methods .= "\n" . '    public function ';

            if ($method->returnsReference()) {
                $methods .= '&';
            }

            $methods .= $name . '(';
            $firstParam = true;
            $parameterString = $argumentString = '';
            $parameters = array();

            foreach ($method->getParameters() as $param) {
                if ($firstParam) {
                    $firstParam = false;
                } else {
                    $parameterString .= ', ';
                    $argumentString  .= ', ';
                }

                $paramClass = $param->getClass();

                // We need to pick the type hint class too
                if (null !== $paramClass) {
                    $parameterString .= '\\' . $paramClass->getName() . ' ';
                } elseif ($param->isArray()) {
                    $parameterString .= 'array ';
                }

                if ($param->isPassedByReference()) {
                    $parameterString .= '&';
                }

                $parameters[] = '$' . $param->getName();
                $parameterString .= '$' . $param->getName();
                $argumentString  .= '$' . $param->getName();

                if ($param->isDefaultValueAvailable()) {
                    $parameterString .= ' = ' . var_export($param->getDefaultValue(), true);
                }
            }

            $methods .= $parameterString . ')';
            $methods .= "\n" . '    {' . "\n";

            $methods .= '        call_user_func($this->__initializer__, $this, ' . var_export($name, true) . ', array('
                . implode(', ', $parameters) . '));' . "\n\n";
            $methods .= '        return $this->__wrappedObject__->' . $name . '(' . $argumentString . ');';
            $methods .= "\n" . '    }' . "\n";
        }

        return $methods;
    }

}