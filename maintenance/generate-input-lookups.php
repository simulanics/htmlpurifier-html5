#!/usr/bin/php
<?php

$LIB_DIR = __DIR__ . '/../library/';
$SCRIPT_NAME = basename(__DIR__) . '/' . basename(__FILE__);

$URL = 'https://html.spec.whatwg.org/dev/input.html';
$ID_INPUT_TYPES_TABLE = 'attr-input-type-keywords';
$ID_INPUT_ATTRIBUTES_TABLE = 'input-type-attr-summary';

libxml_use_internal_errors(true);

$doc = new DOMDocument();
$doc->encoding = 'UTF-8';
$doc->loadHTMLFile($URL);

// Parse spec version
$finder = new DOMXPath($doc);
$pubDate = trim($finder->query("//*[contains(@class, 'pubdate')]")->item(0)->textContent);

// Parse input state -> input type mapping
$inputTypesByState = array();

$table = $doc->getElementById($ID_INPUT_TYPES_TABLE);
$tbody = $table->getElementsByTagName('tbody')->item(0);

foreach ($tbody->getElementsByTagName('tr') as $tr) {
    $tds = $tr->getElementsByTagName('td');
    $inputType = trim($tds->item(0)->textContent);
    $state = trim($tds->item(1)->textContent);

    $inputTypesByState[$state] = $inputType;
}

// Parse table with allowed attributes for each input type
$table = $doc->getElementById($ID_INPUT_ATTRIBUTES_TABLE);
$thead = $table->getElementsByTagName('thead')->item(0);
$tbody = $table->getElementsByTagName('tbody')->item(0);

$columns = array();
foreach ($thead->getElementsByTagName('th') as $th) {
    $columns[] = array_filter(
        array_map(
            function ($state) use ($inputTypesByState) {
                $state = trim($state);
                return isset($inputTypesByState[$state]) ? $inputTypesByState[$state] : null;
            },
            explode(',', $th->textContent)
        ),
        'strlen'
    );
}
array_shift($columns); // first column stores attribute names

$attributes = array();
foreach ($tbody->getElementsByTagName('tr') as $tr) {
    $attribute = trim($tr->getElementsByTagName('th')->item(0)->textContent);

    foreach ($tr->getElementsByTagName('td') as $i => $td) {
        if (trim($td->textContent) === 'Yes') {
            foreach ($columns[$i] as $inputType) {
                $attributes[$attribute][$inputType] = true;
            }
        }
    }
}

// Attribute summary table doesn't include data for 'value' attribute, so we have to
// hardcode it here. As per:
// https://html.spec.whatwg.org/dev/input.html#file-upload-state-(type=file)
// https://html.spec.whatwg.org/dev/input.html#image-button-state-(type=image)
// 'value' attribute is not allowed only on 'file' and 'image' input types.
foreach ($inputTypesByState as $type) {
    if ($type !== 'image' && $type !== 'file') {
        $attributes['value'][$type] = true;
    }
}

foreach ($attributes as $attribute => $_) {
    ksort($attributes[$attribute]);
}
ksort($attributes);

update_property(
    $LIB_DIR . '/HTMLPurifier/AttrTransform/HTML5/Input.php',
    'protected static $attributes',
    $attributes,
    "
        Allowed attributes vs input type lookup
        @var array
        @link {$URL}#{$ID_INPUT_ATTRIBUTES_TABLE}
        @version {$pubDate}
        @note Generated by {$SCRIPT_NAME} script
    "
);

function update_property($file, $property, $value, $comment = null) {
    $token = trim($property) . ' = ';

    $contents = file_get_contents($file);

    $propertyStart = strpos($contents, $token);
    $propertyEnd = strpos($contents, ';', $propertyStart);

    $indentStart = strrpos(substr($contents, 0, $propertyStart), "\n") + 1;
    $indent = substr($contents, $indentStart, $propertyStart - $indentStart);

    $newContents = substr($contents, 0, $propertyStart)
        . $token . dump_var($value, strlen($indent))
        . substr($contents, $propertyEnd);

    $comment = trim(str_replace(array("/**", "*/"), "", $comment));
    if ($comment) {
        $docStart = strrpos(substr($newContents, 0, $propertyStart), '/**');
        $docEnd = strpos($newContents, "*/", $docStart) + 2;

        $docComment = "/**\n{$indent} * "
            . implode("\n{$indent} * ", array_map('trim', explode("\n", $comment)))
            . "\n{$indent} */";

        $newContents = substr($newContents, 0, $docStart)
            . $docComment
            . substr($newContents, $docEnd);
    }

    if ($contents !== $newContents) {
        return file_put_contents($file, $newContents);
    }
    return false;
}

function dump_var($input, $indent = 0) {
    $output = var_export($input, true);
    $output = str_replace('array (', 'array(', $output);
    $output = preg_replace('/=>\s*array/', '=> array', $output);
    $output = str_replace('  ', '    ', $output);

    $indent = str_repeat(' ', (int) $indent);
    return str_replace("\n", "\n" . $indent, $output);
}