<?php
/**
 * Created by PhpStorm.
 * User: LRU
 * Date: 21.03.2015
 * Time: 22:35
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Framework\Services\Locale\I18n;
use Tools\Singleton;
use Tools\SingletonInterface;

class TwigTemplate implements Injectable, SingletonInterface {
    use Singleton;

    /** @var \Twig_Environment $twig */
    private $twig;

    function __construct() {

        $loader = new \Twig_Loader_Filesystem("application/tmpl");

        $this->twig = new \Twig_Environment($loader, [
            //"cache" => "application/cache"
        ]);

        $this->loadFilters();

    }

    private function loadFilters() {

        $this->twig->addFilter(new \Twig_SimpleFilter("json", function ($src) {
            return json_encode($src);
        }));

        $this->twig->addFilter(new \Twig_SimpleFilter("ms2time", function ($src) {
            $hours   = floor($src / 1000 / 3600);
            $minutes = floor($src / 1000 / 60) % 60;
            $seconds = floor($src / 1000) % 60;
            return $hours ?
                sprintf("%2d:%02d:%02d", $hours, $minutes, $seconds) :
                sprintf("%2d:%02d", $minutes, $seconds);
        }));

        $this->twig->addFilter(new \Twig_SimpleFilter("tr", function ($src, $args = null) {
            return I18n::tr($src, $args);
        }));

    }

    /**
     * @param $template
     * @param $context
     */
    public function displayTemplate($template, $context) {
        $this->twig->loadTemplate($template)->display($context);
    }

    /**
     * @param $template
     * @param $context
     * @return string
     */
    public function renderTemplate($template, $context) {
        return $this->twig->loadTemplate($template)->render($context);
    }

}