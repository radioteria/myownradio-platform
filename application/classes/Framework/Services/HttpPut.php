<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 01.01.15
 * Time: 14:44
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Framework\Services\Locale\I18n;
use Framework\View\Errors\View400Exception;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpPut extends HttpRequestAdapter implements SingletonInterface, Injectable {

    use Singleton;

    private $data;

    function __construct() {
        parse_str(file_get_contents("php://input"), $this->data);
    }

    public function getParameter($key) {
        return Optional::ofEmpty(@$this->data[$key]);
    }

    public function getRequired($key) {
        return $this->getParameter($key)
            ->getOrElseThrow($this->getException($key));
    }

    private function getException($key) {
        return new View400Exception(I18n::tr("ERROR_NO_ARGUMENT_SPECIFIED", ["arg" => $key]));
    }

} 