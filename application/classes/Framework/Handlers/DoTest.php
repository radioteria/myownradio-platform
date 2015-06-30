<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Framework\Events\RegistrationSuccessfulPublisher;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\Entity\UserNotFoundException;
use Framework\Template;
use Objects\User;
use Tools\Optional\Consumer;
use Tools\Optional\Filter;
use Tools\Optional\Option;
use Tools\Optional\Transform;

class DoTest extends ControllerImpl {

    public function doGet(Option $id) {

        $id ->filter(Filter::isNumber())
            ->orThrow(ControllerException::class, "Hello, World!")
            ->flatMap(Transform::call(User::class, "getById"))
            ->orThrow(UserNotFoundException::class)
            ->then(RegistrationSuccessfulPublisher::send())
            ->map(Transform::method("jsonSerialize"))
            ->map(Template::map("hello.tmpl"))
            ->then(Consumer::write());

    }

}

