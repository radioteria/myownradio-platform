<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 17:01
 */

namespace MVC\Controllers\api\v2\track;


use Model\Destructor;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

/**
 * Class DoDelete
 * @package MVC\Controllers\api\v2\track
 */
class DoDelete extends Controller {

    public function doPost(HttpPost $post, Destructor $destructor) {

        $id = $post->getParameter("id")
            ->getOrElseThrow(ControllerException::noArgument("id"));

        // Delete tracks from streams where they appears
        $destructor->deleteFromStreams($id);

        // Delete tracks from service
        $destructor->deleteTrack($id);

    }

} 