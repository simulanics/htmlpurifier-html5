<?php

class HTMLPurifier_HTMLModule_HTML5_Forms_InputTest extends HTMLPurifier_HTMLModule_HTML5_AbstractTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->config->set('HTML.Forms', true);
    }

    public function dataProvider()
    {
        return array(
            'input no type' => array(
                '<input>',
                '<input type="text">',
            ),
            'input empty type' => array(
                '<input type="">',
                '<input type="text">',
            ),
            'input isindex name' => array(
                '<input type="text" name="isindex">',
                '<input type="text">',
            ),
            'input is structured inline' => array(
                '<p><input type="text"></p>',
            ),
            'input is strictly inline' => array(
                '<p><dfn><input type="text"></dfn></p>',
            ),
        );
    }
}
