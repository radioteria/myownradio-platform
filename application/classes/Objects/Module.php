<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 23.12.14
 * Time: 16:38
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Module
 * @package Objects
 * @table r_modules
 * @key name
 * @view
 */
class Module extends ActiveRecordObject implements ActiveRecord {
    private $name, $html, $css, $js, $tmpl, $post;

    function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getCss() {
        return $this->css;
    }

    /**
     * @return mixed
     */
    public function getHtml() {
        return $this->html;
    }

    /**
     * @return mixed
     */
    public function getJs() {
        return $this->js;
    }

    /**
     * @return mixed
     */
    public function getPost() {
        return $this->post;
    }

    /**
     * @return mixed
     */
    public function getTmpl() {
        return $this->tmpl;
    }


} 