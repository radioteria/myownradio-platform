<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 16:06
 */

namespace Framework\Models;

use app\Services\ImageService;
use app\Services\Storage\StorageFactory;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\DeleteQuery;
use Framework\Services\DB\Query\InsertQuery;
use Framework\Services\InputValidator;
use Objects\Stream;
use Objects\Track;
use Tools\Common;
use Tools\Singleton;
use Tools\SingletonInterface;

class StreamModel extends Model implements SingletonInterface {

    use Singleton;

    protected $key;


    /** @var UserModel $user */
    protected $user;

    /** @var Stream $stream */
    protected $stream;

    public function __construct($id) {
        parent::__construct();
        $this->user = AuthUserModel::getInstance();
        $this->key = $id;
        $this->load();
    }

    private function load() {

        $this->stream = Stream::getByID($this->key)
            ->getOrElseThrow(ControllerException::noStream($this->key));

        if (! $this->stream->isAccessibleTo($this->user->getUserObject())) {
            throw UnauthorizedException::noPermission();
        }

    }

    /**
     * @return int
     */
    public function getID() {
        return $this->stream->getID();
    }

    /**
     * @return int
     */
    public function getStarted() {
        return $this->stream->getStarted();
    }

    /**
     * @return string
     */
    public function getAccess() {
        return $this->stream->getAccess();
    }

    /**
     * @return int|null
     */
    public function getCategory() {
        return $this->stream->getCategory();
    }

    /**
     * @return string
     */
    public function getCover() {
        return $this->stream->getCover();
    }

    /**
     * @return int
     */
    public function getCreated() {
        return $this->stream->getCover();
    }

    /**
     * @return string
     */
    public function getHashTags() {
        return $this->stream->getHashTags();
    }

    /**
     * @return string
     */
    public function getInfo() {
        return $this->stream->getInfo();
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->stream->getName();
    }

    /**
     * @return string|null
     */
    public function getPermalink() {
        return $this->stream->getPermalink();
    }

    /**
     * @return int
     */
    public function getStartedFrom() {
        return $this->stream->getStartedFrom();
    }

    /**
     * @return int
     */
    public function getStatus() {
        return $this->stream->getStatus();
    }

    /**
     * @return int
     */
    public function getUserID() {
        return $this->stream->getUserID();
    }

    public function update($name, $info, $permalink, $hashtags, $category, $access) {

        $validator = InputValidator::getInstance();

        $validator->validateStreamPermalink($permalink, $this->key);
        $validator->validateStreamCategory($category);
        $validator->validateStreamAccess($access);

        $this->stream
            ->setName($name)
            ->setInfo($info)
            ->setPermalink($permalink)
            ->setHashTags($hashtags)
            ->setCategory($category)
            ->setAccess($access)
            ->save();

        // todo: do this with db query
        $hashtags_array = explode(",", $hashtags);
        foreach ($hashtags_array as $tag) {
            (new InsertQuery("mor_tag_list"))->values("tag_name", trim($tag))
                ->set("usage_count = usage_count + 1")->update();
        }

    }

    public function removeCover()
    {
        if (!is_null($this->stream->getCover())) {
            $storage = StorageFactory::getStorage();
            $path = 'covers/' . $this->stream->getCover();
            $storage->delete($path);
            $this->stream->setCoverBackground(null);
            $this->stream->setCover(null);
            $this->stream->save();
        }
    }

    public function changeCover($file)
    {
        $storage = StorageFactory::getStorage();
//        $s3 = S3::getInstance();

        $validator = InputValidator::getInstance();

        $validator->validateImageMIME($file["tmp_name"]);

        $random = Common::generateUniqueID();

        $this->removeCover();

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

        $newImageFile = sprintf("stream%05d_%s.%s", $this->key, $random, strtolower($extension));
        $newImagePath = 'covers/' . $newImageFile;


        $storage->put($newImagePath, fopen($file["tmp_name"], 'r'), [
            'ContentType' => mimetype_from_extension($extension)
        ]);

        $url = $storage->url($newImagePath);

        $this->stream->setCoverBackground((new ImageService())->getImageBackgroundColor($url));
        $this->stream->setCover($newImageFile);
        $this->stream->save();

        return [
            "url"   => $url,
            "name"  => $newImageFile
        ];
    }

    public function delete()
    {
        (new DeleteQuery("r_link"))->where("stream_id", $this->key)->update();

        if (!is_null($this->stream->getCover())) {
            StorageFactory::getStorage()->delete('covers/'. $this->stream->getCover());
        }

        $this->stream->delete();
    }

    public function moveStreamToOtherUser($streamId, UserModel $targetUser) {

        $dbq = DBQuery::getInstance();

        /** @var Stream $stream */
        $stream = Stream::getByID($streamId)->getOrElseThrow(ControllerException::noStream($streamId));
        $stream->setUserID($targetUser->getID());
        $stream->save();

        $tracks = $dbq->selectFrom("r_tracks")
            ->innerJoin("r_link", "r_link.track_id = r_tracks.tid")
            ->where("r_link.stream_id", $streamId)
            ->fetchAll();

        foreach ($tracks as $track) {
            $track_object = Track::getByData($track);
            $track_object->setUserID($targetUser->getID());
            $track_object->save();
        }

    }


} 