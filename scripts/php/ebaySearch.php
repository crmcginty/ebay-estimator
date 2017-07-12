<?php

  $config = parse_ini_file("../../../config/config_comics.ini");

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
  $apicall .= "&GLOBAL-ID=$globalid";
  $apicall .= "&keywords=$safequery";
  $apicall .= "&paginationInput.entriesPerPage=5";

  // Load the call and capture the document returned by eBay API
  $resp = simplexml_load_file($apicall);

  echo json_encode($resp); // Return the results as JSON
  exit;
?>