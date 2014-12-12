<?php

class morException extends Exception {
    protected $code, $context;

    protected $http_errors = array(
        '403' => "403 Forbidden",
        '404' => "404 Document not found"
    );

    public function __construct($message = null, $code = null, $previous = null, $context = null) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getErrorPage() {
        if (array_search($this->code, array_keys($this->http_errors)) !== false) {
            header("HTTP/1.1 " . $this->http_errors[$this->code]);
        }

        return misc::errJSON($this->getMessage(), $this->context);
    }
}
