<?php

function _U2RFC822_modifier($currdate) {

	if (is_numeric($currdate))
		$date = date('Y-m-d H:i:s', $currdate);
	else
		$date = $currdate;
	
	$datatime = explode(" ",$date);
	$dater = explode("-",$datatime[0]);
	$timer = explode(":",$datatime[1]);

	return date('r', mktime($timer[0], $timer[1], $timer[2], $dater[1], $dater[2], $dater[0]));

}