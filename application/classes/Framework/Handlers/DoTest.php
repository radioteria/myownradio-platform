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
use Framework\Exceptions\Entity\UserNotFoundException;
use Framework\Template;
use Objects\User;
use Tools\Optional\Consumer;
use Tools\Optional\Filter;
use Tools\Optional\Mapper;
use Tools\Optional\Option;

class DoTest extends ControllerImpl {

    public function doGet(Option $id) {

        $id ->orThrow(ControllerException::class, "Parameter id is not set!")
            ->filter(Filter::isNumber())
            ->orThrow(ControllerException::class, "Parameter id must be a valid number!")
            ->flatMap(Mapper::call(User::class, "getById"))
            ->orThrow(UserNotFoundException::class)
            ->map("::jsonSerialize")
            ->map(Template::map("hello.tmpl"))
            ->then(Consumer::write());

    }

}

