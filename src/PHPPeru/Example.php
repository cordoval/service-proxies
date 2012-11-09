<?php

namespace PHPPeru;

class Example
{
    protected $c;

    public function _construct()
    {
        $this->c = new Pimple();
    }

    public function nastyExample()
    {
        $this->c['my_controller'] = function ($c) {

        };

        $this->c['heavy_object'] = function($c) {

        };
    }
}
