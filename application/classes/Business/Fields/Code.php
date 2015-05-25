<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.05.15
 * Time: 16:42
 */

namespace Business\Fields;
use Framework\Services\DB\Query\SelectQuery;

/**
 * Class Code
 * @package Business\Fields
 */
class Code {

    private $object;

    function __construct($base64) {
        $exception = CodeException::newCodeIncorrect();
        $json = base64_decode($base64);
        if ($json === false) {
            throw $exception;
        }
        $array = json_decode($json, true);
        if ($array === null) {
            throw $exception;
        }
        $this->object = $array;
    }

    function __call($method, $arguments) {
        if (substr($method, 0, 3) !== "get") {
            throw new \BadMethodCallException("Method not found!");
        }
        $key = strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", substr($method, 3)));
        return $this->object[$key];
    }

    public function hasOrError() {
        foreach (func_get_args() as $arg) {
            if (!isset($this->object[$arg])) {
                throw CodeException::newCodeIncorrect();
            }
        }
        return true;
    }

    public function addWhere(SelectQuery $query) {
        foreach ($this->object as $key) {
            $query->where($key, $this->object[$key]);
        }
    }

}