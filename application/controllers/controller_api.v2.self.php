<?php

class post_controller extends authController
{

    public function getProfile()
    {
        $profile = User::getInstance();
        $plan    = new VisitorPlan($profile->getId());
        misc::dataJSON(array(
            'main' => $profile->getStatus(),
            'plan' => $plan->getStatus()
        ));
    }
    
    public function getStats()
    {
        $profile = User::getInstance();
        $stats = new VisitorStats($profile->getId());
        misc::dataJSON(array(
            'stats' => $stats->getStatus(),
        ));
    }        

}
