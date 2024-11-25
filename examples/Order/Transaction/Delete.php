<?php

namespace Examples\Order\Transaction;

// Step 1: Require the library from your Composer vendor folder
require_once '../../../vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Step 2: Set production or sandbox access token
MercadoPagoConfig::setAccessToken("APP_USR-874202490252970-100714-e890db6519b0dceb4ef24ef41ed816e4-2021490138");
// Step 2.1 (optional - default is SERVER): Set your runtime enviroment from MercadoPagoConfig::RUNTIME_ENVIROMENTS
// In case you want to test in your local machine first, set runtime enviroment to LOCAL
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

// Step 3: Initialize the API client
$client = new OrderClient();

try {
    // Step 4: Create the request array
    $request = [
      "type" => "online",
      "processing_mode" => "manual",
      "total_amount" => "200.00",
      "external_reference" => "ext_ref_1234",
      "payer" => [
          "email" => "test_212345@testuser.com"
      ],
      "transactions" => [
          "payments" => [
              [
                  "amount" => "200.00",
                  "payment_method" => [
                      "id" => "master",
                      "type" => "credit_card",
                      "token" => "7bd369f503ba958e652d0761fe8fa215",
                      "installments" => 1,
                  ]
              ]
          ]
      ],
];

    // Step 5: Create the request options, setting X-Idempotency-Key
    $request_options = new RequestOptions();
    $request_options->setCustomHeaders(["X-Idempotency-Key: 765678765", "X-Sandbox: true"]);

    /// Step 6: Create the order
    $order = $client->create($request, $request_options);
    echo "Order ID:" . $order->id . "\n";
    echo "Order" . $order->status . "\n";
    $transaction_id = $order->transactions->payments[0]->id;
    echo "Transaction ID: " . $transaction_id . "\n";

    // step 7: Delete the transaction
    $request_options->setCustomHeaders(["X-Idempotency-Key: 0987654", "X-Sandbox: true"]);
    $response = $client->deleteTransaction($order->id, $transaction_id, $request_options);
    if ($response->getStatusCode() === 204) {
        echo "Transaction deleted successfully. HTTP Status Code: " . $response->getStatusCode() . "\n";
    } else {
        // Exibir erro, caso contrário
        echo "Error: " . $response->getContent() . "\n";
        echo "HTTP Status Code: " . $response->getStatusCode() . "\n";
    }

    // Step 8: Handle exceptions
} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
