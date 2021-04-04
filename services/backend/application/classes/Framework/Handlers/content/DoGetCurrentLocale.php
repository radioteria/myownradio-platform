<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.04.15
 * Time: 13:52
 */

namespace Framework\Handlers\content;


use Framework\Controller;
use Framework\Services\Locale\L10n;

class DoGetCurrentLocale implements Controller {
    public function doGet(L10n $l10n) {
        header("Content-Type: application/javascript");
        echo "var locale = ".$l10n->getFileContent().";";
    }
} 