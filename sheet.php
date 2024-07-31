<?php

require 'vendor/autoload.php';

$serviceAccountPath = __DIR__ . '/credentials.json'; // to get the credentials, you need to create service account on google console cloud. and make sure to enable the google shhet api

// Verify the file exists
if (!file_exists($serviceAccountPath)) {
    die('Service account file not found at: ' . $serviceAccountPath);
}

// Set the environment variable for Google Application Credentials
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $serviceAccountPath);

// Initialize the Google Client
$client = new Google_Client();

try {
    $client->useApplicationDefaultCredentials();
} catch (Exception $e) {
    die('Failed to use application default credentials: ' . $e->getMessage());
}

$client->addScope(Google_Service_Sheets::SPREADSHEETS);

$service = new Google_Service_Sheets($client);

$spreadsheetId = ""; //spreadsheet id here
$range = '';  //google sheet title


$url = ""; //get url
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);

$response = curl_exec($ch);

if ($response === false) {
    die('Curl error: ' . curl_error($ch));
}

curl_close($ch);

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('JSON decode error: ' . json_last_error_msg());
}

if (!is_array($data)) {
    die('Expected data to be an array');
}

$original_timestamp = $data['data']['transaction_config']['fx_rate_last_updated'];
$date = new DateTime();
$timestamp = $date->format('F d, Y g:ia');

$processedData = [
    [
        "JW_Search_USA_Country_India_Phrase",
        '',
        '',
        $data['data']['source_currency'],
        $data['data']['net_source_amount'],
        $data['data']['destination_currency'],
        $data['data']['instarem_fx_rate'],
        $timestamp
    ]
];

$body = new Google_Service_Sheets_ValueRange([
    'values' => $processedData
]);


$params = [
    'valueInputOption' => 'RAW'
];

$response = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

if ($response) {
    echo "SUCCESS!!";
} else {
    echo "ERROR....";
}


?>
