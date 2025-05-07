<?php

// Configuration
const TRONSAVE_API_URL = "https://api.tronsave.io";
const RECEIVER = "your-receiver-address";
const RESOURCE_TYPE = "ENERGY";
const REQUESTER_ADDRESS = "TFwUFWr3QV376677Z8VWXxGUAMFSrq1MbM";
const PRIVATE_KEY = "your-private-key";
const TRON_FULL_NODE = "https://api.trongrid.io";
const TRONSAVE_RECEIVER_ADDRESS = "TWZEhq5JuUVvGtutNgnRBATbF8BnHGyn4S";

/**
 * Get estimate extend data
 * @param string $requester
 * @param int $extendTo
 * @param int $maxPriceAccepted
 * @return array
 */
function getEstimateExtendData(string $requester, int $extendTo, int $maxPriceAccepted): array {
    $url = TRONSAVE_API_URL . "/v2/get-extendable-delegates";
    $body = [
        'extendTo' => $extendTo,
        'receiver' => RECEIVER,
        'requester' => $requester,
        'maxPriceAccepted' => $maxPriceAccepted,
        'resourceType' => RESOURCE_TYPE,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

/**
 * Get signed transaction
 * @param int $totalEstimateTrx
 * @return array
 */
function getSignedTx(int $totalEstimateTrx): array {
    // This is a placeholder. In real implementation, you would use TronWeb PHP SDK
    return [
        'visible' => false,
        'txID' => '...',
        'raw_data' => [
            'contract' => [
                [
                    'parameter' => [
                        'value' => [
                            'amount' => $totalEstimateTrx,
                            'owner_address' => REQUESTER_ADDRESS,
                            'to_address' => TRONSAVE_RECEIVER_ADDRESS
                        ],
                        'type_url' => 'type.googleapis.com/protocol.TransferContract'
                    ],
                    'type' => 'TransferContract'
                ]
            ]
        ]
    ];
}

/**
 * Send extend request
 * @param int $extendTo
 * @param int $maxPriceAccepted
 * @return array
 */
function sendExtendRequest(int $extendTo, int $maxPriceAccepted): array {
    $url = TRONSAVE_API_URL . "/v2/extend-request";
    $estimateResponse = getEstimateExtendData(REQUESTER_ADDRESS, $extendTo, $maxPriceAccepted);
    $extendData = $estimateResponse['data']['extendData'] ?? [];

    if (!empty($extendData)) {
        $totalEstimateTrx = $estimateResponse['data']['totalEstimateTrx'];
        $signedTx = getSignedTx($totalEstimateTrx);

        $body = [
            'extendData' => $extendData,
            'receiver' => RECEIVER,
            'signedTx' => $signedTx
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
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