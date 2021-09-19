<?php

/**
 * Performs miscellaneous cross attribute validation and filtering for
 * HTML5 input elements. This is meant to be a post-transform.
 */
class HTMLPurifier_AttrTransform_HTML5_Input extends HTMLPurifier_AttrTransform
{
    /**
     * @param array $attr
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return array|bool
     */
    public function transform($attr, $config, $context)
    {
        $t = isset($attr['type']) ? $attr['type'] : 'text';

        // Type failed Attr.AllowedInputTypes validation - the element has to be removed
        if ($t === false) {
            return false;
        }

        $t = strtolower($t);
        $attr['type'] = $t;

        // For historical reasons, the name isindex is not allowed
        // https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fe-name
        if (isset($attr['name']) && $attr['name'] === 'isindex') {
            unset($attr['name']);
        }

        // Non-empty 'alt' attribute is required for 'image' input
        if ($t === 'image' && !isset($attr['alt'])) {
            $alt = trim($config->get('Attr.DefaultImageAlt'));
            if ($alt === '') {
                $name = isset($attr['name']) ? trim($attr['name']) : '';
                $alt = $name !== '' ? $name : 'image';
            }
            $attr['alt'] = $alt;
        }

        // Remove attributes not allowed for provided input type
        if (isset($attr['checked']) && $t !== 'radio' && $t !== 'checkbox') {
            unset($attr['checked']);
        }
        if (isset($attr['maxlength']) && $t !== 'text' && $t !== 'password') {
            unset($attr['maxlength']);
        }
        if (isset($attr['src']) && $t !== 'image') {
            unset($attr['src']);
        }

        // The value attribute is always optional, though should be considered
        // mandatory for checkbox, radio, and hidden.
        // https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-value
        // Nu Validator diverges from the WHATWG spec, as it defines 'value'
        // attribute as required, where in fact it is optional, and may be an empty string:
        // https://html.spec.whatwg.org/multipage/input.html#button-state-(type=button)
        if (!isset($attr['value']) && ($t === 'checkbox' || $t === 'radio' || $t === 'hidden')) {
            $attr['value'] = '';
        }

        return $attr;
    }
}
