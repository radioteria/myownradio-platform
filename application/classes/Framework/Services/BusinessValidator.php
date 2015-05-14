<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 14.05.15
 * Time: 15:37
 */

namespace Framework\Services;


use Framework\Services\DB\DBQuery;

class BusinessValidator extends Validator {

    const PERMALINK_REGEXP_PATTERN = "~(^[a-z0-9\\-]*$)~";
    const LOGIN_PATTERN = "~^[0-9a-z\\_]+$~";

    function login() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return preg_match(self::LOGIN_PATTERN, $value); });
        return $copy;
    }

    function permalink() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_null($value) || preg_match(self::PERMALINK_REGEXP_PATTERN, $value); });
        return $copy;
    }

    function checkPermalinkForUser($user_id) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($user_id) {

            $dbq = DBQuery::getInstance();

            $query = $dbq->selectFrom("r_users")
                ->where("(permalink = :key OR uid = :key) AND (uid != :user)", [
                    ":key" => $value,
                    ":user" => $user_id
                ]);

            return count($query) == 0;

        });
        return $copy;
    }

    function checkPermalinkForStream($stream_id) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($stream_id) {

            $dbq = DBQuery::getInstance();

            $query = $dbq->selectFrom("r_streams")
                ->where("(permalink = :key OR sid = :key) AND (sid != :stream)", [
                    ":key" => $value,
                    ":stream" => $stream_id
                ]);

            return count($query) == 0;

        });

        return $copy;
    }

} 