<?php

namespace Examples\Order\Transaction;

// Step 1: Require the library from your Composer vendor folder
require_once '../../vendor/autoload.php';

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
$order_id = "01JDMTQSK1M4FAKFXHX95AY0JS";
$transaction_id = "pay_01JDMTQSK1M4FAKFXHXC5CCV2M";

try {
    $request_options = new RequestOptions();
    $request_options->setCustomHeaders([
      "X-Idempotency-Key: 123",
      "X-Sandbox: true"
    ]);

    $response = $client->deleteTransaction($order_id, $transaction_id, $request_options);
    if ($response === null || $response->getStatusCode() === 204) {
        echo "Transaction deleted successfully. HTTP Status Code: 204\n";
    } else {
        echo "Transaction deletion failed with status: " . $response->getStatusCode() . "\n";
        echo "Response: " . var_dump($response->getContent()) . "\n";
    }
} catch (MPApiException $e) {
    echo "Error: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
} catch (\Exception $e) {
    echo $e->getMessage();
}
