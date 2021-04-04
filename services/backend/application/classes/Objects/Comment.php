<?php
/**
 * Created by PhpStorm.
 * User: LRU
 * Date: 28.02.2015
 * Time: 18:29
 */

namespace application\classes\Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

class Comment extends ActiveRecordObject implements ActiveRecord {

    private $comment_id, $comment_stream, $comment_user, $comment_body, $comment_date;

    /**
     * @return mixed
     */
    public function getCommentId() {
        return $this->comment_id;
    }

    /**
     * @return mixed
     */
    public function getCommentStream() {
        return $this->comment_stream;
    }

    /**
     * @return mixed
     */
    public function getCommentUser() {
        return $this->comment_user;
    }

    /**
     * @return mixed
     */
    public function getCommentBody() {
        return $this->comment_body;
    }

    /**
     * @return mixed
     */
    public function getCommentDate() {
        return $this->comment_date;
    }

    /**
     * @param mixed $comment_stream
     */
    public function setCommentStream($comment_stream) {
        $this->comment_stream = $comment_stream;
    }

    /**
     * @param mixed $comment_user
     */
    public function setCommentUser($comment_user) {
        $this->comment_user = $comment_user;
    }

    /**
     * @param mixed $comment_body
     */
    public function setCommentBody($comment_body) {
        $this->comment_body = $comment_body;
    }

    /**
     * @param mixed $comment_date
     */
    public function setCommentDate($comment_date) {
        $this->comment_date = $comment_date;
    }

}