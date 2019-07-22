<?php declare(strict_types=1);

namespace Deleu\Sensor;

use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;

final class Tap
{
    private $target;

    private $sensor;

    public function __construct(object $target, Sensor $sensor)
    {
        $this->target = $target;
        $this->sensor = $sensor;
    }

    public function listen(array $methods)
    {
        $factory = new AccessInterceptorScopeLocalizerFactory();

        $before = $after = [];

        foreach ($methods as $method) {
            $before[$method] = $this->sensor->before($method);
            $after[$method] = $this->sensor->after($method);
        }

        return $factory->createProxy($this->target, $before, $after);
    }
}