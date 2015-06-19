<?php
	require_once "config.php";
	require_once "failure.php"
	
	// $responseText is a string response from the clicky API that is an XML file
	// This file hopefully contains information on pageviews by ip addresses
	
	// This should return an integer number of visits
	function parseClickyData($responseText) {
		$xml = simplexml_load_string($responseText) or die("Unable to parse Clicky data");
		
		$visitData = $xml->xpath("type[@type=\"visitors\"]/date/item/value") or die("Unable to parse Clicky data");
		
		
		// Do the parsing...
		return intval((string)$visitData);;
	}
	
	function getDaysInMonth($month){
		return cal_days_in_month(CAL_GREGORIAN,
		                         intval(substr($month, -2)) + 1, 
		                         intval(substr($month, 0, 4)) + 1900);
	}
	
	// Month is in format YYYY-MM
	function getClickyData($month, $ipList){
		$daysInMonth = getDaysInMonth($month);
		$range = [1, 0];
		
		$totalVisits = 0;
		
		while($range[1] < $daysInMonth) {
			$range[0] += $range[1] + 1;
			$range[1] = min($daysInMonth, $range[1] + 7);
			$dateRange = $month."-".sprintf("%02d",$range[0]).",".$month."-".sprintf("%02d",$range[1]);
			$url = "http://api.clicky.com/api/stats/4?site_id".
					$site_id."&sitekey=".$site_key.
					"&type=segmentation,visitors&date=".$dateRange.
					"&output=xml&ip_address=".$ipList;
			
			$responseText = http_get($url) or fail("Failed to load analytics", 11);
			$totalVisits += parseClickyData($responseText);
		}
		
		return $totalVisits;
	}
?>