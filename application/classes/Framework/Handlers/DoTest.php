<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Framework\Models\UserModel;
use Framework\Services\JsonResponse;
use Tools\Optional\Consumer;
use Tools\Optional\Filter;
use Tools\Optional\Option;
use Tools\Optional\Transform;

class DoTest extends ControllerImpl {

    public function doGet(Option $id, JsonResponse $response) {

        $id->filter(Filter::isNumber())
            ->orThrow(ControllerException::class, "Hello, World!")
            ->map(Transform::newInstance(UserModel::class))
            ->map(Transform::method("toRestFormat"))
            ->map(Transform::key("name"))
            ->then(Consumer::write());


//            ->flatMap(Transform::call(User::class, "getById"))
//            ->orThrow(UserNotFoundException::class)
//            ->then(RegistrationSuccessfulPublisher::send())
//            ->map(Transform::method("jsonSerialize"))
//            ->map(Template::map("hello.tmpl"))
//            ->then(Consumer::write());

    }

}

