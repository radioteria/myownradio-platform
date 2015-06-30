<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Framework\Exceptions\Entity\UserNotFoundException;
use Framework\Template;
use Objects\User;
use Tools\Optional\Consumer;
use Tools\Optional\Filter;
use Tools\Optional\Option;
use Tools\Optional\Transform;

class DoTest extends ControllerImpl {

    public function doGet(Option $id) {

        $id->filter(Filter::isNumber())
            ->map(Transform::toNumber())
            ->reject(Filter::value(1))
            ->flatMap(Transform::call(User::class, "getById"))
            ->map(Transform::method("jsonSerialize"))
            ->map(Template::map("hello.tmpl"))
            ->orThrow(UserNotFoundException::class)
            ->then(Consumer::write());

    }

}

