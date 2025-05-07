<?php

// Configuration
const API_KEY = 'your-api-key';
const TRONSAVE_API_URL = "https://api.tronsave.io";
const RECEIVER_ADDRESS = 'your-receiver-address';
const BUY_AMOUNT = 32000;
const DURATION = 3600; // 1 hour
const MAX_PRICE_ACCEPTED = 100;
const RESOURCE_TYPE = "ENERGY"; // ENERGY or BANDWIDTH

/**
 * Sleep function
 * @param int $ms Milliseconds to sleep
 * @return void
 */
function sleep_ms(int $ms): void {
    usleep($ms * 1000);
}

/**
 * Get order book
 * @return array
 */
function getOrderBook(): array {
    $url = TRONSAVE_API_URL . "/v2/order-book?address=" . RECEIVER_ADDRESS;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . API_KEY
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

/**
 * Get account info
 * @return array
 */
function getAccountInfo(): array {
    $url = TRONSAVE_API_URL . "/v2/user-info";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . API_KEY
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

/**
 * Buy resource
 * @param int $amount
 * @param int $durationSec
 * @param int $maxPriceAccepted
 * @return array
 */
function buyResource(int $amount, int $durationSec, int $maxPriceAccepted): array {
    $url = TRONSAVE_API_URL . "/v2/buy-resource";
    $body = [
        'resourceType' => RESOURCE_TYPE,
        'unitPrice' => "MEDIUM",
        'amount' => $amount,
        'receiver' => RECEIVER_ADDRESS,
        'durationSec' => $durationSec,
        'options' => [
            'allowPartialFill' => true,
            'onlyCreateWhenFulfilled' => false,
            'maxPriceAccepted' => $maxPriceAccepted,
        ]
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
 * Get order details
 * @param string $orderId
 * @return array
 */
function getOrderDetails(string $orderId): array {
    $url = TRONSAVE_API_URL . "/v2/order/" . $orderId;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . API_KEY
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

/**
 * Main function to create order
 */
function createOrderByUsingApiKey(): void {
    // Check energy available
    $orderBook = getOrderBook();
    print_r($orderBook);

    $needTrx = MAX_PRICE_ACCEPTED * BUY_AMOUNT;

    // Check if balance is enough
    $accountInfo = getAccountInfo();
    print_r($accountInfo);

    $isBalanceEnough = (int)$accountInfo['data']['balance'] >= $needTrx;
    echo "Is balance enough: " . ($isBalanceEnough ? "Yes" : "No") . "\n";

    if ($isBalanceEnough) {
        $buyResourceOrder = buyResource(BUY_AMOUNT, DURATION, MAX_PRICE_ACCEPTED);
        print_r($buyResourceOrder);

        if (!$buyResourceOrder['error']) {
            while (true) {
                sleep_ms(3000);
                $orderDetail = getOrderDetails($buyResourceOrder['data']['orderId']);
                print_r($orderDetail);

                if ($orderDetail['data']['fulfilledPercent'] === 100 || $orderDetail['data']['remainAmount'] === 0) {
                    echo "Order fulfilled successfully\n";
                    break;
                } else {
                    echo "Order not fulfilled, waiting 3s and rechecking...\n";
                }
            }
        } else {
            print_r($buyResourceOrder);
            throw new Exception("Buy Order Failed");
        }
    }
}

// Run the main function
try {
    createOrderByUsingApiKey();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 