<?php

class streamreader
{
    static function generateUniquePermalink($permalink)
    {
        $permalink = misc::toAscii($permalink);
        while((int)db::query_single_col("SELECT COUNT(*) FROM `r_streams` WHERE `permalink` = ?", array($permalink)) > 0)
        {
            $matches = array();
            if(preg_match("/^(.+)-(\d+)$/", $permalink, $matches))
            {
                $matches[2] ++;
                $permalink = $matches[1] . "-" . $matches[2];
            }
            else
            {
                $permalink .= "-1";
            }
        }
        
        return $permalink;
    }
    
    static function getStreamsSimple($user_id)
    {
        $result = array();
        $data = db::query("SELECT * FROM `r_streams` WHERE `uid` = ?", array($user_id));
        foreach($data as $row)
        {
            $result[$row['sid']] = $row['name'];
        }
        return $result;
    }
    
    static function getStreamsCount($user_id)
    {
        // Simple db query builder
        $builder = new querybuilder("SELECT");
        
        $builder->addSelect("COUNT(*)")
                ->setFrom("`r_streams`")
                ->addWhere("`uid` = :id");
        
        return (int) db::query_single_col($builder, array('id' => $user_id));
    }
    
    static function streamList($from, $limit)
    {
        $builder = new querybuilder("SELECT");
        $builder->addSelect("*")
                ->setFrom("`r_streams`")
                ->addWhere("`status` = 1")
                ->addOrder("`name` ASC")
                ->setLimit($from, $limit);
        
        return db::query($builder);
    }
   

    static function streamSearch($query = "*", $from = 0, $limit = 50)
    {
        return db::query("SELECT * FROM `r_streams` WHERE `status` = 1 AND MATCH(`name`, `permalink`, `genres`) AGAINST (? IN BOOLEAN MODE) LIMIT $from, $limit", array($query));
    }    
   
    static function getStreams($user_id, $active = -1)
    {
        $data = db::query("SELECT * FROM `r_streams` WHERE `uid` = ?", array($user_id));
        foreach($data as &$obj)
        {
            $obj['active'] = ((int)$obj['sid'] == $active) ? "active" : "";
            $obj['tracks'] = (new stream($obj['sid']))->getTracksCount();
        }
        return $data;
    }
 
}
