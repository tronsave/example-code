<?php

// Configuration
const API_KEY = 'your_api_key';
const TRONSAVE_API_URL = "https://api-dev.tronsave.io";
const RECEIVER_ADDRESS = 'your_receiver_address';
const BUY_AMOUNT = 32000;
const DURATION = 3600; // 1 hour
const MAX_PRICE_ACCEPTED = 100;
const RESOURCE_TYPE = "ENERGY";

function sleep_ms($ms) {
    usleep($ms * 1000);
}

function getOrderBook($apiKey, $receiverAddress) {
    $url = TRONSAVE_API_URL . "/v2/order-book?address=" . $receiverAddress;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['apikey: ' . $apiKey]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getAccountInfo($apiKey) {
    $url = TRONSAVE_API_URL . "/v2/user-info";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['apikey: ' . $apiKey]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function buyResource($apiKey, $receiverAddress, $resourceAmount, $durationSec, $maxPriceAccepted) {
    $url = TRONSAVE_API_URL . "/v2/buy-resource";
    $body = [
        'resourceType' => RESOURCE_TYPE,
        'unitPrice' => "MEDIUM",
        'resourceAmount' => $resourceAmount,
        'receiver' => $receiverAddress,
        'durationSec' => $durationSec,
        'options' => [
            'allowPartialFill' => true,
            'onlyCreateWhenFulfilled' => false,
            'maxPriceAccepted' => $maxPriceAccepted,
        ]
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . $apiKey,
            'Content-Type: application/json'
        ]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getOneOrderDetails($apiKey, $orderId) {
    $url = TRONSAVE_API_URL . "/v2/order/" . $orderId;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['apikey: ' . $apiKey]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function createOrderByUsingApiKey() {
    try {
        // Check energy available
        $orderBook = getOrderBook(API_KEY, RECEIVER_ADDRESS);
        print_r($orderBook);

        $needTrx = MAX_PRICE_ACCEPTED * BUY_AMOUNT;

        // Check if balance is enough
        $accountInfo = getAccountInfo(API_KEY);
        print_r($accountInfo);

        $isBalanceEnough = (int)$accountInfo['data']['balance'] >= $needTrx;
        echo "Is balance enough: " . ($isBalanceEnough ? "Yes" : "No") . "\n";

        if ($isBalanceEnough) {
            $buyResourceOrder = buyResource(API_KEY, RECEIVER_ADDRESS, BUY_AMOUNT, DURATION, MAX_PRICE_ACCEPTED);
            print_r($buyResourceOrder);

            if (!$buyResourceOrder['error']) {
                while (true) {
                    sleep_ms(3000);
                    $orderDetail = getOneOrderDetails(API_KEY, $buyResourceOrder['data']['orderId']);
                    print_r($orderDetail);

                    if ($orderDetail['data']['fulfilledPercent'] === 100 || $orderDetail['data']['remainAmount'] === 0) {
                        echo "Your order already fulfilled\n";
                        break;
                    } else {
                        echo "Your order is not fulfilled, wait 3s and recheck\n";
                    }
                }
            } else {
                print_r($buyResourceOrder);
                throw new Exception("Buy Order Failed");
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Run the main function
createOrderByUsingApiKey();