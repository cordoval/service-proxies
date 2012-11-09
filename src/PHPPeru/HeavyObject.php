<?php

namespace PHPPeru;

class HeavyObject
{
    protected $items;

    public function _construct()
    {
        $this->items = array();

        foreach(range(1,10000) as $value) {
            $this->items[] = $value;
        }
    }

    public function iSayHello()
    {
        return array_rand($this->items) . ': hello!';
    }
}
