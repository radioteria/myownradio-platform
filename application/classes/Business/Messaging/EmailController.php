<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 08.07.2015
 * Time: 9:48
 */

namespace Business\Messaging;


use Framework\Services\Mailer;
use Objects\Letter;
use Objects\LetterEvent;
use Tools\Singleton;
use Tools\SingletonInterface;

class EmailController implements SingletonInterface {

    use Singleton;

    protected function __construct() {}

    public function process($limit = 20) {
        $letters = Letter::getList($limit);
        /** @var Letter $letter */
        foreach ($letters as $letter) {
            $this->send($letter);
        }
    }

    private function send(Letter $letter) {

        $letter_hash = $letter->getLetterHash();

        if (LetterEvent::getByFilter("hash", array($letter_hash))->nonEmpty()) return;

        $email = new Mailer(REG_MAIL, REG_NAME);
        $email->setSubject($letter->getSubject());
        $email->setContentType("text/html");
        $email->addAddress($letter->getTo());
        $email->setBody($letter->getBody());

        if ($email->send()) {

            $event = new LetterEvent();
            $event->setSubject($letter->getSubject());
            $event->setHash($letter_hash);
            $event->setTo($letter->getTo());
            $event->save();

            $letter->delete();

        } else {

            $letter->setStatus(-1);
            $letter->save();

        }
    }

}