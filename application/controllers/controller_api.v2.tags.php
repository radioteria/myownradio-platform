<?php

/**
 * Description of controller_api
 *
 * @author Roman
 */
class post_controller extends controller
{
    public function getList() 
    {
        $search = application::post("s", null, REQ_STRING);
        echo misc::dataJSON(Tags::getList($search));
    }
}
