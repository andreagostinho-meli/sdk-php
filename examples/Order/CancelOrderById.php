<?php

namespace Exemples\Order\CancelOrderById;

// Step 1: Require the library from your Composer vendor folder
require_once '../../vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Step 2: Set production or sandbox access token
MercadoPagoConfig::setAccessToken("<ACCESS_TOKEN>");
// Step 2.1 (optional - default is SERVER): Set your runtime enviroment from MercadoPagoConfig::RUNTIME_ENVIROMENTS
// In case you want to test in your local machine first, set runtime enviroment to LOCAL
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

// Step 3: Initialize the API client
$client = new OrderClient();

$orderClient = new OrderClient();
$orderId = "ID_DA_ORDEM_A_CANCELAR";
try {
    $request_options = new RequestOptions();
    $request_options->setCustomHeaders(["X-Sandbox: true"]);

    $cancellationResponse = $orderClient->cancel($orderId, $request_options);
    print_r($cancellationResponse);
} catch (\Exception $e) {
    // Trate qualquer erro aqui
    echo 'Erro ao cancelar a ordem: ' . $e->getMessage();
}
