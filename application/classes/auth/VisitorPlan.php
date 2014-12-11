<?php

class VisitorPlan extends Model
{
    use Singleton;
    
    private $rights = null;
    private $plan = array();

    public function __construct($user_id) {
        parent::__construct();
        
        $result = $this->database->query_single_row(
            "SELECT * FROM r_subscriptions WHERE uid = ? AND expire > UNIX_TIMESTAMP(NOW()) ORDER BY id DESC LIMIT 1",
            array($user_id));

        if($result === null) {
            $this->plan['plan'] = 0;
            $this->plan['expire'] = null;
        } else {
            $this->plan = $result;
        }
        
        $this->rights = $this->database->query_single_row(
            "SELECT * FROM r_limitations WHERE level = ?",
            array($this->plan['plan']));
    }

    public function getPlanId() {
        return (int) $this->rights['level'];
    }

    public function getPlanName() {
        return $this->rights['name'];
    }

    public function getPlanExpireDate() {
        return $this->rights['expire'];
    }

    public function getTimeLimit() {
        return (int) $this->rights['upload_limit'] * 60 * 1000;
    }

    public function getStreamCountLimit() {
        return (int) $this->rights['streams_max'];
    }

    public function getStatus()
    {
        return array(
            'plan_id' => $this->getPlanId(),
            'plan_name' => $this->getPlanName(),
            'plan_expires' => date("d M, Y", $this->getPlanExpireDate()),
            'plan_expires_unix' => $this->getPlanExpireDate(),
            'plan_time_limit' => $this->getTimeLimit(),
            'plan_streams_limit' => $this->getStreamCountLimit()
        );
    }
}
