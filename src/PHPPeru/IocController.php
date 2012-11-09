<?php

namespace PHPPeru;

class IocController
{
    protected $heavyObject;

    public function __construct(HeavyObject $heavyObject)
    {
        $this->heavyObject = $heavyObject;
    }

    public function lightAction()
    {
        return 'hello world!';
    }

    public function heavyAction()
    {
        return $this->heavyObject->iSayHello();
    }
}