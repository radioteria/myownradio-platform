<?php

/* error_404.tmpl */
class __TwigTemplate_d3b7125587db5468d4bb667ef00ee04ea3d5629512d67492858716780225acfd extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
    <title>404 - Document not found</title>
    <link rel=\"stylesheet\" href=\"/backend/styles/error.css\" type=\"text/css\"/>
</head>
<body>
<div class=\"wrap\">
    <h1>ERROR 404</h1>
    <div id=\"body\">Document you requested could not be found</div>
</div>
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "error_404.tmpl";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }
}
