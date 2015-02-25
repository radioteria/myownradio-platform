<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.02.15
 * Time: 11:16
 */

namespace Framework\View\Errors;


use Framework\Exceptions\ApplicationException;
use Framework\Template;

class ViewException extends ApplicationException {

    private $httpCode, $httpTemplate, $templateData;

    function __construct($httpCode, $httpTemplate, $templateData = []) {
        $this->httpCode     = $httpCode;
        $this->httpTemplate = $httpTemplate;
        $this->templateData = $templateData;
    }

    function drawTemplate() {
        $template = new Template($this->httpTemplate);
        $template->putObject($this->templateData);

        $template->setPrefix("{{");
        $template->setSuffix("}}");

        http_response_code($this->httpCode);
        echo $template->makeDocument();
    }

} 