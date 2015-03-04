<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 12:53
 */

namespace Framework\Services;


use Framework\Injector\Injectable;

class Date implements Injectable {
    /** @var \DateTime $date */
    private $date;

    /**
     * @param int|null|string $time
     */
    function __construct($time = "now") {
        $this->date = new \DateTime($time);
    }

    /**
     * @return int
     */
    public function getSeconds() {
        return (int) $this->date->format("s");
    }

    /**
     * @return int
     */
    public function getMinutes() {
        return (int) $this->date->format("i");
    }

    /**
     * @return int
     */
    public function getHours() {
        return (int) $this->date->format("H");
    }

    /**
     * @return int
     */
    public function getMonthDay() {
        return (int) $this->date->format("j");
    }

    /**
     * @return int
     */
    public function getWeekDay() {
        return (int) $this->date->format("N");
    }

    /**
     * @return int
     */
    public function getMonth() {
        return (int) $this->date->format("n");
    }

    /**
     * @return int
     */
    public function getYear() {
        return (int) $this->date->format("Y");
    }

    /**
     * @param $format
     * @return string
     */
    public function format($format) {
        return $this->date->format($format);
    }

    /**
     * @return int
     */
    public function getUnixTime() {
        return $this->date->getTimestamp();
    }

}