<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 23:02
 */

namespace Model\Beans;


class StreamBean {

    private $sid;
    private $info;

    public function getStreamId() {
        return intval($this->sid);
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        return $this->info;
    }


} 