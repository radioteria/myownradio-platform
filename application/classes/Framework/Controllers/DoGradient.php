<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\DB\Query\DeleteQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\DB\Query\UpdateQuery;
use Framework\Services\Locale\L10n;
use Objects\Options;

class DoGradient implements Controller {
    public function doGet() {

        header("Content-Type: text/plain");
        set_time_limit(30);

        /** @var Options $options */
        $options = Options::getByID(1)->get();
        $options->setProperty("format_id", 5);
        $options->save();
        echo $options->getFormatId();

    }
} 