<?php

/**
 * creator class implements any kind of creation
 *
 * @author Roman
 */
class Fabric extends Model {
    private $visitor;

    const SQL_NEW_STREAM =
        "INSERT INTO r_streams (uid, name, info, genres, permalink, category, created) VALUES (?, ?, ?, ?, ?, ?, ?)";

    public function __construct() {
        parent::__construct();
        $this->visitor = new Visitor(user::getCurrentUserId());
    }

    public function createStream(validStreamName $name, validStreamDescription $info,
                                 ArrayObject $genres, validPermalink $permalink, validCategory $category) {
        $ids = implode(',', $genres->getArrayCopy());

        $result = $this->database->query_update(self::SQL_NEW_STREAM,
            array($this->visitor->getId(), $name, $info, $ids, $permalink, $category, time()));

        if ($result === 0) {
            throw new streamException("Can't create new stream", 1001, null);
        }

        $id = $this->database->lastInsertId();

        $stream = new radioStream($id);

        return misc::okJSON($stream->toArray());
    }
}
