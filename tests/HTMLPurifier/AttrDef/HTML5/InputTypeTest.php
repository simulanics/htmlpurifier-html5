<?php

/**
 * @property HTMLPurifier_AttrDef_HTML5_InputType $attr
 */
class HTMLPurifier_AttrDef_HTML5_InputTypeTest extends AttrDefTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->attr = new HTMLPurifier_AttrDef_HTML5_InputType();
    }

    public function testDefault()
    {
        $this->assertValidate('checkbox');
    }

    public function testNullAllowedInputTypes()
    {
        $this->config->set('Attr.AllowedInputTypes', null);
        $this->assertValidate('checkbox');
    }

    public function testEmptyAllowedInputTypes()
    {
        $this->config->set('Attr.AllowedInputTypes', array());
        $this->assertValidate('checkbox', false);
    }

    public function testInvalidAllowedInputTypes()
    {
        $this->config->set('Attr.AllowedInputTypes', array('foo'));
        $this->assertValidate('foo', false);
        $this->assertValidate('checkbox', false);
    }

    public function testEmpty()
    {
        $this->assertValidate('', 'text');
    }
}
