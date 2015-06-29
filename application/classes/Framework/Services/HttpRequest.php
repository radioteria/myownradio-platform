<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:54
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Framework\Object;
use Framework\Services\Locale\I18n;
use Framework\View\Errors\View400Exception;
use http\Env;
use Tools\Optional\Option;
use Tools\Singleton;

class HttpRequest implements Injectable {

    use Singleton, Object;

    function __construct() {
    }

    /**
     * @param string $key
     * @return Option
     */
    public function getHeader($key) {
        return Option::ofNullable(Env::getRequestHeader($key));
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->filterInputServer("REQUEST_METHOD");
    }

    /**
     * @return mixed
     */
    public function getServerAddress() {
        return $this->filterInputServer("SERVER_ADDR");
    }

    /**
     * @return mixed
     */
    public function getServerName() {
        return $this->filterInputServer("SERVER_NAME");
    }

    /**
     * @return mixed
     */
    public function getServerProtocol() {
        return $this->filterInputServer("SERVER_PROTOCOL");
    }

    /**
     * @return mixed
     */
    public function getRequestTime() {
        return $this->filterInputServer("REQUEST_TIME");
    }

    /**
     * @return Option
     */
    public function getLanguage() {
        return Option::ofDeceptive(substr(@filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), 0, 2));
    }

    /**
     * @return mixed
     */
    public function getQueryString() {
        return $this->filterInputServer("QUERY_STRING");
    }

    /**
     * @return Option
     */
    public function getHttpAccept() {
        return Option::ofNullable($this->filterInputServer("HTTP_ACCEPT"));
    }

    /**
     * @return Option
     */
    public function getHttpHost() {
        return Option::ofNullable($this->filterInputServer("HTTP_HOST"));
    }

    /**
     * @return Option
     */
    public function getHttpReferer() {
        return Option::ofNullable($this->filterInputServer("HTTP_REFERER"));
    }

    /**
     * @return Option
     */
    public function getHttpUserAgent() {
        return Option::ofNullable($this->filterInputServer("HTTP_USER_AGENT"));
    }

    /**
     * @return Option
     */
    public function getHttps() {
        return Option::ofNullable($this->filterInputServer("HTTPS"));
    }

    /**
     * @return mixed
     */
    public function getRemoteAddress() {
        return $this->filterInputServer("HTTP_X_REAL_IP")
            ? $this->filterInputServer("HTTP_X_REAL_IP")
            : $this->filterInputServer("REMOTE_ADDR");
    }

    /**
     * @return mixed
     */
    public function getRemotePort() {
        return $this->filterInputServer("REMOTE_PORT");
    }

    /**
     * @return mixed
     */
    public function getRequestUri() {
        return $this->filterInputServer("REQUEST_URI");
    }

    /**
     * @param string $param
     * @return mixed
     */
    private function filterInputServer($param) {
        return FILTER_INPUT(INPUT_SERVER, $param);
    }

    /**
     * @param $key
     * @return View400Exception
     */
    private function getException($key) {
        return new View400Exception(I18n::tr("ERROR_NO_ARGUMENT_SPECIFIED", [ $key ]));
    }
} 