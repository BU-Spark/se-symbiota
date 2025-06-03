<?php

header('Content-Type: application/json');

$imageUrl = isset($_POST['imgurl']) ? $_POST['imgurl'] : '';
if (empty($imageUrl)) {
    echo json_encode(["error" => "Missing image URL"]);
    exit;
}

$url = 'http://ocr_middleware:8000/evaluate/azure?url=' . urlencode($imageUrl);

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Accept: application/json\r\n",
        'content' => '', // empty body
        'ignore_errors' => true,
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode(["error" => "Failed to fetch API response"]);
    exit;
}

$decoded = json_decode($response, true);

if ($decoded === null) {
    echo json_encode(["error" => "Invalid JSON response from OCR service"]);
    exit;
}

echo json_encode($decoded);

?>

