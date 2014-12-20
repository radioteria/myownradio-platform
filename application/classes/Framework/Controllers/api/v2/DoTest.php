<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\Services\HttpFile;

class DoTest implements Controller {

    public function doPost(HttpFile $files) {
        $first = $files->getFirstFile();
        $file = $first->get();
        echo gettype($file);
    }

} 