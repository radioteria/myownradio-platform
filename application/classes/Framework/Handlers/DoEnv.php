<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 12:36
 */

namespace Framework\Handlers;

use Framework\Controller;

class DoEnv implements Controller
{
    public function doGet()
    {
        header('Content-Type: application/json');
        echo json_encode($_ENV);
    }
}
