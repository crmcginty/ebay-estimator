<?php
	include 'scripts/php/db_objects.php';

	// Create a database object
	$db = new DB();

	// Queries
	$titles = $db -> query("SELECT * FROM titles ORDER BY title DESC LIMIT 5");
	$eras = $db -> query("SELECT * FROM eras ORDER BY name ASC");
	$publishers = $db -> query("SELECT * FROM publisher ORDER BY name ASC");

	// Function to retrieve 'name' values using an ID
	function getName($id, $arrayName) {
		global ${$arrayName};
		foreach(${$arrayName} as $row) {
		  if($row['id'] == $id) {
		    return $row['name'];
		  }
		}
	}

	// Function to construct the eBay query string
	function getQuery($title, $volume, $issue, $issue_detail, $date, $variantBool, $annualBool, $notes) {
		$yearArray = explode(" ", $date);
		$notesArray = explode(" ", $notes);
		$year = $variant = $annual = '';
		$year = ($yearArray[1] != null ? $yearArray[1] : $yearArray[0]);
		if($annualBool == 1) $annual = ' Annual';
		if($notesArray) {
			$numItems = count($notesArray);
			$i = 0;
			$notesQuery = "(";
			foreach($notesArray as $n){
				if(++$i === $numItems) {
					$notesQuery .= $n .")"; // Last item in the notes array
				} else {
					$notesQuery .= $n . ",";
				}
			}
		}
		$variant = ($variantBool == 1 ? $notesQuery : '-Variant -signed');		
		$query = '"' .$title .' #' .$issue .'" ' .$year .' ' .$annual .' ' .$variant .' -complete -sketch -cgc_grading';
		return $query;	
	}

	// Function to get the estimated value using ebay sold prices
	function getValue($query) {

		set_time_limit(0); // v.1 is slow so set a time out until this is optimised!

		error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging

		// API request variables
		$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // eBay URL
		$version = '1.0.0';  // API version
		$appid = '#';  // Personal eBay appID
		$globalid = 'EBAY-GB';  // eBay UK
		$safequery = urlencode($query);  // Make the query URL-friendly

		// Construct the findCompletedItems HTTP GET call (sold items)
		$apicall = "$endpoint?";
		$apicall .= "OPERATION-NAME=findCompletedItems";
		$apicall .= "&SERVICE-VERSION=$version";
		$apicall .= "&SECURITY-APPNAME=$appid";
		$apicall .= "&GLOBAL-ID=$globalid";
		$apicall .= "&keywords=$safequery";
		$apicall .= "&paginationInput.entriesPerPage=3";

		// Load the call and capture the document returned by eBay API
		$resp = simplexml_load_file($apicall);

		// Array for average price
		$priceArray = array();		
		
		// Check to see if the request was successful
		if ($resp->ack == "Success") {
		  // If the response was loaded, get the sold prices
		  foreach($resp->searchResult->item as $item) {
		    $price = $item->sellingStatus->currentPrice;
		    array_push($priceArray, floatval($price));
		  }
		  // Calculate and return the average sold price
		  $averagePrice = array_sum($priceArray) / count($priceArray);
		  return round($averagePrice, 2);
		}
	}	
?>