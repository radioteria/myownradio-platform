<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 05.05.15
 * Time: 11:13
 */

namespace Framework\Services\Annotations;


use Tools\Optional\Option;

class Annotation {

    private $name;
    private $parameters;

    function __construct($name, array $parameters = null) {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * @param $annotation
     * @return Annotation
     */
    public static function parseAnnotation($annotation) {

        if (preg_match('~^\@(\w+)$~', $annotation, $match)) {
            return new self($match[1], null);
        } elseif (preg_match('~^\@(\w+)\((.+)\)$~', $annotation, $match)) {
            return new self($match[1], ["value" => json_decode($match[2], true)]);
        }

    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return Option
     */
    public function getDefault() {
        return $this->getParameter("value");
    }

    /**
     * @param $key
     * @return Option
     */
    public function getParameter($key) {
        return Option::ofNullable(@$this->parameters[$key]);
    }

} 