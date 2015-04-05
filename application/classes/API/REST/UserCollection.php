<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:28
 */

namespace API\REST;


use Framework\Injector\Injectable;
use Framework\Services\DB\Query\SelectQuery;
use Tools\Singleton;
use Tools\SingletonInterface;

class UserCollection implements Injectable, SingletonInterface  {
    use Singleton;

    private function getUsersPrefix() {

        $prefix = (new SelectQuery("mor_users_view"))
            ->select("uid", "name", "permalink", "avatar", "streams_count",
                "tracks_count", "info", "plan_id", "country_id");

        return $prefix;

    }
} 