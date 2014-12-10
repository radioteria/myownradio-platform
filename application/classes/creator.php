<?php

/**
 * creator class implements any kind of creation
 *
 * @author Roman
 */
class creator extends Model
{
    private $vendee;
    
    public function __construct()
    {
        parent::__construct();
        $this->vendee = new Visitor(user::getCurrentUserId());
    }
    
    public function createStream(validStreamName $name, validStreamDescription $info, ArrayObject $genres, validPermalink $permalink, validCategory $category)
    {
        
        $i = new Directory("");

        $ids = implode(',', $genres->getArrayCopy());
        $query = "INSERT INTO `r_streams` (`uid`, `name`, `info`, `genres`, `permalink`, `category`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = $this->database->query_update($query, array($this->vendee->getId(), $name, $info, $ids, $permalink, $category));
        
        if($result === 0)
        {
            throw new streamException("Can't create new stream", 1001, null);
        }
        
        $id = $this->database->lastInsertId();
        
        $stream = new radioStream($id);
        
        return misc::okJSON($stream->toArray());
    }
}
