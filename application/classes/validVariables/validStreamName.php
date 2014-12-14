<?php

class validStreamName extends validVariable {
    function __construct($data) {
        $this->variable = InputValidator::streamNameValidator($data);
    }
}
