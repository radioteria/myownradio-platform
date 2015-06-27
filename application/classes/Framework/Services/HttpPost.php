<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:26
 */

namespace Framework\Services;

use Framework\Injector\Injectable;
use Framework\Services\Locale\I18n;
use Framework\View\Errors\View400Exception;
use Tools\Optional;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpPost extends HttpRequestAdapter implements Injectable, SingletonInterface {

    use Singleton;

    /**
     * @param string $key
     * @return Option
     */
    public function getParam($key) {
        if (is_null(FILTER_INPUT(INPUT_GET, $key))) {
            return Option::None();
        } else {
            return Option::Some(FILTER_INPUT(INPUT_GET, $key));
        }
    }

    public function getParameter($key, $filter = FILTER_DEFAULT, $options = null) {
        return Optional::ofEmpty(FILTER_INPUT(INPUT_POST, $key, $filter, $options));
    }

    public function getArrayParameter($key, $filter = FILTER_DEFAULT) {
        $array = FILTER_INPUT_ARRAY(INPUT_POST, [
            $key => [
                "filter" => $filter,
                "flags"  => FILTER_REQUIRE_ARRAY
            ]
        ]);
        return Optional::ofArray($array[$key]);
    }

    public function getRequired($key, $filter = FILTER_DEFAULT, $options = null) {
        return $this->getParameter($key, $filter, $options)
            ->getOrElseThrow($this->getException($key));
    }

    public function getArrayRequired($key, $definition = null) {
        return $this->getArrayParameter($key, $definition)
            ->getOrElseThrow($this->getException($key));
    }

    private function getException($key) {
        return new View400Exception(I18n::tr("ERROR_NO_ARGUMENT_SPECIFIED", [ $key ]));
    }

}