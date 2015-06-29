<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 14.05.15
 * Time: 15:37
 */

namespace Business\Validator;


use Business\Test\TestFields;
use Objects\Country;

class BusinessValidator extends Validator {

    const PERMALINK_REGEXP_PATTERN = "~(^[a-z0-9\\-]+$)~";

    const PASSWORD_MIN_LENGTH = 6;
    const PASSWORD_MAX_LENGTH = 32;

    public function permalink() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return preg_match(self::PERMALINK_REGEXP_PATTERN, $value); });
        return $copy;
    }

    /**
     * @param int|null $ignoredId
     * @return $this
     */
    public function isPermalinkAvailableForUser($ignoredId = null) {

        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($ignoredId) {
            $test = TestFields::getInstance();
            $result = $test->testUserPermalink($value);
            return $result === false || $result == $ignoredId;
        });

        return $copy;

    }

    /**
     * @param int|null $ignoredId
     * @return $this
     */
    public function isPermalinkAvailableForStream($ignoredId = null) {

        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($ignoredId) {
            $test = TestFields::getInstance();
            $result = $test->testStreamPermalink($value);
            return $result === false || $result == $ignoredId;

        });

        return $copy;

    }

    /**
     * @param $ignoredId
     * @return $this
     */
    public function isEmailAvailable($ignoredId = null) {

        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($ignoredId) {
            $test = TestFields::getInstance();
            $result = $test->testEmail($value);
            return $result === false || $result == $ignoredId;
        });

        return $copy;

    }


    /**
     * @param $ignoredId
     * @return $this
     */
    public function isLoginAvailable($ignoredId = null) {

        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($ignoredId) {
            $test = TestFields::getInstance();
            $result = $test->testLogin($value);
            return $result === false || $result == $ignoredId;
        });

        return $copy;

    }

    /**
     * @return $this
     */
    public function isCountryIdCorrect() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) {
            return Country::getByID($value)->nonEmpty();
        });
        return $copy;
    }

    /**
     * @param $hash
     * @return $this
     */
    public function isPasswordCorrect($hash) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($hash) {
            return false;
        });

        return $copy;
    }

} 