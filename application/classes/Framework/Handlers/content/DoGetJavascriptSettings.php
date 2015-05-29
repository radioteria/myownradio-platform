<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 29.05.15
 * Time: 18:07
 */

namespace Framework\Handlers\content;


use Framework\ControllerImpl;
use Framework\Preferences;

class DoGetJavascriptSettings extends ControllerImpl {
    public function doGet() {
        header("Content-Type: application/x-javascript");
        echo 'var $CONFIG = ' . Preferences::json() . ';';
        echo "\n";
    }
} 