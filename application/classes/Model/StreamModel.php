<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 16:06
 */

namespace Model;


use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Services\InputValidator;
use Objects\Stream;
use Tools\Common;
use Tools\File;
use Tools\Folders;
use Tools\Singleton;

class StreamModel extends Model {

    use Singleton;

    protected $key;


    /** @var UserModel $user */
    protected $user;

    /** @var Stream $stream  */
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

        if ($this->stream->getUserID() !== $this->user->getID()) {
            throw UnauthorizedException::noAccess();
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

        $validator->validateUserPermalink($permalink, $this->key);
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

    }

    public function removeCover() {

        $folders = Folders::getInstance();

        if (!is_null($this->stream->getCover())) {

            $file = new File($folders->genStreamCoverPath($this->stream->getCover()));

            if ($file->exists()) {
                $file->delete();
            }

            $this->stream->setCover(null)->save();

        }

    }

    public function changeCover($file) {

        $folders = Folders::getInstance();

        $validator = InputValidator::getInstance();

        $validator->validateImageMIME($file["tmp_name"]);

        $random = Common::generateUniqueID();

        $this->removeCover();

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

        $newImageFile = sprintf("stream%05d_%s.%s", $this->key, $random, strtolower($extension));
        $newImagePath = $folders->genStreamCoverPath($newImageFile);

        $result = move_uploaded_file($file['tmp_name'], $newImagePath);

        if ($result !== false) {

            $this->stream->setCover($newImageFile)->save();

            return $folders->genStreamCoverUrl($newImageFile);

        } else {

            return null;

        }

    }

} 