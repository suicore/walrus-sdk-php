<?php

namespace Suicore\Walrus\Tests;

use PHPUnit\Framework\TestCase;
use Suicore\Walrus\Types\QuiltElementFile;
use Suicore\Walrus\Types\QuiltElementFileMetadata;
use Suicore\Walrus\Types\StoreBlobOrQuiltOptions;
use Suicore\Walrus\WalrusClient;

final class WalrusClientQuiltsE2ETest extends TestCase
{
    private string $publisherUrl = 'https://publisher.walrus-testnet.walrus.space';
    private string $aggregatorUrl = 'https://aggregator.walrus-testnet.walrus.space';

    protected function setUp(): void
    {
        if (!getenv('RUN_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Integration tests are disabled. Set RUN_INTEGRATION_TESTS=1 to run them.');
        }
    }

    public function testStoreQuiltReturnsNewlyCreated(): void
    {
        $client = new WalrusClient($this->publisherUrl, $this->aggregatorUrl);
        $options = new StoreBlobOrQuiltOptions(2, '', false);
        $files = [
            new QuiltElementFile('wal1.jpg', __DIR__ . '/walrus.jpg'),
            new QuiltElementFile('wal2.jpg', fopen(__DIR__ . '/walrus.jpg', 'rb')), // resource
        ];
        $metadata = [
            new QuiltElementFileMetadata('wal1.jpg', ['creator' => 'walrus', 'version' => '1.0']),
            new QuiltElementFileMetadata('wal2.jpg', ['type' => 'logo', 'format' => 'png']),
        ];

        $storeResponse = $client->storeQuilt($files, $options, $metadata);

        // Check that we got a newly created response.
        $this->assertTrue($storeResponse->isAlreadyCertified(), 'Expected response to be alreadyCertified.');
        $blobId = $storeResponse->getAlreadyCertified()->getBlobId();
        $this->assertNotEmpty($blobId, 'Expected non-empty blobId.');
        $endEpoch = $storeResponse->getAlreadyCertified()->getEndEpoch();
        $this->assertNotEmpty($endEpoch, 'Expected end epoch to be set.');
        $elements = $storeResponse->getStoredQuiltBlobs()->getQuiltFiles();
        $this->assertCount(2, $elements, 'Expected 2 quilts to be stored.');
        $patchIds = array_map(fn($q) => $q->getQuiltPatchId(), $elements);

        sleep(2);
        foreach ($patchIds as $patchId) {
            $retrievedContent = $client->getQuilt($patchId);
            $this->assertNotEmpty($retrievedContent, 'Retrieved content for patch should not be empty.');
            $retrievedContentWithName = $client->getQuilt($blobId, 'wal1.jpg');
            $this->assertNotEmpty($retrievedContentWithName, 'Retrieved content with name should not be empty.');
        }
    }
}
