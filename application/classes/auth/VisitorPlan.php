<?php

class VisitorPlan extends Model
{
    use Singleton;
    
    private $rights = null;
    private $plan = array();

    public function __construct($user_id)
    {
        parent::__construct();
        
        $result = $this->database->query_single_row("SELECT * FROM `r_subscriptions` WHERE `uid` = ? AND `expire` > UNIX_TIMESTAMP(NOW()) ORDER BY `id` DESC LIMIT 1", array($user_id));

        if($result === null)
        {
            $this->plan['plan'] = 0;
            $this->plan['expire'] = null;
        }
        else
        {
            $this->plan = $result;
        }
        
        $this->rights = $this->database->query_single_row("SELECT * FROM `r_limitations` WHERE `level` = ?", array($this->plan['plan']));
    }
    
    public function getStatus()
    {
        return array(
            'plan_id' => (int) $this->rights['level'],
            'plan_name' => $this->rights['name'],
            'plan_expires' => date("d M, Y", $this->plan['expire']),
            'plan_expires_unix' => (int) $this->plan['expire'],
            'plan_time_limit' => (int) $this->rights['upload_limit'] * 60 * 1000,
            'plan_streams_limit' => (int) $this->rights['streams_max']
        );
    }
}
