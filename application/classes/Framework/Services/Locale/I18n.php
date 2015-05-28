<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.04.15
 * Time: 9:48
 */

namespace Framework\Services\Locale;


use Tools\Common;

class I18n {

    private $lang = null,
            $data = null,
            $file_path = null;

    function __construct($locale) {
        $file_path = "application/i18n/{$locale}.json";
        if (!file_exists($file_path)) {
            throw new LocaleException(sprintf("File '%s' not found", $file_path));
        }
        $this->file_path = $file_path;
        $this->lang = $locale;
        $this->data = json_decode(file_get_contents($file_path), true);
    }

    public function getFileContent() {
        return file_get_contents($this->file_path);
    }

    /**
     * @param string $key
     * @param array $args
     * @return mixed|string
     */
    public function get($key, array $args = null) {
        if (isset($this->data[$key])) {
            return Common::deepTemplate($this->data[$key], $args);
        } else {
            return $key;
        }
    }

    /**
     * @return null|string
     */
    public function getLocale() {
        return $this->lang;
    }

    /**
     * @param string $key
     * @param array $args
     * @return mixed
     */
    public static function tr($key, array $args = null) {
        return L10n::getInstance()->get($key, $args);
    }

}