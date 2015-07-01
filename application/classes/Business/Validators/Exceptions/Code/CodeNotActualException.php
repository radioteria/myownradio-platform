<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 16:43
 */

namespace Business\Validators\Exceptions\Code;


use Framework\Services\Locale\I18n;

class CodeNotActualException extends CodeException {
    public function __construct() {
        parent::__construct(I18n::tr("ERROR_CODE_NOT_ACTUAL"));
    }
}