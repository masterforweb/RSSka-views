<?php

function date_ISO_8601($currdate) {

	if (is_numeric($currdate))
		$date = date('Y-m-d H:i:s', $currdate);
	else
		$date = $currdate;

	$date = str_replace(" ", "T", $date);
	$date = $date.'Z';

	return $date;

}