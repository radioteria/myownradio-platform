<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 10:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\Database;

class DoTest implements Controller {

    public function doGet() {

        // Database is not connected

        Database::doInConnection(function (Database $db) {
            // Database is connected with handle in $db

            // Default style
            echo $db->fetchOneColumn("SELECT NOW()", NULL, 0)
                ->getOrElseThrow(new \Exception("Database is bad!"));

            echo '<br />';

            // Functional style
            $db->fetchOneColumn("SELECT NOW()", NULL, 0)->then(function ($now) {
                echo $now;
            });


        });

        // Database connection is returned into connection pool

    }

} 