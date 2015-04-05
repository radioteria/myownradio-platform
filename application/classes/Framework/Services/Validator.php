<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:31
 */

namespace Framework\Services;


use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\Locale\I18n;
use Tools\Singleton;
use Tools\SingletonInterface;

class Validator implements Injectable, SingletonInterface {
    use Singleton;

    /**
     * @param $category_permalink
     * @throws ControllerException
     * @return int category_id
     */
    public function validateChannelCategoryByPermalink($category_permalink) {
        $category_object = (new SelectQuery("r_categories"))->where("category_permalink", $category_permalink)
            ->fetchOneRow()->getOrElseThrow(ControllerException::of(
                I18n::tr("VALIDATOR_INVALID_CATEGORY_NAME", [$category_permalink])
            ));
        return $category_object["category_id"];
    }
} 