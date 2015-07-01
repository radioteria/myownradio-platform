<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 13:58
 */

namespace Business\Forms;


use Framework\Services\Http\HttpPost;
use Tools\Optional\Option;

abstract class HttpForm {

    public function __construct() {
        /** @var \ReflectionProperty $property */
        foreach ((new \ReflectionClass($this))->getProperties() as $property) {
            if ($property->isStatic())
                continue;

            $property->setAccessible(true);
            $property->setValue($this, $this->getField($property->getName()));
        }
        $this->validate();
    }

    /**
     * @param $key
     * @return mixed
     */
    function getField($key) {

        return HttpPost::getInstance()->getOrError($key);

    }

    /**
     * @return Option
     */
    function wrap() {

        return Option::Some($this);

    }

    /**
     * @throws \Exception
     */
    abstract function validate();

}