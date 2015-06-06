<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 6/6/15
 * Time: 8:57 PM
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Framework\Services\Locale\I18n;
use Framework\View\Errors\View400Exception;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpParams extends HttpRequestAdapter implements SingletonInterface, Injectable {

    use Singleton;

    private function getParameter($key, $filter = FILTER_DEFAULT, $args = null) {
        $value = FILTER_INPUT(INPUT_POST, $key, $filter) ?: FILTER_INPUT(INPUT_GET, $key, $filter) ?: RouteParams::getInstance()->get($key);
        return Optional::ofNullable($value);
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