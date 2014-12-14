<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\Beans\StreamBean;
use MVC\Controller;
use MVC\Services\Database;
use MVC\Services\HttpResponse;

class test extends Controller {
    public function doPost(Database $db, HttpResponse $response) {
        /** @var StreamBean $object */
        $object = $db->fetchOneObject("SELECT * FROM r_streams WHERE sid = 36", [], "Model\\Beans\\StreamBean")
            ->getOrElseNull();
        $response->setData($object->getInfo());
    }
} 