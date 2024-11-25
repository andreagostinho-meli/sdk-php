<?php

namespace MercadoPago\Tests\Client\Integration\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use PHPUnit\Framework\TestCase;

/**
 * OrderClient integration tests.
 */
final class OrderClientITTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        MercadoPagoConfig::setAccessToken(getenv("ACCESS_TOKEN"));
    }

    public function testCreateSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequest();
            $request_options = new RequestOptions();

            $idempotencyKey = bin2hex(random_bytes(16));
            echo "Idempotency Key for CREATE: " . $idempotencyKey . PHP_EOL;

            $request_options->setCustomHeaders([
                "X-Sandbox: true",
                "X-Idempotency-Key: " . $idempotencyKey,
                "X-Caller-SiteID: MLB"]);

            $order = $client->create($request, $request_options);

            $this->assertNotNull($order->id);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createRequest(): array
    {
        $request = [
            "type" => "online",
            "total_amount" => "1000.00",
            "external_reference" => "ext_ref_1234",
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "1000.00",
                        "payment_method" => [
                            "id" => "pix",
                            "type" => "bank_transfer"
                        ],
                    ],
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
        return $request;
    }

    public function testCaptureSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestCapture();
            $request_options = new RequestOptions();

            $request_options->setCustomHeaders([ "X-Sandbox: true"]);

            $order = $client->create($request, $request_options);

            $order = $client->capture($order->id, $request_options);

            $this->assertSame($order->status, "processed");
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createRequestCapture(): array
    {
        $publicKey = 'APP_USR-1a65da8c-993a-4cff-9244-1f841e7c2ea9';
        $tokenResponse = $this->createCardToken($publicKey);
        $token = $tokenResponse['id'];

        $request = [
            "type" => "online",
            "total_amount" => "200.00",
            "external_reference" => "ext_ref_1234",
            "type_config" => [
                "capture_mode" => "manual"
            ],
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "200.00",
                        "payment_method" => [
                            "id" => "master",
                            "type" => "credit_card",
                            "token" => $token,
                            "installments" => 1,
                        ]
                    ]
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
        return $request;
    }

    public function testGetOrderSuccess(): void
    {
        try {
            $client = new OrderClient();
            $orderId = "01JD2P9GGXAPBDGG6YT90N77M3";
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Sandbox: true"]);
            $order = $client->get($orderId, $request_options);

            $this->assertNotNull($order->id);
            $this->assertSame("01JD2P9GGXAPBDGG6YT90N77M3", $order->id);
            $this->assertSame("online", $order->type);
            $this->assertSame("200.00", $order->total_amount);
            $this->assertSame("ext_ref_1234", $order->external_reference);
            $this->assertSame("processed", $order->status);
            $this->assertSame("accredited", $order->status_detail);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createRequestCancel(): array
    {
        $publicKey = 'APP_USR-1a65da8c-993a-4cff-9244-1f841e7c2ea9';
        $tokenResponse = $this->createCardToken($publicKey);
        $token = $tokenResponse['id'];

        $request = [
            "type" => "online",
            "processing_mode" => "manual",
            "total_amount" => "200.00",
            "external_reference" => "ext_ref_1234",
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "200.00",
                        "payment_method" => [
                            "id" => "master",
                            "type" => "credit_card",
                            "token" => $token,
                            "installments" => 1,
                        ]
                    ]
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
        return $request;
    }

    public function testCancelOrderSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestCancel();
            $request_options = new RequestOptions();

            $request_options->setCustomHeaders([ "X-Sandbox: true"]);

            $order = $client->create($request, $request_options);

            //reset idempotencyKey
            $idempotencyKey = bin2hex(random_bytes(16));
            echo "Idempotency Key for CANCEL after creation: " . $idempotencyKey . PHP_EOL;

            $request_options->setCustomHeaders([
                "X-Sandbox: true",
                "X-Idempotency-Key: " . $idempotencyKey]);

            $order = $client->cancel($order->id, $request_options);

            $this->assertNotNull($order->id);
            $this->assertSame("cancelled", $order->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createRequestProcess(): array
    {
        $publicKey = 'APP_USR-1a65da8c-993a-4cff-9244-1f841e7c2ea9';
        $tokenResponse = $this->createCardToken($publicKey);
        $token = $tokenResponse['id'];

        $request = [
            "type" => "online",
            "processing_mode" => "manual",
            "total_amount" => "200.00",
            "external_reference" => "ext_ref_1234",
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "200.00",
                        "payment_method" => [
                            "id" => "master",
                            "type" => "credit_card",
                            "token" => $token,
                            "installments" => 1,
                        ]
                    ]
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
        return $request;
    }

    public function testProcessSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestProcess();
            $request_options = new RequestOptions();

            $request_options->setCustomHeaders([
                "X-Sandbox: true",
                "X-Caller-SiteID: MLB"]);

            $order = $client->create($request, $request_options);

            $order = $client->process($order->id, $request_options);

            $this->assertSame("processed", $order->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    public function createCardToken($publicKey)
    {
        $url = 'https://api.mercadopago.com/v1/card_tokens?public_key=' . $publicKey;

        $cardData = [
            "expiration_year" => 2025,
            "site_id" => "MLB",
            "expiration_month" => 11,
            "cardholder" => [
                "identification" => [
                    "type" => "CPF",
                    "number" => "15635614680"
                ],
                "name" => "APRO"
            ],
            "security_code" => "123",
            "card_number" => "5031433215406351"
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cardData));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function testCardTokenCreation(): void
    {
        $publicKey = 'APP_USR-1a65da8c-993a-4cff-9244-1f841e7c2ea9';
        $tokenResponse = $this->createCardToken($publicKey);
    }
}
