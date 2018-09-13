<?
	
	function _helper_date_W3C($date, $time = True)
	{
	
		$currdate = explode(' ', $date);
		
		if (!$time) 
			return $currdate[0];
		else 
			return $currdate[0].'T'.$currdate[1].'+0400';
			


} 

?>