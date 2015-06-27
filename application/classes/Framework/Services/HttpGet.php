<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 17:50
 */

namespace Framework\Services;

use Framework\Injector\Injectable;
use Framework\Services\Locale\I18n;
use Framework\View\Errors\View400Exception;
use Tools\Optional;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class HttpGet
 * @package MVC\Services
 */
class HttpGet extends HttpRequestAdapter implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @param string $key
     * @return Option
     */
    public function getParam($key) {
        $value = FILTER_INPUT(INPUT_GET, $key);
        return is_null($value) ? Option::None() : Option::Some($value);
    }

    /**
     * @param $key
     * @return Option
     */
    public function getArray($key) {
        $value = FILTER_INPUT_ARRAY(INPUT_GET, [
            $key => ["flags"  => FILTER_REQUIRE_ARRAY]
        ]);
        return is_array($value) ? Option::Some($value) : Option::None();
    }

    public function getParameter($key, $filter = FILTER_DEFAULT, $args = null) {
        if (FILTER_INPUT(INPUT_GET, $key) !== null) {
            return Optional::ofEmpty(FILTER_INPUT(INPUT_GET, $key, $filter, $args));
        } else {
            return RouteParams::getInstance()->getParameter($key);
        }
    }

    public function getRequired($key, $filter = FILTER_DEFAULT, $args = null) {
        return $this->getParameter($key, $filter, $args)
            ->getOrElseThrow($this->getException($key));
    }

    public function getArrayParameter($key, $filter = FILTER_DEFAULT) {
        $array = FILTER_INPUT_ARRAY(INPUT_GET, [
            $key => [
                "filter" => $filter,
                "flags"  => FILTER_REQUIRE_ARRAY
            ]
        ]);
        return Optional::ofArray($array[$key]);
    }

    public function getArrayRequired($key, $definition = null) {
        return $this->getArrayParameter($key, $definition)
            ->getOrElseThrow($this->getException($key));
    }

    private function getException($key) {
        return new View400Exception(I18n::tr("ERROR_NO_ARGUMENT_SPECIFIED", [ $key ]));
    }

}