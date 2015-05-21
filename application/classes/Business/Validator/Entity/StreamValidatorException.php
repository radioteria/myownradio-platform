<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 21.05.15
 * Time: 15:16
 */

namespace Business\Validator\Entity;


use Business\Validator\ValidatorException;

class StreamValidatorException extends ValidatorException {

    /**
     * @param null|string $message
     * @param null $data
     * @param int $status
     */
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }

    /**
     * @param $mode
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newWrongAccessMode($mode) {
        return self::tr("VALIDATOR_STREAM_ACCESS_MODE", ["mode" => $mode]);
    }

    /**
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newStreamInformationTooLong() {
        return self::tr("VALIDATOR_STREAM_INFO_LENGTH");
    }

    /**
     * @param $category_id
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newWrongCategoryId($category_id) {
        return self::tr("VALIDATOR_STREAM_CATEGORY", ["id" => $category_id]);
    }

    /**
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newStreamNameTooLong() {
        return self::tr("VALIDATOR_STREAM_NAME_LENGTH");
    }
}