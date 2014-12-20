<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 17:01
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Model\TracksModel;

/**
 * Class DoDelete
 * @package MVC\Controllers\api\v2\track
 */
class DoDelete implements Controller {

    public function doPost(HttpPost $post, TracksModel $model) {

        $id = $post->getParameter("id")
            ->getOrElseThrow(ControllerException::noArgument("id"));

        // Delete tracks from streams where they appears
        $model->deleteFromStreams($id);

        // Delete tracks from service
        $model->delete($id);

    }

} 