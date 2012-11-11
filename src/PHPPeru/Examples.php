<?php

namespace PHPPeru;

use Doctrine\Common\Proxy\ProxyGenerator;
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

    public function goodAndFastAndAutomaticExample()
    {
        $this->c['cache_dir'] = __DIR__.'/../../cache';
        $this->c['phpperu_namespace'] = 'PHPPeru';

        $this->c['proxy_factory'] = function($c) {
            return new ServiceProxyFactory($c['cache_dir'], $c['phpperu_namespace']);
        };

        $this->c['heavy_object_proxy'] = function($c) {
            return $c['proxy_factory']->getProxy("PHPPeru\\HeavyObject", array("heavy_object"), $c);
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

    public function pimpleRefactorExample()
    {
        $c = $this->c;

        $this->buildService('heavy_object', function($c) { return new HeavyObject(); }, true, $c);

        $this->c['ioc_controller'] = function ($c) {
            return new IocController($c['heavy_object']);
        };

        /** @var $controller IocController */
        $controller = $this->c['ioc_controller'];

        $controller->lightAction();

        /** @var $controller IocController */
        $controller = $this->c['ioc_controller'];

        $controller->heavyAction();
    }

    public function buildService($serviceId, $originalClosure, $proxied = false, $c)
    {
        if ($proxied == true) {
            $c[$serviceId] = $c->share(function ($c) use ($originalClosure, $serviceId) {
                $this->c['cache_dir'] = __DIR__.'/../../cache';
                $this->c['phpperu_namespace'] = 'PHPPeru';
                //$fqcn = get_class(call_user_func($originalClosure, null));
                $c[$serviceId . '_pimple_safe_object'] = $originalClosure;
                $factory = new ServiceProxyFactory($c['cache_dir'], $c['phpperu_namespace']);
                return $factory->getProxy("PHPPeru\\HeavyObject", array($serviceId . '_pimple_safe_object'), $c);
            });
        } else {
            $c[$serviceId] = $c->share($originalClosure);
        }
    }
}
