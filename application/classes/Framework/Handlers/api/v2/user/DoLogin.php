<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 9:50
 */

namespace Framework\Handlers\api\v2\user;


use Business\Forms\LoginForm;
use Framework\Controller;
use Framework\Events\LoginSuccessfulPublisher;
use Framework\Models\UsersModel;
use Framework\Services\JsonResponse;
use Tools\Optional\Mapper;

class DoLogin implements Controller {

    public function doPost(JsonResponse $response, LoginForm $form, UsersModel $users) {

        $form ->wrap()
              ->then(LoginSuccessfulPublisher::send())
              ->then(Mapper::call($users, "authorizeByLoginForm"));

    }

}