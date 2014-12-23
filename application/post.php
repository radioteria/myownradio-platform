<?php

use Framework\Services\Database;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\DB\Query\UpdateQuery;
use Framework\Services\HttpPost;

$request = Framework\Services\HttpPost::getInstance();

$action = $request->getParameter("action")->getOrElseNull();

header("Content-Type: application/json");

switch($action)
{
    case 'list':
        $query = new SelectQuery("r_modules");
        $query->select("name", "alias");
        $query->orderBy("name");
        echo json_encode($query->fetchAll());
        break;

    case 'get':
        $module = HttpPost::getInstance()->getParameter("module")->getOrElseNull();
        $query = new SelectQuery("r_modules");
        $query->where("name", [$module]);
        echo json_encode($query->fetchOneRow()->get());
        break;

    case 'alias':

        $module = application::post("module", NULL, REQ_STRING);
        $alias = application::post("alias", NULL, REQ_STRING);

        $uid = db::query_single_col("SELECT IF_NULL(`uid`, 0) FROM `r_modules` WHERE `name` = ?", array($module));

        if($uid != user::getCurrentUserId() && $uid > 0)
        {
            misc::errorJSON("ERROR_NO_PRIVILEGES");
        }

        $resp = db::query_update("UPDATE `r_modules` SET `alias` = ? WHERE `name` = ?", array($alias, $module));

        echo json_encode(array("result" => $resp));

        break;

    case 'save':

        $post = HttpPost::getInstance();
        $db = Database::getInstance();
        $db->connect();

        $module = $post->getParameter("module")->getOrElseNull();

        $html = $post->getParameter("html")->getOrElseNull();
        $css = $post->getParameter("css")->getOrElseNull();
        $js = $post->getParameter("js")->getOrElseNull();
        $tmpl = $post->getParameter("tmpl")->getOrElseNull();
        $pst = $post->getParameter("post")->getOrElseNull();


        $args = array();

        if(!is_null($html))
        {
            $args[] = "`html` = " . $db->quote($html);
        }

        if(!is_null($css))
        {
            $args[] = "`css` = " .$db->quote($css);
        }

        if(!is_null($js))
        {
            $args[] = "`js` = " . $db->quote($js);
        }

        if(!is_null($tmpl))
        {
            $args[] = "`tmpl` = " . $db->quote($tmpl);
        }

        if(!is_null($pst))
        {
            $args[] = "`post` = " . $db->quote($pst);
        }

        $actionTime = time();

        $args[] = "`modified` = FROM_UNIXTIME({$actionTime})";

        $setters = implode(", ", $args);
        $result = $db->executeUpdate("INSERT INTO `r_modules` SET `name` = ?, `uid` = ?, {$setters} ON DUPLICATE KEY UPDATE {$setters}",
            [$module, 1]);
        echo json_encode(array("save"=>$result));
        break;
}
