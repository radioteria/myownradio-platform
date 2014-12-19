<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:57
 */

namespace Model;


use Model\ActiveRecords\Basis;
use MVC\Exceptions\ControllerException;

class BasisModel extends Model {

    /** @var Basis */
    private $basis;

    function __construct($id) {
        $this->basis = Basis::getByID($id)->getOrElseThrow(ControllerException::noBasis($id));
    }

    public function getInfo() { return $this->basis->getInfo(); }

    public function getDuration() { return $this->basis->getDuration(); }


} 