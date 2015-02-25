<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.02.15
 * Time: 11:32
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\View\Errors\View404Exception;

class Do404 implements Controller {
    public function doGet() {
        throw new View404Exception();
    }
} 