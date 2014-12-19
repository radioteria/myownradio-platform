<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 17:01
 */

namespace MVC\Controllers\api\v2\track;


use Model\TracksModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

/**
 * Class DoDelete
 * @package MVC\Controllers\api\v2\track
 */
class DoDelete extends Controller {

    public function doPost(HttpPost $post, TracksModel $model) {

        $id = $post->getParameter("id")
            ->getOrElseThrow(ControllerException::noArgument("id"));

        // Delete tracks from streams where they appears
        $model->deleteFromStreams($id);

        // Delete tracks from service
        $model->delete($id);

    }

} 