<?php

class HTMLPurifier_AttrDef_HTML5_InputType extends HTMLPurifier_AttrDef
{
    /**
     * Lookup table for valid values
     * @var array
     */
    protected static $values = array(
        'button' => true,
        'checkbox' => true,
        'file' => true,
        'hidden' => true,
        'image' => true,
        'password' => true,
        'radio' => true,
        'reset' => true,
        'submit' => true,
        'text' => true,
    );

    /**
     * @var array
     */
    protected $allowed;

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

        if (!isset($this->allowed[$type])) {
            return false;
        }

        return $type;
    }
}
