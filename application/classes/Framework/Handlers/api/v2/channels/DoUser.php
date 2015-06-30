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
use Framework\Exceptions\Auth\NoUserByLoginException;
use Framework\Services\Http\HttpGet;
use Objects\User;
use Tools\Optional\Filter;

class DoUser implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection) {
        $key    = $get->getOrError("key");
        $offset = $get->get("offset")->filter(Filter::isNumber())
                      ->orZero();
        $limit  = $get->get("limit")->filter(Filter::isNumber())
                      ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        /** @var User $user */
        $user = User::getByFilter("FIND_BY_KEY", [":key" => $key])
            ->orThrow(NoUserByLoginException::class, $key);

        return [
            "user" => $user->toRestFormat(),
            "channels" => $collection->getChannelsListByUser($user->getId(), $offset, $limit)
        ];
    }
} 