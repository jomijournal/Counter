<?php
//Generates a failure message 
	function fail($message, $errorNumber){
		echo <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<ReportResponse ID="22" Created="$currentDate">
EOT;
		if($requestorElement !== False) echo $requestorElement->asXML();
		if($customerElement !== False) echo $customerReferenceElement->asXML();
		if($reportElement !== False) echo $reportElement->asXML();
		echo <<<EOT
		<Report></Report>
		<Exception>
			<Number>$errorNumber</Number>
			<Severity>Crucial</Severity>
			<Message>$message</Message>
		</Exception>
	</Report>
</ReportResponse>
EOT;
	}
?>