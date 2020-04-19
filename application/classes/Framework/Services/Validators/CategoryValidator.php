<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:45
 */

namespace Framework\Services\Validators;


use Framework\Exceptions\ControllerException;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\Locale\I18n;
use Framework\Services\Validator;

class CategoryValidator extends Validator {
    /**
     * @param $category_permalink
     * @throws ControllerException
     * @return object category_id
     */
    public function validateChannelCategoryByPermalink($category_permalink) {
        $category_object = (new SelectQuery("r_categories"))->where("category_permalink", $category_permalink)
            ->fetchOneRow()->getOrElseThrow(ControllerException::of(
                I18n::tr("VALIDATOR_INVALID_CATEGORY_NAME", [$category_permalink])
            ));
        return $category_object;
    }

    /**
     * @param $category_id
     * @throws ControllerException
     * @return object category_id
     */
    public function validateChannelCategoryById($category_id) {
        $category_object = (new SelectQuery("r_categories"))->where("category_id", $category_id)
            ->fetchOneRow()->getOrElseThrow(ControllerException::of(
                I18n::tr("VALIDATOR_INVALID_CATEGORY_NAME", [$category_id])
            ));
        return $category_object;
    }
} 