<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.05.15
 * Time: 9:30
 */

namespace Business\Validator\Entity;


interface EntityValidator {
    public function validateAllFields();
}