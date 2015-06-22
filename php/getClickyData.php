<?php
	require_once COUNTER_PATH."config.php";
	require_once COUNTER_PATH."failure.php";
	
	

	// $responseText is a string response from the clicky API that is an XML file
	// This file hopefully contains information on pageviews by ip addresses
	
	// This should return an integer number of visits
	function parseClickyData($responseText, $requiredErrorInfo) {
	
		$xml = simplexml_load_string($responseText) or die("Unable to parse Clicky data");
		
		$visitData = $xml->xpath("type[@type=\"visitors\"]/date/item/value") or fail("Unable to parse Clicky data", 12, $requiredErrorInfo);
		
		return intval((string)($visitData[0]));
	}
	
	function getDaysInMonth($month){
		return cal_days_in_month(CAL_GREGORIAN,
		                         intval(substr($month, -2)), 
		                         intval(substr($month, 0, 4)));
	}
	
	// Month is in format YYYY-MM
	function fetchClickyData($month, $ipList, $requiredErrorInfo){
		global $site_id, $site_key;
		$daysInMonth = getDaysInMonth($month);
		$range = [1, 0];
		
		$totalVisits = 0;
		
		while($range[1] < $daysInMonth) {
			$range[0] += $range[1] + 1;
			$range[1] = min($daysInMonth, $range[1] + 7);
			$dateRange = $month."-".sprintf("%02d",$range[0]).",".$month."-".sprintf("%02d",$range[1]);
			$url = "http://api.clicky.com/api/stats/4?site_id=".
					$site_id."&sitekey=".$site_key.
					"&type=segmentation,visitors&date=".$dateRange.
					"&output=xml&ip_address=".$ipList;
			
			// Note that wp_remote_get must be used as the wordpress equivelant of http_get
			$responseText = wp_remote_get($url) or fail("Failed to load analytics", 11, $requiredErrorInfo);
			if($responseText["response"]["code"] != 200)
				fail("Failed to load analytics", 11, $requiredErrorInfo);
			$totalVisits += parseClickyData($responseText["body"], $requiredErrorInfo);
		}
		
		return $totalVisits;
	}
	
	function getCachedData($cacheFile){
		return intval(file_get_contents($cacheFile));
	}
	
	function findClickyData($month, $ipList, $cacheFile, $requiredErrorInfo){
		if(file_exists($cacheFile) && date('Y-m', time()) != $month){
			return getCachedData($cacheFile);
		} else {
			$data = fetchClickyData($month, $ipList, $requiredErrorInfo);
			file_put_contents($cacheFile, $data);
			return $data;
		}
	}
?>