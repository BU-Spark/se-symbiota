<?php

$response = shell_exec("curl http://ocr-server:8050/output/2");

// Decode the JSON response
$decodedResponse = json_decode($response, true);

// Define default variables
$ocr_catalogNumber = 0;
$ocr_collectionCode = 0;
$ocr_country = '';
$ocr_donatedBy = '';
$ocr_eventDate = '';
$ocr_institutionCode = 0;
$ocr_occurrenceID = 0;
$ocr_recordedBy = '';
$ocr_scientificName = '';
$ocr_yearDonated = 0;

// Update variables if the JSON response is valid
if ($decodedResponse !== null && is_array($decodedResponse)) {
    $catalogNumber = $decodedResponse['catalogNumber'] ?? $catalogNumber;
    $collectionCode = $decodedResponse['collectionCode'] ?? $collectionCode;
    $country = $decodedResponse['country'] ?? $country;
    $donatedBy = $decodedResponse['donatedBy'] ?? $donatedBy;
    $eventDate = $decodedResponse['eventDate'] ?? $eventDate;
    $institutionCode = $decodedResponse['institutionCode'] ?? $institutionCode;
    $occurrenceID = $decodedResponse['occurrenceID'] ?? $occurrenceID;
    $recordedBy = $decodedResponse['recordedBy'] ?? $recordedBy;
    $scientificName = $decodedResponse['scientificName'] ?? $scientificName;
    $yearDonated = $decodedResponse['yearDonated'] ?? $yearDonated;
}

// Return the raw JSON response directly
header('Content-Type: application/json');
echo $response;
