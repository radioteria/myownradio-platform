<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:36
 */

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use REST\Streams;
use Tools\Optional\Option;

class DoGetBookmarks implements Controller {

    public function doGet(Option $offset, Streams $streams, AuthUserModel $model) {

        return $streams->getBookmarksByUser($model, $offset->orZero());

    }

} 