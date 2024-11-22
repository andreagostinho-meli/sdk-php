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

            $order = $client->create($request);

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
                            "token" => "<unique_credit_card_token>",
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
            $this->assertSame("test_1731354550@testuser.com", $order->payer->email);
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
            // At each test run, it is important to use an order_id of an order that can be cancelled.
            $orderId = "01JD82K0XV472DWVBF73H8NT6N";
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Sandbox: true"]);

            $order = $client->cancel($orderId, $request_options);

            $this->assertNotNull($order->id);
            $this->assertSame($orderId, $order->id);
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

    public function testProcessSuccess(): void
    {
        try {
            $client = new OrderClient();
            $orderId = "01HRYFWNYRE1MR1E60MW3X0T2P";
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Sandbox: true"]);

            $order = $client->process($orderId, $request_options);

            $this->assertNotNull($order->id);
            $this->assertSame($orderId, $order->id);
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
}
