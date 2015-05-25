<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 21.05.15
 * Time: 15:07
 */

namespace Business\Validator\Entity;


use Business\Validator\BusinessValidator;
use Business\Validator\Validator;
use Business\Validator\ValidatorException;
use Objects\Category;
use Objects\Stream;

/**
 * Class StreamValidator
 * @package Business\Validator\Entity
 */
class StreamValidator implements EntityValidator {

    public static $ACCESS_MODES = ["PUBLIC", "UNLISTED", "PRIVATE"];
    public static $INFO_MAX_LENGTH = 4096;
    public static $NAME_MIN_LENGTH = 3;
    public static $NAME_MAX_LENGTH = 32;

    /** @var Stream */
    private $stream;

    /**
     * @param Stream $stream
     * @throws StreamValidatorException
     */
    public static function validate(Stream $stream) {
        $validator = new self($stream);
        $validator->validateAllFields();
    }

    public function __construct(Stream $stream) {
        $this->stream = $stream;
    }

    /**
     * @throws StreamValidatorException
     */
    public function validateAllFields() {
        $this->validateStreamName();
        $this->validateStreamPermalink();
        $this->validateAccessMode();
        $this->validateStreamCategory();
        $this->validateStreamInformation();
    }

    /**
     * @throws StreamValidatorException
     */
    private function validateStreamName() {
        (new BusinessValidator($this->stream->getName()))
            ->length(self::$NAME_MIN_LENGTH, self::$NAME_MAX_LENGTH)
            ->throwOnFail(StreamValidatorException::newStreamNameLength());
    }

    /**
     * @throws StreamValidatorException
     */
    private function validateStreamPermalink() {

        if (is_null($this->stream->getPermalink())) {
            return;
        }

        (new BusinessValidator($this->stream->getPermalink()))
            ->permalink()
            ->throwOnFail(ValidatorException::tr("VALIDATOR_PERMALINK_CHARS"))
            ->isPermalinkAvailableForStream($this->stream->getID())
            ->throwOnFail(ValidatorException::tr("VALIDATOR_PERMALINK_USED"));

    }

    /**
     * @throws StreamValidatorException
     */
    private function validateAccessMode() {
        (new Validator($this->stream->getAccess()))->existsInArray(self::$ACCESS_MODES)
            ->throwOnFail(StreamValidatorException::newWrongAccessMode());
    }

    /**
     * @throws StreamValidatorException
     */
    private function validateStreamCategory() {
        (new Validator($this->stream->getCategory()))
            ->isNumber()
            ->existsInIterator(Category::getList()->getKeys())
            ->throwOnFail(StreamValidatorException::newWrongCategoryId());
    }

    /**
     * @throws StreamValidatorException
     */
    private function validateStreamInformation() {
        (new Validator($this->stream->getInfo()))
            ->maxLength(self::$INFO_MAX_LENGTH)
            ->throwOnFail(StreamValidatorException::newStreamInformationTooLong());
    }

}