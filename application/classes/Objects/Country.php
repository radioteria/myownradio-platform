<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 31.01.15
 * Time: 18:07
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Country
 * @package Objects
 *
 * @table mor_countries
 * @key country_id
 * @view
 */
class Country extends ActiveRecordObject implements ActiveRecord {
    private
        $country_id,
        $country_code,
        $country_name;

    /**
     * @param mixed $country_code
     * @return $this
     */
    public function setCountryCode($country_code) {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * @param mixed $country_name
     * @return $this
     */
    public function setCountryName($country_name) {
        $this->country_name = $country_name;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getCountryCode() {
        return $this->country_code;
    }

    /**
     * @return mixed
     */
    public function getCountryID() {
        return $this->country_id;
    }

    /**
     * @return mixed
     */
    public function getCountryName() {
        return $this->country_name;
    }



}
