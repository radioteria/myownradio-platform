<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.06.15
 * Time: 15:46
 */

namespace Framework\Services\Http;


use Tools\Functional\MapSupport;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpFile extends MapSupport implements SingletonInterface {

    use Singleton;

    /**
     * @param string $key
     * @return bool
     */
    public function isDefined($key) {
        return array_key_exists($key, $_FILES);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getValue($key) {
        return $_FILES[$key];
    }

    /**
     * @return Option
     */
    public function findAny() {
        if (count(array_keys($_FILES)) == 0) {
            return Option::None();
        } else {
            $key = array_keys($_FILES)[0];
            return Option::Some($_FILES[$key]);
        }
    }

    /**
     * @param $callable
     */
    public function each($callable) {
        foreach ($_FILES as $file) {
            $callable($file);
        }
    }

}