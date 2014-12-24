<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 22:16
 */

namespace Tools;

/**
 * Interface SingletonInterface
 * @package Tools
 */
interface SingletonInterface {
    public static function getInstance();

    public static function hasInstance();

    public static function killInstance();
} 