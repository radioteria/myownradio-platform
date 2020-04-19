<?php

/* track.extra.info.tmpl */
class __TwigTemplate_7f2b973e87e5feb84ace5911ed035399f2ffa506212b9a50fa521b3cf7733fb2 extends Twig_Template
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
        echo "<div class=\"track-extra-info\">
    <table class=\"track-info\">
        <tr>
            <th colspan=\"2\">Track Information</th>
        </tr>
        <tr>
            <td>Filename</td>
            <td>";
        // line 8
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "file_name", array()), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <td>Track ID</td>
            <td>";
        // line 12
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "id", array()), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <td>Uploaded</td>
            <td>";
        // line 16
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "uploaded", array()), "M d, Y H:i:s"), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <td>Duration</td>
            <td>";
        // line 20
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('ms2time')->getCallable(), array($this->getAttribute((isset($context["track"]) ? $context["track"] : null), "duration", array()))), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <th colspan=\"2\">Track Metadata</th>
        </tr>
        <tr>
            <td>Title</td>
            <td>";
        // line 27
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "title", array()), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <td>Artist</td>
            <td>";
        // line 31
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "artist", array()), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <td>Album</td>
            <td>";
        // line 35
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "album", array()), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <td>Genre</td>
            <td>";
        // line 39
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "genre", array()), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <td>Track #</td>
            <td>";
        // line 43
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["track"]) ? $context["track"] : null), "track_number", array()), "html", null, true);
        echo "</td>
        </tr>
        <tr>
            <th colspan=\"2\">Additional Info</th>
        </tr>
        <tr>
            <td>Appears on</td>
            <td>
                ";
        // line 51
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["appears"]) ? $context["appears"] : null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["a"]) {
            // line 52
            echo "                    <div class=\"stream\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["a"], "name", array()), "html", null, true);
            echo " - ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["a"], "times", array()), "html", null, true);
            echo " time(s)</div>
                ";
            $context['_iterated'] = true;
        }
        if (!$context['_iterated']) {
            // line 54
            echo "                    <i>No appearances</i>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['a'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 56
        echo "            </td>
        </tr>
    </table>
</div>";
    }

    public function getTemplateName()
    {
        return "track.extra.info.tmpl";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  120 => 56,  113 => 54,  103 => 52,  98 => 51,  87 => 43,  80 => 39,  73 => 35,  66 => 31,  59 => 27,  49 => 20,  42 => 16,  35 => 12,  28 => 8,  19 => 1,);
    }
}
