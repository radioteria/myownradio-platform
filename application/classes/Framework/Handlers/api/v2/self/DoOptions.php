<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.04.15
 * Time: 14:12
 */

namespace Framework\Handlers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\Http\HttpPost;
use Framework\Services\ORM\Exceptions\ORMException;
use Objects\Options;
use Tools\Optional\Filter;

// todo: what is that?
class DoOptions implements Controller {
    public function doPost(HttpPost $post, AuthUserModel $userModel) {

        /** @var Options $object */

        $object = Options::getByID($userModel->getID())->getOrThrow(
            new ControllerException("Options could not be applied for you")
        );

        $post->get("options")->filter(Filter::isArray())
            ->then(function (array $options) use ($object) {

                foreach ($options as $key => $option) {
                    try {
                        $object->setProperty($key, $option);
                    } catch (ORMException $e) {
                        //throw new ControllerException(sprintf("Option '" . $key . "' is not applicable"));
                    }
                }

                $object->save();

            });

        return $object;

    }
} 