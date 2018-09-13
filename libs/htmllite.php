<?php

function htmllite($html) {

	//чистим стили
	$html = strip_tags($html, '<p><b><i><ul><ol><li><h1><h2><h3><a><div>');
	$tags = array('<p><b><i><ul><ol><li><h1><h2><h3><a><div><a><img>');
	
	$attr = array('href', 'src');

	$html = stripTagsAttributes($html, $tags, $attr);

	$html = tag_replace($html, 'div', 'p');
	$html = tag_replace($html, 'h1', 'b');
	$html = tag_replace($html, 'h2', 'b');
	$html = tag_replace($html, 'h3', 'b');

    $html = nl2br($html); 

	return $html;

}


function tag_replace($html, $t1, $t2) {
	

	$result = str_replace('<'.$t1.'>', '<'.$t2.'>', $html);
	$result = str_replace('</'.$t1.'>', '</'.$t2.'>', $result);

	return $result;

}


function stripTagsAttributes($html, $allowedTags = array(), $allowedAttributes = array('(?:a^)')) {
    if (!empty($html)) {
        $theTags = count($allowedTags) ? '<' . implode('><', $allowedTags) . '>' : '';
        $theAttributes = '%' . implode('|', $allowedAttributes) . '%i';
        $dom = @DOMDocument::loadHTML(
            mb_convert_encoding(
                strip_tags(
                    $html,
                    $theTags
                ),
                'HTML-ENTITIES',
                'UTF-8'
            )
        );
        $xpath = new DOMXPath($dom);
        $tags = $xpath->query('//*');
        foreach ($tags as $tag) {
            $attrs = array();
            for ($i = 0; $i < $tag->attributes->length; $i++) {
                $attrs[] = $tag->attributes->item($i)->name;
            }
            foreach ($attrs as $attribute) {
                if (!preg_match($theAttributes, $attribute)) {
                    $tag->removeAttribute($attribute);
                } elseif (preg_match('%^(?:href|src)$%i', $attribute) and preg_match('%^javascript:%i', $tag->getAttribute($attribute))) {
                    $tag->setAttribute($attribute, '#');
                }
            }
        }
        return (
            trim(
                strip_tags(
                    html_entity_decode(
                        $dom->saveHTML()
                    ),
                    $theTags
                )
            )
        );
    }
}


