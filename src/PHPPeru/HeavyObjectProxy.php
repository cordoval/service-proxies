<?php

namespace PHPPeru;

class HeavyObjectProxy extends HeavyObject
{
    protected $wrappedHeavyObject;

    private $__container__;
    private $__serviceId__;

    public function __construct($container, $serviceId)
    {
        $this->__container__ = $container;
        $this->__serviceId__ = $serviceId;
    }

    public function iSayHello()
    {
        $this->__load();

        return $this->wrappedHeavyObject->iSayHello();
    }

    public function __load()
    {
        if (!$this->wrappedHeavyObject) {
            $this->wrappedHeavyObject = $this->__container__[$this->__serviceId__];
        }
    }
}
