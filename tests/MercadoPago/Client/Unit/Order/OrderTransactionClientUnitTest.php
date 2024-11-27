<?php

namespace MercadoPago\Tests\Client\Unit\Order;

use MercadoPago\Client\Order\OrderTransactionClient;
use MercadoPago\Client\Order\Transaction\CreateTransactionRequest;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPDefaultHttpClient;
use MercadoPago\Net\MPHttpClient;
use MercadoPago\Net\MPResponse;
use MercadoPago\Tests\Client\Unit\Base\BaseClient;

/**
 * OrderTransactionClient unit tests.
 */
final class OrderTransactionClientUnitTest extends BaseClient
{
    private $httpClientMock;
    private $client;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(MPHttpClient::class);
        $this->client = new OrderTransactionClient($this->httpClientMock);
    }

    public function testCreateSuccess(): void
    {
        $filepath = '../../../../Resources/Mocks/Response/Order/transaction.json';
        $mock_http_request = $this->mockHttpRequest($filepath, 201);
        $http_client = new MPDefaultHttpClient($mock_http_request);
        MercadoPagoConfig::setHttpClient($http_client);
        $client = new OrderTransactionClient();
        $request = $this->createRequest();

        $transaction = $client->create("01JD26HQ96FFHBD2CHDTXZ9MSH", $request);

        $this->assertSame(201, $transaction->getResponse()->getStatusCode());
        $this->assertSame("pay_01JD26HQ96FFHBD2CHDW984TZM", $transaction->payments[0]->id);
        $this->assertSame("100.00", $transaction->payments[0]->amount);
        $this->assertSame("master", $transaction->payments[0]->payment_method->id);
        $this->assertSame("credit_card", $transaction->payments[0]->payment_method->type);
        $this->assertSame(3, $transaction->payments[0]->payment_method->installments);
    }

    private function createRequest(): CreateTransactionRequest
    {
        $request = new CreateTransactionRequest();
        $request->payments = [
            [
                "amount" => "100.00",
                "payment_method" => [
                    "id" => "master",
                    "type" => "credit_card",
                    "token" => "{{card_token}}",
                    "installments" => 3,
                ],
            ],
        ];
        return $request;
    }

    public function testUpdateSuccess(): void
    {
        $filepath = '../../../../Resources/Mocks/Response/Order/updated_transaction.json';
        $mock_http_request = $this->mockHttpRequest($filepath, 200);
        $http_client = new MPDefaultHttpClient($mock_http_request);
        MercadoPagoConfig::setHttpClient($http_client);
        $client = new OrderTransactionClient();
        $order_id = "01JD26HQ96FFHBD2CHDTXZ9MSH";
        $transaction_id = "pay_01JD26HQ96FFHBD2CHDW984TZM";
        $request = [
            "amount" => "299.90",
        ];

        $transaction = $client->update($order_id, $transaction_id, $request);

        $this->assertSame(200, $transaction->getResponse()->getStatusCode());
        $this->assertSame("pay_01JD26HQ96FFHBD2CHDW984TZM", $transaction->id);
        $this->assertSame("299.90", $transaction->amount);
        $this->assertSame("master", $transaction->payment_method->id);
    }

    public function testDeleteSucess(): void
    {
        $order_id = "1234321";
        $transaction_id = "pay_3456789";
        $expectedResponse = new MPResponse(204, []);

        $this->httpClientMock->method('send')->willReturn($expectedResponse);
        $response = $this->client->deleteTransaction($order_id, $transaction_id);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testDeleteErrorNotFound()
    {
        $order_id = "1234321";
        $transaction_id = "pay_3456789";
        $expectedResponse = new MPResponse(404, ['Order not found.']);

        $this->httpClientMock->method('send')->willReturn($expectedResponse);
        $response = $this->client->deleteTransaction($order_id, $transaction_id);

        $this->assertEquals(404, $response->getStatusCode());
        print_r($response);
    }
}
