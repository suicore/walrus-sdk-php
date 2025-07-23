<?php

namespace Suicore\Walrus\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Suicore\Walrus\Types\StoreBlobOrQuiltOptions;
use Suicore\Walrus\WalrusClient;

final class WalrusClientTest extends TestCase
{
    public function testStoreBlobReturnsNewlyCreated()
    {
        // Simulate a publisher response with a "newlyCreated" blob.
        $mockResponseBody = json_encode([
            "newlyCreated" => [
                "blobObject" => [
                    "id" => "testid",
                    "registeredEpoch" => 0,
                    "blobId" => "testblobid",
                    "size" => 17,
                    "encodingType" => "RedStuff",
                    "certifiedEpoch" => 0,
                    "storage" => [
                        "id" => "storagesid",
                        "startEpoch" => 0,
                        "endEpoch" => 1,
                        "storageSize" => 4747680,
                    ],
                    "deletable" => false,
                ],
                "resourceOperation" => [
                    "registerFromScratch" => [
                        "encodedLength" => 4747680,
                        "epochsAhead" => 1,
                    ]
                ],
                "cost" => 231850,
            ]
        ]);

        // Set up the publisher client with a mock handler.
        $publisherMock = new MockHandler([
            new Response(200, [], $mockResponseBody)
        ]);
        $publisherHandler = HandlerStack::create($publisherMock);
        $publisherClient = new Client([
            'handler' => $publisherHandler,
            'base_uri' => 'https://publisher.walrus-testnet.walrus.space'
        ]);

        // Create a dummy aggregator client.
        $dummyMock = new MockHandler([
            new Response(200, [], "dummy")
        ]);
        $dummyHandler = HandlerStack::create($dummyMock);
        $aggregatorClient = new Client([
            'handler' => $dummyHandler,
            'base_uri' => 'https://aggregator.walrus-testnet.walrus.space'
        ]);

        $client = new WalrusClient(
            publisherUrl: 'https://publisher.walrus-testnet.walrus.space',
            aggregatorUrl: 'https://aggregator.walrus-testnet.walrus.space',
            publisherClient: $publisherClient,
            aggregatorClient: $aggregatorClient
        );

        $StoreBlobOrQuiltOptions = new StoreBlobOrQuiltOptions(2, '', false);
        $result = $client->storeBlob("some string", $StoreBlobOrQuiltOptions);

        // Use the strongly-typed methods to check the response.
        $this->assertTrue($result->isNewlyCreated(), 'Expected response to be newly created.');
        $this->assertEquals('testblobid', $result->getNewlyCreated()->getBlobObject()->getBlobId());
    }

    public function testGetBlobReturnsContent()
    {
        $expectedContent = "this is a blob content";

        // Set up the aggregator client with a mock handler.
        $aggregatorMock = new MockHandler([
            new Response(200, [], $expectedContent)
        ]);
        $aggregatorHandler = HandlerStack::create($aggregatorMock);
        $aggregatorClient = new Client([
            'handler' => $aggregatorHandler,
            'base_uri' => 'https://aggregator.walrus-testnet.walrus.space'
        ]);

        // Create a dummy publisher client.
        $dummyMock = new MockHandler([
            new Response(200, [], "dummy")
        ]);
        $dummyHandler = HandlerStack::create($dummyMock);
        $publisherClient = new Client([
            'handler' => $dummyHandler,
            'base_uri' => 'https://publisher.walrus-testnet.walrus.space'
        ]);

        $client = new WalrusClient(
            publisherClient: $publisherClient,
            aggregatorClient: $aggregatorClient
        );

        $content = $client->getBlob('testblobid');
        $this->assertEquals($expectedContent, $content);
    }
}