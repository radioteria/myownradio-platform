<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 10:33
 */

namespace Framework\Handlers\test;


use Business\Forms\SignUpCompleteForm;
use Framework\Controller;
use Framework\Services\JsonResponse;

class DoRegistration implements Controller {
    public function doPost(JsonResponse $response, SignUpCompleteForm $form) { }
}