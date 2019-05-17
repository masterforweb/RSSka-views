<?php

function textlite($text){

    $text = str_replace('</p>', '<br>', $text);
    $text = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    $text = strip_tags($text);

    $text = xmlentities($text);

    return $text;

}


function xmlentities($s) {
    static $patterns = null;
    static $reps = null;
    static $tbl = null;
    if ($tbl === null) {
        $tbl = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
        foreach ($tbl as $k => $v) {
            $patterns[] = "/$v/";
            $reps[] = '&#' . ord($k) . ';'
        }
    }
    return preg_replace($patterns, $reps, htmlentities($s, ENT_QUOTES, 'UTF-8'));
}