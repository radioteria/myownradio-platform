<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\PlaylistTracksModel;
use MVC\Controller;
use MVC\Services\JsonResponse;

//use Tools\String;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {


//        $string = new String("Hello, World! Давай, до свиданья!");

        $ptm = PlaylistTracksModel::getInstance(36);

        //$query = DBQuery::getInstance()->selectFrom("r_tracks")->limit(50)->offset(100);

        //$response->setData($ptm->getRandom());

    }

} 