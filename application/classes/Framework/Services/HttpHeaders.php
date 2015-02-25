<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 10.02.15
 * Time: 9:51
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpHeaders extends HttpRequestAdapter implements Injectable, SingletonInterface {

    use Singleton;

    private $headers;

    function __construct() {
        $this->headers = getallheaders();
    }

    public function getParameter($key) {
        return Optional::ofEmpty(@$this->headers[$key]);
    }

} 