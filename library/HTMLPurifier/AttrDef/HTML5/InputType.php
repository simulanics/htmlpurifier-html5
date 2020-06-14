<?php

class HTMLPurifier_AttrDef_HTML5_InputType extends HTMLPurifier_AttrDef
{
    /**
     * Lookup table for valid values
     * @var array
     * @see https://html.spec.whatwg.org/dev/input.html#attr-input-type
     * @note Generated by maintenance/generate-input-lookups.php script
     */
    protected static $values = array(
        'button' => true,
        'checkbox' => true,
        'color' => true,
        'date' => true,
        'datetime-local' => true,
        'email' => true,
        'file' => true,
        'hidden' => true,
        'image' => true,
        'month' => true,
        'number' => true,
        'password' => true,
        'radio' => true,
        'range' => true,
        'reset' => true,
        'search' => true,
        'submit' => true,
        'tel' => true,
        'text' => true,
        'time' => true,
        'url' => true,
        'week' => true,
    );

    /**
     * @var array
     */
    protected $allowed;

    /**
     * Return lookup table for valid 'type' values
     *
     * @return array
     * @codeCoverageIgnore
     */
    public static function values()
    {
        return self::$values;
    }

    protected function init(HTMLPurifier_Config $config)
    {
        if ($this->allowed === null) {
            $allowedInputTypes = isset($config->def->info['Attr.AllowedInputTypes'])
                ? $config->get('Attr.AllowedInputTypes')
                : null;

            if (is_array($allowedInputTypes)) {
                $allowed = array_intersect_key($allowedInputTypes, self::$values);
            } else {
                $allowed = self::$values;
            }
            $this->allowed = $allowed;
        }
    }

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        $this->init($config);

        $type = strtolower($this->parseCDATA($string));
        if ($type === '') {
            $type = 'text';
        }

        // The datetime input type field has been removed from WHATWG HTML and replaced
        // with datetime-local, see: https://github.com/whatwg/html/issues/336
        if ($type === 'datetime') {
            $type = 'datetime-local';
        }

        if (!isset($this->allowed[$type])) {
            return false;
        }

        return $type;
    }
}
