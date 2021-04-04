<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.04.15
 * Time: 14:05
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Lang
 * @package Objects
 * @table opt_valid_lang
 * @key lang_id
 */
class Lang extends ActiveRecordObject implements ActiveRecord {
    private $lang_id;
    private $lang_code;
    private $lang_locale;
    private $lang_name;

    /**
     * @return mixed
     */
    public function getLangCode() {
        return $this->lang_code;
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
    public function getLangLocale() {
        return $this->lang_locale;
    }

    /**
     * @return mixed
     */
    public function getLangName() {
        return $this->lang_name;
    }

    /**
     * @param mixed $lang_code
     */
    public function setLangCode($lang_code) {
        $this->lang_code = $lang_code;
    }

    /**
     * @param mixed $lang_locale
     */
    public function setLangLocale($lang_locale) {
        $this->lang_locale = $lang_locale;
    }

    /**
     * @param mixed $lang_name
     */
    public function setLangName($lang_name) {
        $this->lang_name = $lang_name;
    }


} 