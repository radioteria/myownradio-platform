<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 12.12.14
 * Time: 15:51
 */

class SuperRouter {
    private $routes = [];

    /**
     * @param string $route
     * @param callable $do
     */
    public function when($route, callable $do) {
        $routes[$route] = $do;
    }

    // path: /cool/123
    // pattern: /cool/:id
    public function find($path) {

    }


} 