<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 19:46
 */

namespace Framework;


use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class Preferences implements Injectable, SingletonInterface {
    use Singleton;
    private static $config = [
        'invalid' => [
            'login' => ['admin', 'adm', 'root'],
            'domain' => ['localhost', 'radioter.io', 'myownradio.biz', '127.0.0.1']
        ]
    ];

    /**
     * @param null $_
     * @return Optional
     */
    public function get($_ = null) {
        $count = func_num_args();
        $accumulator = self::$config;
        for ($i = 0; $i < $count; $i++) {
            if (isset($accumulator[func_get_arg($i)])) {
                $accumulator = $accumulator[func_get_arg($i)];
            } else {
                return Optional::noValue();
            }
        }
        return Optional::ofEmpty($accumulator);
    }
} 