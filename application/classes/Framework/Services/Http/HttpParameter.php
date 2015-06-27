<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 14:40
 */

namespace Framework\Services\Http;


use Framework\Injector\Injectable;
use Tools\Functional\MapSupport;
use Tools\Functional\Sequence;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpParameter extends MapSupport implements SingletonInterface, Injectable {

    use Singleton;

    /** @var MapSupport[] Http parameters providers */
    private $sources;

    public function __construct() {
        $this->sources = new Sequence();
        $this->sources->push(HttpGet::getInstance());
        $this->sources->push(HttpPut::getInstance());
        $this->sources->push(HttpPost::getInstance());
        $this->sources->push(HttpRouteMap::getInstance());
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isDefined($key) {
        $isDefined = function (MapSupport $o) use (&$key) {
            return $o->isDefined($key);
        };
        return $this->sources->firstMatching($isDefined)->nonEmpty();
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getValue($key) {
        $getter = function (MapSupport $support) use (&$key) {
            return $support->get($key);
        };
        $reduce = function (Option $a, Option $b) {
            return $a->orElse($b);
        };
        return $this->sources->map($getter)->reduce($reduce)->get();
    }

}