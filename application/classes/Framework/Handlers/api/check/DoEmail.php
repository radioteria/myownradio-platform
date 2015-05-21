<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 19:42
 */

namespace Framework\Handlers\api\check;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\ValidatorTemplates;

class DoEmail extends ControllerImpl {
    public function doPost(HttpPost $post, JsonResponse $response, DBQuery $query) {
        $field = $post->getRequired("field");
        try {
            ValidatorTemplates::validateEmail($field);
            $available = true;
        } catch (ControllerException $ex) {
            $available = false;
        }
        $response->setData(["available" => $available]);
    }
} 