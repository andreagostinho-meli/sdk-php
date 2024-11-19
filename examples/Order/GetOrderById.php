<?php

namespace Exemples\Order\GetOrderById;

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

try {
    $orderId = "01JD2P9GGXAPBDGG6YT90N77M3";
    $request_options = new RequestOptions();
    $request_options->setCustomHeaders(["X-Sandbox: true"]);

    // Obtenha a ordem
    $order = $client->get($orderId, $request_options);
    // var_dump($order);
    //print_r($order);

    // Exibir informações da ordem
    echo "Order ID: " . $order->id . "\n";
    echo "Total Amount: " . $order->total_amount . "\n";
    echo "Type: " . $order->type . "\n";
    echo "External Reference: " . $order->external_reference . "\n";
    echo "Status: " . $order->status . "\n";
    echo "Status Detail: " . $order->status_detail . "\n";
    echo "Created Date: " . $order->created_date . "\n";
    echo "Last Updated Date: " . $order->last_updated_date . "\n";
    echo "email:" . $order->payer->email;

    // Verificando o objeto payer - não está funcionando, pois mesmo com todas as alterações e inclusão de func pra DESERIALIZAR O JSON, O RETORNO AINDA É DE Payer information is not available.
    if (isset($order->payer) && $order->payer instanceof \MercadoPago\Resources\Order\Payer) {
        echo "Payer Email: " . $order->payer->email . "\n";
    } else {
        echo "Payer information is not available.\n";
        //var_dump($order);
    }

    // Verificando as transações
    if (isset($order->transactions) && isset($order->transactions->payments) && is_array($order->transactions->payments) && count($order->transactions->payments) > 0) {
        $payment = $order->transactions->payments[0];

        echo "Payments Id: " . $payment->id . "\n";
        echo "Payment Processed: " . $payment->status . "\n";
        echo "Payment Amount: " . $payment->amount . "\n";

        if (isset($payment->payment_method)) {
            echo "Payment Method ID: " . $payment->payment_method->id . "\n";
            echo "Payment Method Type: " . $payment->payment_method->type . "\n";
            echo "Issuer ID: " . $payment->payment_method->issuer_id . "\n";
            echo "Installments: " . $payment->payment_method->installments . "\n";
        }
    } else {
        echo "No payments found for this order.\n";
    }

} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
