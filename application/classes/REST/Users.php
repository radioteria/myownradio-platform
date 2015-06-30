<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 17:11
 */

namespace REST;


use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\SelectQuery;
use Tools\Common;
use Tools\Folders;
use Tools\Singleton;
use Tools\SingletonInterface;

class Users implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @return SelectQuery
     */
    private function getUsersPrefix() {

        $prefix = (new SelectQuery("mor_users_view"))
            ->select("uid", "name", "permalink", "avatar", "streams_count", "tracks_count", "info", "plan_id",
                "country_id");

        return $prefix;

    }

    public function getUserByID($id, $showLogin = false) {

        $query = $this->getUsersPrefix();

        if ($showLogin) {
            $query->select("login");
        }

        $query->select("tracks_duration", "plan_expires");
        $query->where("uid = :key OR (permalink = :key AND permalink is not null)", [":key" => $id]);
        $user = $query->fetchOneRow()->getOrThrow(ControllerException::noUser("user"));


        $plan_data = DBQuery::getInstance()->selectFrom("mor_plans_view", "plan_id", $user["plan_id"])
            ->fetchOneRow()->orNull();

        $user["plan_data"] = $plan_data;

        $this->processUserRow($user);

        return $user;
    }

    /**
     * @param null|string $filter
     * @param null|int $limit
     * @param null|int $offset
     * @return array
     */
    public function getUsersList($filter = null, $limit = null, $offset = null) {

        $query = $this->getUsersPrefix();

        if (is_numeric($limit)) {
            $query->limit($limit);
        }

        if (is_numeric($offset)) {
            $query->offset($offset);
        }

        if (is_string($filter)) {
            $query->where("MATCH (name) AGAINST (? IN BOOLEAN MODE)", [Common::searchQueryFilter($filter)]);
        }

        return $query->fetchAll(null, function ($row) {
            $this->processUserRow($row);
            return $row;
        });

    }

    /**
     * @param $row
     */
    private function processUserRow(&$row) {

        $row['avatar_url'] = Folders::getInstance()->genAvatarUrl($row['avatar']);
        $row['key'] = empty($row['permalink']) ? $row['uid'] : $row['permalink'];

    }

} 