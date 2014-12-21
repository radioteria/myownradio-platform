<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 14:04
 */

namespace Objects;

/**
 * Class Category
 * @package Objects
 * @table category
 * @key id
 * @view
 */
class Category extends ActiveRecordObject implements ActiveRecord {

    private $id, $name, $permalink;

    /**
     * @return mixed
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPermalink() {
        return $this->permalink;
    }

} 