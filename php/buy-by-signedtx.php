<?php

// Configuration constants
const TRONSAVE_RECEIVER_ADDRESS = "TWZEhq5JuUVvGtutNgnRBATbF8BnHGyn4S";
const PRIVATE_KEY = "your_private_key";
const TRON_FULL_NODE = "https://api.trongrid.io";
const TRONSAVE_API_URL = "https://api.tronsave.io";
const RESOURCE_TYPE = "ENERGY";

const REQUEST_ADDRESS = "your_request_address";
const RECEIVER_ADDRESS = "your_receiver_address";
const BUY_AMOUNT = 32000;
const DURATION_SEC = 3600; // 1 hour

function getEstimate($resourceAmount, $durationSec) {
    $url = TRONSAVE_API_URL . "/v2/estimate-buy-resource";
    $body = [
        'resourceAmount' => $resourceAmount,
        'unitPrice' => "MEDIUM",
        'resourceType' => RESOURCE_TYPE,
        'durationSec' => $durationSec,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getSignedTransaction($estimateTrx, $requestAddress) {
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
                            'owner_address' => $requestAddress,
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

function createOrder($resourceAmount, $signedTx, $receiverAddress, $unitPrice, $durationSec) {
    $url = TRONSAVE_API_URL . "/v2/buy-resource";
    $body = [
        'resourceType' => RESOURCE_TYPE,
        'resourceAmount' => $resourceAmount,
        'unitPrice' => $unitPrice,
        'allowPartialFill' => true,
        'receiver' => $receiverAddress,
        'durationSec' => $durationSec,
        'signedTx' => $signedTx
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function buyResourceUsingPrivateKey() {
    try {
        // Step 1: Estimate the cost
        $estimateData = getEstimate(BUY_AMOUNT, DURATION_SEC);
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
            $signedTx = getSignedTransaction($estimateTrx, REQUEST_ADDRESS);
            print_r($signedTx);

            // Step 3: Create order
            $dataCreateOrder = createOrder(BUY_AMOUNT, $signedTx, RECEIVER_ADDRESS, $unitPrice, $durationSec);
            print_r($dataCreateOrder);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Run the main function
buyResourceUsingPrivateKey();