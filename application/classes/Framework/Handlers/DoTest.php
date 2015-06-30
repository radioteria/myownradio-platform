<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Objects\User;
use Tools\Optional\Filter;
use Tools\Optional\Option;
use Tools\Optional\Transform;

class DoTest extends ControllerImpl {
    public function doGet(Option $id) {

        $userName = $id->map(Transform::toNumber())
            ->reject(Filter::value(1))
            ->flatMap(Transform::call(User::class, "getById"))
            ->map(Transform::method("getName"));


        echo $userName;


    }
}

