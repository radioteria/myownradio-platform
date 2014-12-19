<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 16.12.14
 * Time: 20:44
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use Objects\Plan;
use Tools\Singleton;

class PlanModel extends Model {

    use Singleton;

    /** @var Plan $plan */
    private $plan;

    const MONTH_PAYMENT = 2678400;
    const YEAR_PAYMENT = 31536000;

    public function __construct($id) {

        parent::__construct();

        $this->plan = Plan::getByID($id)
            ->getOrElseThrow(new ControllerException(sprintf("Plan with ID = %s not exists", $id)));

    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->plan->getName();
    }

    /**
     * @return mixed
     */
    public function getPrice() {
        return $this->plan->getPrice();
    }

    /**
     * @return mixed
     */
    public function getStreamLimit() {
        return $this->plan->getStreamsMax();
    }

    /**
     * @return mixed
     */
    public function getUploadLimit() {
        return $this->plan->getUploadLimit();
    }

    /**
     * @return int
     */
    public function getID() {
        return $this->plan->getID();
    }

} 