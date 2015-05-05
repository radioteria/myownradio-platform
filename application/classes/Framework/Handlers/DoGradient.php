<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Framework\Context;
use Framework\Controller;
use Framework\FileServer\FSFile;
use Framework\Services\Annotations\Annotation;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\JsonResponse;
use Objects\FileServer\FileServerFile;

class DoGradient implements Controller {
    public function doGet(JsonResponse $response) {
        $response->setData($this->parseAnnotation('@MyAnnotation({"key": "Hello, World"})')->getDefault());
    }

    /**
     * @param $annotation
     * @return Annotation
     */
    private function parseAnnotation($annotation) {

        if (preg_match('~^\@(\w+)$~', $annotation, $match)) {
            return new Annotation($match[1], null);
        } elseif (preg_match('~^\@(\w+)\((.+)\)$~', $annotation, $match)) {
            return new Annotation($match[1], ["value" => json_decode($match[2], true)]);
        }

    }
} 