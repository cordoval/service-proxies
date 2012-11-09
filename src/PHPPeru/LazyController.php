<?php

namespace PHPPeru;

use Pimple\Pimple;

class LazyController
{
    protected $container;

    public function __construct(Pimple $pimple)
    {
        $this->container = $pimple;
    }

    public function lightAction()
    {
        return 'hello world!';
    }

    public function heavyAction()
    {
        /** @var $heavyObject HeavyObject */
        $heavyObject = $this->container['heavy_object'];

        return $heavyObject->iSayHello();
    }
}