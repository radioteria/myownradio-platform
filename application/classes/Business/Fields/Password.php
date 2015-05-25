<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.05.15
 * Time: 15:58
 */

namespace Business\Fields;


use Business\Validator\Entity\PasswordValidator;

class Password {

    private $password;

    function __construct($password) {
        PasswordValidator::validate($password);
        $this->password = $password;
    }

    function matches($hash) {
        return password_verify($this->password, $hash);
    }

    function hash() {
        return password_hash($this->password, PASSWORD_DEFAULT);
    }

    function getPassword() {
        return $this->password;
    }

    function __toString() {
        return "{p:{$this->password},h:{$this->hash()}}";
    }

} 