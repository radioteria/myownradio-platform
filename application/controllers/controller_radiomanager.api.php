<?php

class post_controller extends authController
{
    public function getTrackItem()
    {
        $track_id = application::post("track_id", NULL, REQ_STRING);
        $type = application::post("type", "html", REQ_STRING);

        if(is_null($track_id))
        {
            exit();
        }

        if($type === "html")
        {
            echo application::getModule("rm.part.get.track", array(), array('track_id' => $track_id));
        }
        else if($type === "json")
        {
            echo json_encode((new track($track_id))->makeArray());
        }
        else if($type === "tags")
        {
            $tagger = array(
                'title' => null,
                'artist' => null,
                'album' => null,
                'date' => null,
                'genre' => null,
                'track_number' => null
            );
            $queue = new trackqueue($track_id);
            $queue->iterator(function($track_id) use (&$tagger) {
                $track_data = (new track($track_id))->makeArray();
                foreach(array_keys($tagger) as $key)
                {
                    if($tagger[$key] === null)
                    {
                        $tagger[$key] = $track_data[$key];
                    }
                    elseif($tagger[$key] !== config::getSetting("tagger", "do_not_change_string") && $track_data[$key] !== $tagger[$key])
                    {
                        $tagger[$key] = config::getSetting("tagger", "do_not_change_string");
                    }
                }
                unset($track_data);
            });
            echo json_encode($tagger);
        }
    }
    
    public function getStreamItem()
    {
        $stream_id = application::post("stream_id", NULL, REQ_INT);

        if(is_null($stream_id))
        {
            exit();
        }

        $stream = application::singular("stream", $stream_id);

        echo json_encode($stream->makeArray());

    }
}

class get_controller extends authController
{
    public function profileStatus()
    {
        $user_id = user::getCurrentUserId();
        
        header("Content-Type: text/plain");
        
        $vendee = new Visitor($user_id);
        $plan   = new VisitorPlan($user_id);
        $stats  = new VisitorStats($user_id);
        
        echo json_encode(array(
            'user_data'  => $vendee->getStatus(),
            'plan_data'  => $plan->getStatus(),
            'user_stats' => $stats->getStatus()
        ));
        
    }
}