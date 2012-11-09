<?php

namespace PHPPeru;

use Pimple;

class Examples
{
    protected $c;

    public function __construct()
    {
        $this->c = new Pimple();

        $this->c['heavy_object'] = function($c) {
            return new HeavyObject();
        };

        $this->c['ioc_controller'] = function ($c) {
            return new IocController($c['heavy_object']);
        };

        $this->c['lazy_controller'] = function ($c) {
            return new LazyController($c);
        };
    }

    public function nastyExample()
    {
        /** @var $controller LazyController */
        $controller = $this->c['lazy_controller'];

        $controller->lightAction();

        /** @var $controller LazyController */
        $controller = $this->c['lazy_controller'];

        $controller->heavyAction();
    }

    public function goodExample()
    {
        /** @var $controller IocController */
        $controller = $this->c['ioc_controller'];

        $controller->lightAction();

        /** @var $controller IocController */
        $controller = $this->c['ioc_controller'];

        $controller->heavyAction();
    }

    public function goodAndFastExample()
    {
        $this->c['heavy_object_proxy'] = function($c) {
            return new HeavyObjectProxy($c, 'heavy_object');
        };

        $this->c['ioc_controller'] = function ($c) {
            return new IocController($c['heavy_object_proxy']);
        };

        /** @var $controller IocController */
        $controller = $this->c['ioc_controller'];

        $controller->lightAction();

        /** @var $controller IocController */
        $controller = $this->c['ioc_controller'];

        $controller->heavyAction();
    }
}
