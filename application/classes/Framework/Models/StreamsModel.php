<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:34
 */

namespace Framework\Models;


use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Services\Database;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\InsertQuery;
use Objects\Stream;
use Tools\Common;
use Tools\Folders;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class StreamsModel
 * @package Framework\Models
 * @localized 21.05.2015
 */
class StreamsModel implements Injectable, SingletonInterface {

    use Singleton;

    const ACCESS_PUBLIC = 'PUBLIC';
    const ACCESS_UNLISTED = 'UNLISTED';
    const ACCESS_PRIVATE = 'PRIVATE';

    /** @var UserModel $user */

    protected $user;

    function __construct() {
        $this->user = AuthUserModel::getInstance();
    }


    /**
     * @param $name
     * @param $info
     * @param $hashtags
     * @param $category
     * @param Optional $permalink
     * @param $access
     * @return int
     * @throws \Framework\Exceptions\ControllerException
     * todo: localize
     */
    public function create($name, $info, $hashtags, $category, Optional $permalink, $access) {

        if ($this->user->getCurrentPlan()->getStreamsMax() !== null &&
            $this->user->getStreamsCount() >= $this->user->getCurrentPlan()->getStreamsMax()) {
            throw ControllerException::of(sprintf("You are already created %d streams of %d available. Please upgrade your account.",
                $this->user->getStreamsCount(), $this->user->getCurrentPlan()->getStreamsMax()));
        }

        $stream = new Stream();
        $stream->setUserID($this->user->getID());
        $stream->setName($name);
        $stream->setInfo($info);
        $stream->setHashTags($hashtags);
        $stream->setCategory($category);
        $stream->setPermalink($permalink->getOrElse($this->generatePermalink($name)));
        $stream->setCreated(time());
        $stream->setAccess($access);
        $stream->save();

        $hashtags_array = explode(",", $hashtags);
        foreach ($hashtags_array as $tag) {
            (new InsertQuery("mor_tag_list"))->values("tag_name", trim($tag))
                ->set("usage_count = usage_count + 1")->update();
        }

        // Generate Stream Cover
        $random = Common::generateUniqueId();
        $newImageFile = sprintf("stream%05d_%s.%s", $stream->getID(), $random, "png");
        $newImagePath = Folders::getInstance()->genStreamCoverPath($newImageFile);

        $stream->setCover($newImageFile);
        $stream->save();

        Common::createTemporaryImage($newImagePath);

        return $stream->getID();

    }

    public function generatePermalink($name) {

        $permalink = Common::toAscii(Common::toTransliteration($name));

        Database::doInConnection(function (Database $db) use (&$permalink) {

            while ($db->fetchOneColumn("SELECT COUNT(*) FROM r_streams WHERE permalink = ?", [$permalink])->get() !== 0) {
                if (preg_match("~^(.+)\\-(\\d+)$~m", $permalink, $matches)) {
                    $matches[2]++;
                    $permalink = sprintf("%s-%d", $matches[1], $matches[2]);
                } else {
                    $permalink .= "-1";
                }
            }

        });

        return $permalink;

    }


    public function addBookmark(Stream $stream) {

        $dbo = DBQuery::getInstance();

        if (count($dbo->selectFrom("r_bookmarks")->where([
                "user_id" => $this->user->getID(),
                "stream_id" => $stream->getID()
            ])) != 0) return;

        $dbo->into("r_bookmarks")
            ->values([
                "user_id" => $this->user->getID(),
                "stream_id" => $stream->getID()
            ])
            ->update();
    }

    public function deleteBookmark(Stream $stream) {
        $dbo = DBQuery::getInstance();
        $dbo->deleteFrom("r_bookmarks")
            ->where([
                "user_id" => $this->user->getID(),
                "stream_id" => $stream->getID()
            ])
            ->update();
    }


} 