<?php

namespace MercadoPago\Tests\Client\Integration\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use PHPUnit\Framework\TestCase;
use MercadoPago\Client\CardToken\CardTokenClient;

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
            $request_options->setCustomHeaders(["X-Sandbox: true"]);
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
            $request_options->setCustomHeaders(["X-Sandbox: true"]);
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            $order_capture = $client->capture($order->id, $request_options);
            $this->assertSame($order_capture->status, "processed");

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
        $client_token = new CardTokenClient();
        $card_token = $client_token->create($this->createCardTokenRequest());
        $this->assertNotNull($card_token->id);

        $request = [
            "type" => "online",
            "processing_mode" => "automatic",
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
                            "token" => $card_token->id,
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
            $request = $this->createRequest();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Sandbox: true"]);
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            $order_get = $client->get($order->id, $request_options);
            $this->assertNotNull($order_get->id);
            $this->assertSame($order->id, $order_get->id);
            $this->assertSame($order->total_amount, $order_get->total_amount);
            $this->assertSame($order->status, $order_get->status);
            $this->assertSame($order->status_detail, $order_get->status_detail);

        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);

        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    public function testCancelOrderSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestCapture();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Sandbox: true"]);
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            sleep(3); // sleep to avoid error 422 when create and cancel ocurrs just in time

            $order_cancelled = $client->cancel($order->id, $request_options);
            $this->assertNotNull($order_cancelled->id);
            $this->assertSame("cancelled", $order_cancelled->status);

        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);

        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    public function testProcessSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createOrderProcess();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders([ "X-Sandbox: true"]);
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            $order_processed = $client->process($order->id, $request_options);
            $this->assertSame("processed", $order_processed->status);

        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);

        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createOrderProcess(): array
    {
        $client_token = new CardTokenClient();
        $card_token = $client_token->create($this->createCardTokenRequest());
        $this->assertNotNull($card_token->id);

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
                            "token" => $card_token->id,
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

    private function createCardTokenRequest(): array
    {
        $request = [
            "card_number" => "5031433215406351",
            "expiration_year" => "2025",
            "expiration_month" => "12",
            "security_code" => "123",
            "cardholder" => [
                "name" => "APRO",
                "identification" => [
                    "type" => "CPF",
                    "number" => "19119119100",
                ],
            ]
        ];
        return $request;
    }
}
