<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 14:35
 */

namespace Framework\Services\Http;


use Framework\Injector\Injectable;
use Tools\Functional\MapSupport;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpRouteMap extends MapSupport implements SingletonInterface, Injectable {

    use Singleton;

    private $map = [];

    /**
     * @param string $key
     * @return bool
     */
    public function isDefined($key) {
        return array_key_exists($key, $this->map);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getValue($key) {
        return $this->map[$key];
    }

    /**
     * @param array $data
     */
    public function setMapData(array $data) {
        $this->map = $data;
    }

}