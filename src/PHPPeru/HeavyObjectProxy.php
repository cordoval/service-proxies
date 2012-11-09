<?php

namespace PHPPeru;

class HeavyObjectProxy
{
    protected $wrappedHeavyObject;

    public function __construct()
    {

    }

    public function iSayHello()
    {
        if(!$this->wrappedHeavyObject) {
            $this->wrappedHeavyObject = new HeavyObject();
        }

        return $this->wrappedHeavyObject->iSayHello();
    }
}
