<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.12.14
 * Time: 16:26
 */

namespace Framework;


use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Services\HttpRequest;
use Tools\Singleton;
use Tools\SingletonInterface;

class Context implements SingletonInterface, Injectable {
    use Singleton;

    private $streamID;

    /** @var \Tools\Optional */
    private $offset;
    private $limit;
    private $filter;

    function __construct() {
        $this->offset = $this->getParameters()->getParameter("offset");
        $this->limit  = $this->getParameters()->getParameter("limit");
        $this->filter = $this->getParameters()->getParameter("filter");
    }

    public function getStreamID() {
        return $this->getParameters()
            ->getParameter("stream_id")->getOrElseThrow(ControllerException::noArgument("stream_id"));
    }

    public function getLimit() {
        return $this->limit->getOrElseNull();
    }

    public function getOffset() {
        return $this->offset->getOrElseNull();
    }

    public function getFilter() {
        return $this->filter->getOrElseNull();
    }

    private function getParameters() {
        $request = HttpRequest::getInstance();
        if ($request->getMethod() == "POST") {
            return $request->getPost();
        } else {
            return $request->getParameters();
        }
    }
} 