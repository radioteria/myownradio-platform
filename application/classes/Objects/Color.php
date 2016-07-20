<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 22.02.15
 * Time: 11:18
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Color
 * @package Objects
 * @table r_colors
 * @key color_id
 * @view
 */
class Color extends ActiveRecordObject implements ActiveRecord {
    private $color_id, $color_name, $color_code;

    /**
     * @return mixed
     */
    public function getColorCode() {
        return $this->color_code;
    }

    /**
     * @return mixed
     */
    public function getColorId() {
        return $this->color_id;
    }

    /**
     * @return mixed
     */
    public function getColorName() {
        return $this->color_name;
    }

} 