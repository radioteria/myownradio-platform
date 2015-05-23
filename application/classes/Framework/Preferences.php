<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 19:46
 */

namespace Framework;


use Framework\Injector\Injectable;
use Tools\Common;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class Preferences implements Injectable, SingletonInterface {
    use Singleton;
    private static $config = [
        'invalid' => [
            'login' => ['admin', 'adm', 'root'],
            'domain' => ['localhost', 'myownradio.biz', '127.0.0.1']
        ]
    ];

    private static $prefs;

    public static function staticInit() {
        self::$prefs = parse_ini_file("settings.ini", true);
    }

    /**
     * @param string $section
     * @param string $setting
     * @param array $context
     * @return mixed
     */
    public static function getSetting($section, $setting, array $context = null) {
        if ($context === null) {
            return self::$prefs[$section][$setting];
        }
        return Common::quickReplace(self::$prefs[$section][$setting], $context);
    }

    /**
     * @return Optional
     */
    public function get() {
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