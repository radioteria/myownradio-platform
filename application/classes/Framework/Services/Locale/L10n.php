<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.04.15
 * Time: 11:25
 */

namespace Framework\Services\Locale;


use Framework\Injector\Injectable;
use Framework\Services\HttpRequest;
use Tools\Singleton;
use Tools\SingletonInterface;

class L10n extends I18n implements Injectable, SingletonInterface {

    use Singleton;

    private $locales = [
        "uk" => "en_US",
        "ru" => "en_US",
        "en" => "en_US",

        "default" => "en_US"
    ];

    function __construct() {
        $server = HttpRequest::getInstance();
        $locale = $server->getLanguage()->getOrElse("en");
        if (isset($this->locales[$locale])) {
            parent::__construct($this->locales[$locale]);
        } else {
            parent::__construct($this->locales["default"]);
        }
    }


}