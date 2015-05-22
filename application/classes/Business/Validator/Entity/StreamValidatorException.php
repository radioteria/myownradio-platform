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
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newWrongAccessMode() {
        return self::tr("VALIDATOR_STREAM_ACCESS_MODE");
    }

    /**
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newStreamInformationTooLong() {
        return self::tr("VALIDATOR_STREAM_INFO_LENGTH");
    }

    /**
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newWrongCategoryId() {
        return self::tr("VALIDATOR_STREAM_CATEGORY");
    }

    /**
     * @return StreamValidatorException
     * todo: localize
     */
    public static function newStreamNameLength() {
        return self::tr("VALIDATOR_STREAM_NAME_LENGTH");
    }
}