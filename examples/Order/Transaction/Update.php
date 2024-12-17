<?php

namespace Examples\Order\Transaction;

// Step 1: Require the library from your Composer vendor folder
require_once '../../../vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Client\Order\OrderTransactionClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Step 2: Set production or sandbox access token
MercadoPagoConfig::setAccessToken("APP_USR-874202490252970-100714-e890db6519b0dceb4ef24ef41ed816e4-2021490138");
// Step 2.1 (optional - default is SERVER): Set your runtime enviroment from MercadoPagoConfig::RUNTIME_ENVIROMENTS
// In case you want to test in your local machine first, set runtime enviroment to LOCAL
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

// Step 3: Initialize the Order client
$order_client = new OrderClient();

// Step 4: Initialize the Order Transaction client
$order_transaction_client = new OrderTransactionClient();

try {
    // Step 5: Create the request to create an Order
    $create_order_request = [
        "type" => "online",
        "processing_mode" => "manual",
        "total_amount" => "100.00",
        "external_reference" => "ext_ref_1234",
        "transactions" => [
            "payments" => [
                [
                    "amount" => "100.00",
                    "payment_method" => [
                        "id" => "master",
                        "type" => "credit_card",
                        "token" => "e95f195e5d68ddaee9e5374281047028",
                        "installments" => 1,
                        "statement_descriptor" => "Store",
                    ]
                ]
            ]
        ],
        "payer" => [
            "email" => "test_1734439736@testuser.com",
        ]
    ];

    // Step 6: Create the request options, setting X-Idempotency-Key
    $create_order_request_options = new RequestOptions();
    $create_order_request_options->setCustomHeaders(["X-Idempotency-Key: 911876655"]);

    // Step 7: Create the Order
    $order = $order_client->create($create_order_request, $create_order_request_options);
    echo "===== BEFORE UPDATE =====";
    echo "\nPayment ID: " . $order->transactions->payments[0]->id;
    echo "\nAmount: " . $order->transactions->payments[0]->amount;

    // Step 8: Create the request to update a transaction
    $update_transaction_request = [
        "payment_method" => [
          "type" => "credit_card",
           "installments" => 3,
           "statement_descriptor" => "Store",
        ]
    ];

    // Step 9: Create the request options, setting X-Idempotency-Key
    $update_transaction_request_options = new RequestOptions();
    $update_transaction_request_options->setCustomHeaders(["X-Idempotency-Key: 0282387342"]);

    // Step 10: Update the transaction
    sleep(3);
    $transaction = $order_transaction_client->update($order->id, $order->transactions->payments[0]->id, $update_transaction_request, $update_transaction_request_options);

    echo "\n===== AFTER UPDATE =====";
    echo "\nTransaction Updated: \n";
    print_r($transaction);

    // Step 11: Handle exceptions
} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
