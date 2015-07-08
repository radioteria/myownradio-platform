<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.06.2015
 * Time: 16:14
 */

namespace Framework\Events;


use Tools\Singleton;
use Tools\SingletonInterface;

abstract class AbstractPublisher implements SingletonInterface {

    use Singleton;

    protected $subscribers = array();

    /**
     * @param \Closure $closure
     */
    function subscribe(\Closure $closure) {
        $this->subscribers[] = $closure;
    }

    /**
     * @param null $scope
     */
    function publish(...$scope) {
        foreach ($this->subscribers as $subscriber) {
            $subscriber(...$scope);
        }
    }

    /**
     * @return \Closure
     */
    static function send() {
        return function ($data) {
            self::getInstance()->publish($data);
        };
    }

}