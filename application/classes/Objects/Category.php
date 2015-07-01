<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 14:04
 */

namespace Objects;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Category
 * @package Objects
 * @table r_categories
 * @key category_id
 * @do_key category_permalink = :key
 * @view
 */
class Category extends ActiveRecordObject implements ActiveRecord {

    protected $category_id, $category_name, $category_permalink;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->category_id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->category_name;
    }

    /**
     * @return mixed
     */
    public function getPermalink() {
        return $this->category_permalink;
    }


} 