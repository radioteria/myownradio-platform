<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.12.14
 * Time: 20:44
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use Tools\Singleton;

class Plan extends Model {

    use Singleton;

    private $id;
    private $name;
    private $stream_limit;
    private $upload_limit;
    private $price;

    public function __construct($id) {

        parent::__construct();

        $plan = $this->db->fetchOneRow("SELECT * FROM r_limitations WHERE level = ?", [$id])
            ->getOrElseThrow(new ControllerException(sprintf("Plan with ID = %s not exists", $id)));

        $this->id = $plan["level"];
        $this->name = $plan["name"];
        $this->stream_limit = $plan["streams_max"];
        $this->upload_limit = $plan["upload_limit"];
        $this->price = $plan["price"];

    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getStreamLimit() {
        return $this->stream_limit;
    }

    /**
     * @return mixed
     */
    public function getUploadLimit() {
        return $this->upload_limit;
    }



} 