<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:54
 */

namespace MVC\Services;


use Tools\Optional;
use Tools\Singleton;

class HttpRequest {
    use Singleton, Injectable;

    private $headers;

    function __construct() {
        $this->headers = http_get_request_headers();
    }

    /**
     * @param string $key
     * @return Optional
     */
    public function getHeader($key) {
        return Optional::ofNull(@$this->headers[$key]);
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
     * @return Optional
     */
    public function getLanguage() {
        return Optional::ofDeceptive(substr(@filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), 0, 2));
    }

    /**
     * @return mixed
     */
    public function getQueryString() {
        return $this->filterInputServer("QUERY_STRING");
    }

    /**
     * @return Optional
     */
    public function getHttpAccept() {
        return Optional::ofNull($this->filterInputServer("HTTP_ACCEPT"));
    }

    /**
     * @return Optional
     */
    public function getHttpHost() {
        return Optional::ofNull($this->filterInputServer("HTTP_HOST"));
    }

    /**
     * @return Optional
     */
    public function getHttpReferer() {
        return Optional::ofNull($this->filterInputServer("HTTP_REFERER"));
    }

    /**
     * @return Optional
     */
    public function getHttpUserAgent() {
        return Optional::ofNull($this->filterInputServer("HTTP_USER_AGENT"));
    }

    /**
     * @return Optional
     */
    public function getHttps() {
        return Optional::ofNull($this->filterInputServer("HTTPS"));
    }

    /**
     * @return mixed
     */
    public function getRemoteAddress() {
        return $this->filterInputServer("REMOTE_ADDR");
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
     * @return HttpGet
     */
    public function getParameters() {
        return HttpGet::getInstance();
    }

    /**
     * @return HttpPost
     */
    public function getPost() {
        return HttpPost::getInstance();
    }

    /**
     * @param string $param
     * @return mixed
     */
    private function filterInputServer($param) {
        return FILTER_INPUT(INPUT_SERVER, $param);
    }
} 