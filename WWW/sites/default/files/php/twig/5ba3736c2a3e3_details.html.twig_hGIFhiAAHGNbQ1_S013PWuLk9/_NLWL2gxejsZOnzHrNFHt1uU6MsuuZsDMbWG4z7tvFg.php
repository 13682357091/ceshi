<?php

/* themes/thunder_admin/templates/form/details.html.twig */
class __TwigTemplate_82abfd937ded18b39ef55977e0838f8f3ff65e3745af11ac7850760f73c1542c extends Twig_Template
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
        $tags = array("if" => 18, "set" => 20, "include" => 29);
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('if', 'set', 'include'),
                array(),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 17
        echo "<details";
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["attributes"] ?? null), "addClass", array(0 => "seven-details"), "method"), "html", null, true));
        echo " data-form-description-container>";
        // line 18
        if (($context["title"] ?? null)) {
            // line 20
            $context["summary_classes"] = array(0 => "seven-details__summary", 1 => ((            // line 22
($context["required"] ?? null)) ? ("js-form-required") : ("")), 2 => ((            // line 23
($context["required"] ?? null)) ? ("form-required") : ("")));
            // line 26
            echo "    <summary";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["summary_attributes"] ?? null), "addClass", array(0 => ($context["summary_classes"] ?? null)), "method"), "html", null, true));
            echo ">
      ";
            // line 27
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true));
            // line 28
            if (($context["description"] ?? null)) {
                // line 29
                $this->loadTemplate("@thunder_admin/button-description-toggle.html.twig", "themes/thunder_admin/templates/form/details.html.twig", 29)->display(array_merge($context, array("class" => "details__toggle-description")));
            }
            // line 31
            echo "</summary>";
        }
        // line 33
        echo "<div class=\"seven-details__wrapper details-wrapper\">
    ";
        // line 34
        if (($context["errors"] ?? null)) {
            // line 35
            echo "      <div class=\"form-item--error-message\">
        <strong>";
            // line 36
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["errors"] ?? null), "html", null, true));
            echo "</strong>
      </div>
    ";
        }
        // line 39
        if (($context["description"] ?? null)) {
            // line 40
            echo "<div class=\"seven-details__description js-details-description details-description\">";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["description"] ?? null), "html", null, true));
            echo "</div>";
        }
        // line 42
        if (($context["children"] ?? null)) {
            // line 43
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["children"] ?? null), "html", null, true));
        }
        // line 45
        if (($context["value"] ?? null)) {
            // line 46
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["value"] ?? null), "html", null, true));
        }
        // line 48
        echo "</div>
</details>
";
    }

    public function getTemplateName()
    {
        return "themes/thunder_admin/templates/form/details.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  99 => 48,  96 => 46,  94 => 45,  91 => 43,  89 => 42,  84 => 40,  82 => 39,  76 => 36,  73 => 35,  71 => 34,  68 => 33,  65 => 31,  62 => 29,  60 => 28,  58 => 27,  53 => 26,  51 => 23,  50 => 22,  49 => 20,  47 => 18,  43 => 17,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "themes/thunder_admin/templates/form/details.html.twig", "F:\\phpstudy\\WWW\\themes\\thunder_admin\\templates\\form\\details.html.twig");
    }
}
