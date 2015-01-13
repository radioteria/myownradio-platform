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
use Framework\Models\TracksModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

/**
 * Class DoDelete
 * @package MVC\Controllers\api\v2\track
 */
class DoDelete implements Controller {

    public function doPost(HttpPost $post, TracksModel $model, JsonResponse $response) {

        $id = $post->getParameter("track_id")
            ->getOrElseThrow(ControllerException::noArgument("track_id"));

        // Delete tracks from streams if they appears
        $model->deleteFromStreams($id);

        // Delete tracks from service
        //$model->delete($id);

    }

} 