<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:05
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Framework\Services\Validators\UserValidator;
use Objects\User;

class DoUser implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, JsonResponse $response, UserValidator $validator) {
        $key = $get->getRequired("key");
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse(0);
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)
            ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        /** @var User $user */
        $user = User::getByFilter("FIND_BY_KEY", [":key" => $key])
            ->getOrElseThrow(ControllerException::noUser($key));

        $response->setData([
            "user" => $user->toRestFormat(),
            "channels" => $collection->getChannelsListByUser($user->getId(), $offset, $limit)
        ]);
    }
} 