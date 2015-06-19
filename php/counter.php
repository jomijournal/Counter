<?php
	/******************************************************************************************
	*                                                                                         *
	* counter.php                                                                             *
	*                                                                                         *
	* Nolan Hawkins                                                                           *
	*                                                                                         *
	* The access point for the SUSHI protocol                                                 *
	* Make valid SUSHI requests to this page, with the requestor ID set to the ID JoMI holds  *
	* internally for each institution and with a date range set, and a COUNTER report will    *
	* be generated which tells the monthly usage for that institurion in that date range of   *
	* of JOMI. (This monthly usage is currently in  humber of unique visitors).               *
	*                                                                                         *
	* No testing has been done yet, but hopefully this works somewhat correctly.              *	
	* TODO: Testing, Caching, front end stuff                                                 *
	*                                                                                         *
	*                                                                                         *
	******************************************************************************************/
	
	$currentDate = date('m/d/Y h:i:s a', time());
	$requestorElement = False;
	$customerElement  = False;
	$reportElement    = False;
	
	// config.php stores credentials
	require_once "config.php";
	// failure.php stores the fail() function, which handles everything that could go wrong
	require_once "failure.php";
	// getClickyData.php gets information from clicky about a particular month
	require_once "getClickyData.php";
	
	// Given an institution id, return the associated IPs in the format that clicky wants
	//     This format uses commas between IPs to designate ranges and pipes between IPs to
	//     designate seperate blocks. For example, "0000000000,0000000010|10000000,100000010"
	//     are clearly not real IPs, but would designate two blocks from 00000000 to 000000010
	//     and from 100000000 to 10000000010. Disregard the random amounts of zeros I used.
	// If the institution id does not have any associated IPs, it returns an empty string, in which
	// case all of our usage data is returned.
	
	function getIPsFromID($rID) {
		// Create connection to our MySQL database
		$conn = new mysqli($db["host"], $db["user"], $db["pwd"], $db["db"]);

		// Check connection
		if ($conn->connect_error) {
			fail("Connection to MySQL failed: " . $conn->connect_error);
		}
		
		// query the database for ip addresses
		$sqlQuery = "SELECT start, end FROM SELECT wp_institution_ips WHERE location_id=".$rID;
		
		$result = $conn->query($sqlQuery);
		
		// generate list of ip addresses from rows of query
		// the response will be like:
		// [ ["start" => _start of ip block_, "end" => _end of ip block_], ... ]
		
		$ipList = "";
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$ipList = $ipList.$row["start"].",".$row["end"]."|";
			}
		}
		
		return $ipList;
	}
	
	// Returns an array of months that happen between (and including) the two dates, 
	// represented as strings in the form of "YYYY-MM"
	function getMonths($beginDate, $endDate){
		$bMonth = $beginDate["tm_month"]; $bYear = $beginDate["tm_year"];
		$eMonth =   $endDate["tm_month"]; $eYear =   $endDate["tm_year"];
		
		$months = array();
		
		while($bYear < $eYear ||
		   ($bYear == $eYear && $bMonth <= $eMonth)) {
			$months.append((string)($bYear + 1900)."-".sprintf("%02d",$bMonth + 1);
			if($bMonth == 11){
				$bMonth = 0;
				$bYear++;
			}else{
				$bMonth++;
			}
		}
		return $months;
	}
	
	
	// parse the relevant data from the posted XML file, in accordance with the SUSHI ReportRequest Schema defined at
	// http://www.niso.org/schemas/sushi/sushi1_7.xsd    and visualized at
	// http://www.niso.org/schemas/sushi/diagrams/sushi1_7_ReportRequest.png
	$xmlString = file_get_contents('php://input') or fail("Bad Request - No POSTed file", 1);
	
	$xml = simplexml_load_string($xmlstring) or fail("Bad Request - Couldn't parse POSTed file as XML", 2);
	
	
	$requestorElement = $xml->xpath("Requestor") or fail("Couldn't find Request element", 3);
	$customerElement  = $xml->xpath("CustomerReference") or fail("Couldn't find CustomerReference element", 4);
	$reportElement    = $xml->xpath("ReportDefinition") or fail("Couldin't find ReportDefinition", 5);
	
	$beginDateData = $xml->xpath("ReportDefinition/Filters/UsageDateRange/Begin") or fail("Bad Request - Begin Date not specified", 6);
	$beginDateData = (string)$beginDateData;
	
	$endDateData = $xml->xpath("ReportDefinition/Filters/UsageDateRange/End")or fail("Bad Request - End date not specified", 7);
	$endDateData = (string)$endDateData;
	
	$beginDate = strptime($beginDateData, "%Y-%m-%d") or fail("Bad Request - Begin Date formatted incorrectly", 8);
	$endDate   = strptime($endDateData  , "%Y-%m-%d") or fail("Bad Request - End Date formatted incorrectly", 9);
	
	$requestorID = $xml->xpath("Requestor/ID") or fail("Bad Request - Unspecified ID", 10);
	
	
	// generate list of ips from the requesting ID
	$ipList = getIPsFromID((string)$requestorID);
	
	$monthList = getMonths($beginDate, $endDate);
	
	$data = array();
	
	// for each month, reap the data with the function getClickyData, defined in getClickyData.php
	foreach($monthList as $month) {
		// define the end points of the date range for that month (daysInMonth is also defined in getClickyData.php)
		$monthStart = $month."-01";
		$monthEnd   = $month."-".sprintf("%02d",daysInMonth($month));
		$monthsData = [$monthStart, $monthEnd, getClickyData($month, $ipList)];
		$data->append($monthData);
	}
	
	
?>
<?xml version="1.0" encoding="UTF-8"?>
<ReportResponse ID="22" Created="<?php echo $currentDate; ?>">
	<?php 
		echo $requestorElement->asXML();
		echo $customerReferenceElement->asXML();
		echo $reportElement->asXML();
	?>
         <Report>
            <Report Name="JR1" Version="4.1" Created="<?php echo $currentDate ?>" ID="JR1" title="Journal Report 1">
               <Vendor>
                  <Name>Journal of Medical Insight</Name>
                  <ID>JoMI</ID>
                  <Contact>
                     <E-mail>nikitab@jomi.com</E-mail>
                  </Contact>
               </Vendor>
               <Customer>
                  <Name><?php echo $requestorName; ?>/Name>
                  <ID><?php echo $requestorID; ?></ID>
                  <ReportItems>
                     <ItemIdentifier>
                        <Type>Print_ISSN</Type>
                        <Value>2373-6003</Value>
                     </ItemIdentifier>
                     <ItemIdentifier>
                        <Type>Proprietary</Type>
                        <Value>32090</Value>
                     </ItemIdentifier>
                     <ItemPlatform>JoMI</ItemPlatform>
                     <ItemPublisher>Journal of Medical Insight</ItemPublisher>
                     <ItemName>Journal of Medical Insight</ItemName>
                     <ItemDataType>Journal</ItemDataType>
                     
<?php
	foreach($data as $month){
		echo <<<EOT
                     <ItemPerformance>
                        <Period>
                           <Begin>$month[0]</Begin>
                           <End>$month[1]</End>
                        </Period>
                        <Category>Requests</Category>
                        <Instance>
                           <MetricType>ft_total</MetricType>
                           <Count>$month[2]</Count>
                        </Instance>
                        <Instance>
                           <MetricType>ft_html</MetricType>
                           <Count>$month[2]</Count>
                        </Instance>
                        <Instance>
                           <MetricType>multimedia</MetricType>
                           <Count>$month[2]</Count>
                        </Instance>
                     </ItemPerformance>
EOT;
	}
?>
                  </ReportItems>
               </Customer>
            </Report>
         </Report>
      </ReportResponse>
   </s:Body>
</s:Envelope>
	
	
	
	
	
	
	