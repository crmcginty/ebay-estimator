<?php

  $config = parse_ini_file("../../../../config/config_comics.ini");

  $query = $_GET['keywords'];

  // API request variables
  $endpoint = "http://svcs.ebay.com/services/search/FindingService/v1";  // eBay URL
  $version = '1.0.0';  // API version
  $appid = $config['ebayid'];  // Personal eBay appID
  $globalid = 'EBAY-GB';  // eBay UK
  $safequery = urlencode($query);  // Make the query URL-friendly

  // Construct the findCompletedItems HTTP GET call (sold items)
  $apicall = "$endpoint?";
  $apicall .= "OPERATION-NAME=findCompletedItems";
  $apicall .= "&SERVICE-VERSION=$version";
  $apicall .= "&SECURITY-APPNAME=$appid";
  $apicall .= "&RESPONSE-DATA-FORMAT=XML";
  $apicall .= "&GLOBAL-ID=$globalid";
  $apicall .= "&keywords=$safequery";
  $apicall .= "&paginationInput.entriesPerPage=5";

  // Get cURL resource
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_URL,$apicall);
  // Execute request
  $resp = curl_exec($curl);
  // Close request
  curl_close($curl);
  // Return the results
  $resp = simplexml_load_string($resp);
  echo json_encode($resp);

  exit;
?>