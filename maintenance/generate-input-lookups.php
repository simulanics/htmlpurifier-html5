#!/usr/bin/php
<?php

$dom = new DOMDocument('1.0', 'UTF-8');
if (!$dom->loadHTMLFile('https://html.spec.whatwg.org/multipage/input.html', LIBXML_NOERROR)) {
    echo "Unable to fetch input element spec\n";
    exit(1);
}

$LIB_DIR = __DIR__ . '/../library/';

$input_types = extract_input_types($dom);
$input_types_lookup = array_map(function () { return true; }, $input_types);
update_file($LIB_DIR . '/HTMLPurifier/AttrDef/HTML5/InputType.php', 'protected static $values = ', $input_types_lookup);

/**
 * Extracts input types from https://html.spec.whatwg.org/multipage/input.html#attr-input-type-keywords
 * @return array
 */
function extract_input_types(DOMDocument $dom) {
    $table = $dom->getElementById('attr-input-type-keywords');

    /** @var DOMElement $thead */
    $thead = $table->getElementsByTagName('thead')->item(0);

    $columns = array();
    foreach (iterator_to_array($thead->getElementsByTagName('th')) as $th) {
        $columns[] = trim($th->textContent);
    }

    /** @var DOMElement $tbody */
    $tbody = $table->getElementsByTagName('tbody')->item(0);

    $input_types = array();

    foreach (iterator_to_array($tbody->getElementsByTagName('tr')) as $tr) {
        /** @var DOMElement $tr */
        $values = array();
        foreach (iterator_to_array($tr->getElementsByTagName('td')) as $index => $td) {
            $values[$columns[$index]] = trim($td->textContent);
        }
        $input_types[$values['Keyword']] = $values['State'];
    }

    ksort($input_types);
    return $input_types;
}

function update_file($file, $token, $value) {
    $contents = file_get_contents($file);
    $pos_start = strpos($contents, $token);
    $pos_end = strpos($contents, ';', $pos_start);

    $new_contents = substr($contents, 0, $pos_start)
        . $token . dump_var($value, 4)
        . substr($contents, $pos_end);

    if ($contents !== $new_contents) {
        return file_put_contents($file, $new_contents);
    }
    return false;
}

function dump_var($input, $indent = 0) {
    $output = var_export($input, true);
    $output = str_replace('array (', 'array(', $output);
    $output = preg_replace('/=>\s*array/', '=> array', $output);
    $output = str_replace('  ', '    ', $output);

    $indent = str_repeat(' ', (int) $indent);
    $output = str_replace("\n", "\n" . $indent, $output);

    return $output;
}
