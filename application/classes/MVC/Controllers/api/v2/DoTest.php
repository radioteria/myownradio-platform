<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use MVC\Controller;
use MVC\Services\HttpFile;

class DoTest extends Controller {

    public function doPost(HttpFile $files) {
        $first = $files->getFirstFile();
        $file = $first->get();
        echo gettype($file);
    }

} 