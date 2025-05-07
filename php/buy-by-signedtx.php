<?php

// Configuration
const TRONSAVE_RECEIVER_ADDRESS = "TWZEhq5JuUVvGtutNgnRBATbF8BnHGyn4S";
const PRIVATE_KEY = "your_private_key";
const TRON_FULL_NODE = "https://api.trongrid.io";
const TRONSAVE_API_URL = "https://api.tronsave.io";
const RESOURCE_TYPE = "ENERGY";

const REQUEST_ADDRESS = "your_request_address";
const RECEIVER_ADDRESS = "your_receiver_address";
const BUY_AMOUNT = 32000;
const DURATION_SEC = 3 * 86400; // 3 days

/**
 * Get estimate for buying resource
 * @return array
 */
function getEstimate(): array {
    $url = TRONSAVE_API_URL . "/v2/estimate-buy-resource";
    $body = [
        'amount' => BUY_AMOUNT,
        'unitPrice' => "MEDIUM",
        'resourceType' => RESOURCE_TYPE,
        'durationSec' => DURATION_SEC,
        'requester' => REQUEST_ADDRESS,
        'receiver' => RECEIVER_ADDRESS,
        'options' => [
            'allowPartialFill' => true,
        ]
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
 * @param int $estimateTrx
 * @return array
 */
function getSignedTransaction(int $estimateTrx): array {
    // This is a placeholder. In real implementation, you would use TronWeb PHP SDK
    return [
        'visible' => false,
        'txID' => '...',
        'raw_data' => [
            'contract' => [
                [
                    'parameter' => [
                        'value' => [
                            'amount' => $estimateTrx,
                            'owner_address' => REQUEST_ADDRESS,
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
 * Create order
 * @param array $signedTx
 * @param string $receiverAddress
 * @param int $unitPrice
 * @param int $durationSec
 * @param array $options
 * @return array
 */
function createOrder(array $signedTx, string $receiverAddress, int $unitPrice, int $durationSec, array $options): array {
    $url = TRONSAVE_API_URL . "/v2/buy-resource";
    $body = [
        'resourceType' => RESOURCE_TYPE,
        'unitPrice' => $unitPrice,
        'allowPartialFill' => true,
        'receiver' => $receiverAddress,
        'durationSec' => $durationSec,
        'signedTx' => $signedTx,
        'options' => $options
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
 * Main function to buy resource using private key
 */
function buyResourceUsingPrivateKey(): void {
    // Step 1: Estimate the cost
    $estimateData = getEstimate();
    if ($estimateData['error']) {
        throw new Exception($estimateData['message']);
    }
    print_r($estimateData);

    $unitPrice = $estimateData['data']['unitPrice'];
    $estimateTrx = $estimateData['data']['estimateTrx'];
    $durationSec = $estimateData['data']['durationSec'];
    $availableResource = $estimateData['data']['availableResource'];

    $isReadyFulfilled = $availableResource >= BUY_AMOUNT;
    if ($isReadyFulfilled) {
        // Step 2: Build signed transaction
        $signedTx = getSignedTransaction($estimateTrx);
        print_r($signedTx);

        // Step 3: Create order
        $options = [
            'allowPartialFill' => true,
            'maxPriceAccepted' => 100,
        ];
        $dataCreateOrder = createOrder($signedTx, RECEIVER_ADDRESS, $unitPrice, $durationSec, $options);
        print_r($dataCreateOrder);
    }
}

// Run the main function
try {
    buyResourceUsingPrivateKey();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 