<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:05
 */

namespace Framework\Controllers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\UserModel;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Framework\Services\Validators\UserValidator;
use Objects\User;

class DoUser implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, JsonResponse $response, UserValidator $validator) {
        $key = $get->getRequired("key");
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse(0);
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElseNull();

        /** @var User $user */
        $user = User::getByFilter("FIND_BY_KEY", [":key" => $key])->getOrElseThrow(ControllerException::noUser($key));

        $response->setData([
            "user" => $user->toRestFormat(),
            "channels" => $collection->getChannelsListByUser($user->getID(), $offset, $limit)
        ]);
    }
} 