<?php

class validStreamName extends validVariable {
    function __construct($data) {
        $this->variable = Validators::streamNameValidator($data);
    }
}
