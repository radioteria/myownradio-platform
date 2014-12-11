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

    /**
     * @param validStreamName $name
     * @param validStreamDescription $info
     * @param ArrayObject $genres
     * @param validPermalink $permalink
     * @param validCategory $category
     * @return string
     * @throws streamException
     */
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

        if ($result === 0) {
            throw new streamException("Can't create new stream", 1001, null);
        }

        $id = $this->database->lastInsertId();

        return misc::okJSON(Streams::getOneStream($id));

    }

    // todo: fix outputJSON -> throw
    public function uploadTrack(array $file) {

        $visitorPlan = VisitorPlan::getInstance($this->visitor->getId());
        $visitorStats = VisitorStats::getInstance($this->visitor->getId());

        $timeLeftOnAccount = $visitorPlan->getTimeLimit() - $visitorStats->getTracksDuration();

        // Check file type is supported
        if(array_search($file['type'], config::getSetting('upload', 'supported_audio')) === false) {
            return misc::outputJSON("UPLOAD_ERROR_UNSUPPORTED");
        }

        $audio_tags = misc::get_audio_tags($file['tmp_name']);

        if(empty($audio_tags['DURATION']) || $audio_tags['DURATION'] == 0)
        {
            return misc::outputJSON("UPLOAD_ERROR_CORRUPTED_AUDIO");
        }

        if($audio_tags['DURATION'] > config::getSetting('upload', 'maximal_length'))
        {
            return misc::outputJSON("UPLOAD_ERROR_LONG_AUDIO");
        }

        // todo: fix this
        if($audio_tags['DURATION'] > $timeLeftOnAccount && $visitorPlan->getTimeLimit())
        {
            return misc::outputJSON("UPLOAD_ERROR_NO_SPACE");
        }

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

        $fluent = $this->database->getFluentPDO();
        $query = $fluent->insertInto("r_tracks")->values([
            "uid"           => $this->visitor->getId(),
            "filename"      => $file["name"],
            "ext"           => $extension,
            "track_number"  => Optional::ofEmpty($audio_tags["TRACKNUMBER"])    ->getOrElseEmpty(),
            "artist"        => Optional::ofEmpty($audio_tags["PERFORMER"])      ->getOrElseEmpty(),
            "title"         => Optional::ofEmpty($audio_tags["TITLE"])          ->getOrElse($file['name']),
            "album"         => Optional::ofEmpty($audio_tags["ALBUM"])          ->getOrElseEmpty(),
            "genre"         => Optional::ofEmpty($audio_tags["GENRE"])          ->getOrElseEmpty(),
            "date"          => Optional::ofEmpty($audio_tags["RECORDED_DATE"])  ->getOrElseEmpty(),
            "duration"      => $audio_tags["DURATION"],
            "filesize"      => filesize($file["tmp_name"]),
            'uploaded'      => time()
        ]);

        $last_id = $this->database->executeInsert($query->getQuery(false), $query->getParameters());

        if(!$last_id) {
            return misc::outputJSON("UPLOAD_WAS_NOT_ADDED");
        }

        $temp_track = new radioTrackItem($last_id);

        if(move_uploaded_file($file['tmp_name'], $temp_track->getDetails()->getOriginalFile()->path())) {
            misc::writeDebug(sprintf("User #%s successfully uploaded track \"%s\"", user::getCurrentUserId(), $file['name']));
            return misc::outputJSON("UPLOAD_SUCCESS", $temp_track->getDetails()->toArray());
        } else {
            return misc::outputJSON("UPLOAD_ERROR_DISK_ACCESS_ERROR");
        }

    }
}
