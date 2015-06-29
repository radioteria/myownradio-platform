<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 17:01
 */

namespace Framework\Handlers\api\v2\track;


use Framework\Controller;
use Framework\Models\TracksModel;
use Framework\Services\JsonResponse;

/**
 * Class DoDelete
 * @package MVC\Controllers\api\v2\track
 */
class DoDelete implements Controller {

    public function doPost($track_id, TracksModel $model, JsonResponse $response) {

        // Delete tracks from streams if appears
        $model->deleteFromStreams($track_id);

        // Delete tracks from library
        $model->delete($track_id);

    }

} 