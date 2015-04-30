<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.04.15
 * Time: 10:51
 */

namespace Framework\Services;


use Framework\Defaults;
use Framework\Injector\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class Notif1er implements SingletonInterface, Injectable {

    use Singleton;

    public function notify($key, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Defaults::NOTIFIER_URL . "?app=mor&keys=" . $key);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, Defaults::NOTIFIER_TIMEOUT);
        curl_exec($ch);
        curl_close($ch);
    }

}