<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.04.15
 * Time: 13:57
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;
use Framework\Services\ORM\Exceptions\ORMException;

/**
 * Class Options
 * @package Objects
 * @table opt_user_options
 * @key user_id
 */
class Options extends ActiveRecordObject implements ActiveRecord {
    private $user_id;
    private $lang_id;
    private $format_id;
    private $volume;

    /**
     * @return mixed
     */
    public function getFormatId() {
        return $this->format_id;
    }

    /**
     * @return mixed
     */
    public function getLangId() {
        return $this->lang_id;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function getVolume() {
        return $this->volume;
    }

    /**
     * @param mixed $format
     */
    public function setFormatId($format) {
        $this->format_id = $format;
    }

    /**
     * @param mixed $lang
     */
    public function setLangId($lang) {
        $this->lang_id = $lang;
    }

    /**
     * @param mixed $volume
     */
    public function setVolume($volume) {
        $this->volume = $volume;
    }

} 