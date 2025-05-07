<?php

// Configuration
const API_KEY = 'your-api-key';
const TRONSAVE_API_URL = "https://api.tronsave.io";
const RECEIVER = "your-receiver-address";
const RESOURCE_TYPE = "ENERGY";

/**
 * Get estimate extend data
 * @param int $extendTo
 * @param int $maxPriceAccepted
 * @return array
 */
function getEstimateExtendData(int $extendTo, int $maxPriceAccepted): array {
    $url = TRONSAVE_API_URL . "/v2/get-extendable-delegates";
    $body = [
        'extendTo' => $extendTo,
        'receiver' => RECEIVER,
        'maxPriceAccepted' => $maxPriceAccepted,
        'resourceType' => RESOURCE_TYPE,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . API_KEY,
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

/**
 * Send extend request
 * @param int $extendTo
 * @param int $maxPriceAccepted
 * @return array
 */
function sendExtendRequest(int $extendTo, int $maxPriceAccepted): array {
    $url = TRONSAVE_API_URL . "/v2/extend-request";
    $estimateResponse = getEstimateExtendData($extendTo, $maxPriceAccepted);
    $extendData = $estimateResponse['data']['extendData'] ?? [];

    if (!empty($extendData)) {
        $body = [
            'extendData' => $extendData,
            'receiver' => RECEIVER,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . API_KEY,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    return [];
}

// Example run code
try {
    $extendTo = time() + (3 * 86400); // Extend to 3 next days
    $maxPriceAccepted = 900;
    $response = sendExtendRequest($extendTo, $maxPriceAccepted);
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 