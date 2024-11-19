<?php

namespace MercadoPago\Tests\Client\Integration\Order;

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
        MercadoPagoConfig::setAccessToken(getenv("APP_USR-874202490252970-100714-e890db6519b0dceb4ef24ef41ed816e4-2021490138"));
    }

    public function testCreateSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequest();

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


    public function testGetOrderSuccess(): void
    {
        try {
            $client = new OrderClient();
            $orderId = "01JD2P9GGXAPBDGG6YT90N77M3";
            $order = $client->get($orderId);

            // Verificações das respostas
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
}
