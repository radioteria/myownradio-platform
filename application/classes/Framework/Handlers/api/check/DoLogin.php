<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 18:25
 */

namespace Framework\Handlers\api\check;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Framework\Preferences;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\ValidatorTemplates;

class DoLogin extends ControllerImpl {
    public function doPost(HttpPost $post, JsonResponse $response, Preferences $preferences) {

        $field = $post->getRequired("field");

        try {

            if (array_search($field, $preferences->get("invalid", "login")->get()) !== false) {

                throw new ControllerException();

            }

            ValidatorTemplates::validateLogin($field);

            $available = true;

        } catch (ControllerException $ex) {

            $available = false;

        }

        $response->setData(["available" => $available]);

    }
} 