<?php

/**
 * creator class implements any kind of creation
 *
 * @author Roman
 */
class Fabric extends Model {
    private $visitor;

    public function __construct() {
        parent::__construct();
        $this->visitor = new Visitor(user::getCurrentUserId());
    }

    public function createStream(validStreamName $name, validStreamDescription $info,
                                 ArrayObject $genres, validPermalink $permalink, validCategory $category) {

        $ids = implode(',', $genres->getArrayCopy());

        $fluentPDO = Database::getFluentPDO();

        $query = $fluentPDO->insertInto("r_streams")->values([
            'uid' => $this->visitor->getId(),
            'name' => $name->get(),
            'info' => $info->get(),
            'hashtags' => $ids,
            'permalink' => $permalink,
            'category' => $category,
            'created' => time()
        ])->getQuery();

        $result = $this->database->query_update($query);

/*        $result = $this->database->query_update(self::SQL_NEW_STREAM,
            array($this->visitor->getId(), $name, $info, $ids, $permalink, $category, time())); */

        if ($result === 0) {
            throw new streamException("Can't create new stream", 1001, null);
        }

        $id = $this->database->lastInsertId();

        $stream = new radioStream($id);

        return misc::okJSON($stream->toArray());
    }
}
