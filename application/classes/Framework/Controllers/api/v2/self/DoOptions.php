<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.04.15
 * Time: 14:12
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\ORM\Exceptions\ORMException;
use Objects\Options;

class DoOptions implements Controller {
    public function doPost(HttpPost $post, AuthUserModel $userModel, JsonResponse $response) {

        /** @var Options $object */

        $object = Options::getByID($userModel->getID())->getOrElseThrow(
            new ControllerException("Options could not be applied for you")
        );

        $post->getArrayParameter("options")->then(function ($options) use ($object) {

            foreach ($options as $key => $option) {
                try {
                    $object->setProperty($key, $option);
                } catch (ORMException $e) {
                    throw new ControllerException(sprintf("Option '" . $key . "' is not applicable"));
                }
            }

            $object->save();

        });

        $response->setData($object);

    }
} 