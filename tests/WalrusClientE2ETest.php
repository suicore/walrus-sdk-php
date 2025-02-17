<?php

namespace Suicore\Walrus\Tests;

use PHPUnit\Framework\TestCase;
use Suicore\Walrus\Responses\StoreBlobOptions;
use Suicore\Walrus\WalrusClient;

final class WalrusClientE2ETest extends TestCase
{
    private string $publisherUrl = 'https://publisher.walrus-testnet.walrus.space';
    private string $aggregatorUrl = 'https://aggregator.walrus-testnet.walrus.space';

    protected function setUp(): void
    {
        if (!getenv('RUN_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Integration tests are disabled. Set RUN_INTEGRATION_TESTS=1 to run them.');
        }
    }

    public function testStoreBlobReturnsNewlyCreated(): void
    {
        $client = new WalrusClient($this->publisherUrl, $this->aggregatorUrl);
        $data = "end-to-end test blob " . uniqid();
        $options = new StoreBlobOptions(2, '', false);
        $storeResponse = $client->storeBlob($data, $options, false);

        // Check that we got a newly created response.
        $this->assertTrue($storeResponse->isNewlyCreated(), 'Expected response to be newlyCreated.');
        $blobId = $storeResponse->getNewlyCreated()->getBlobObject()->getBlobId();
        $this->assertNotEmpty($blobId, 'Expected non-empty blobId.');
        $startEpoch = $storeResponse->getNewlyCreated()->getBlobObject()->getStorage()->getStartEpoch();
        $endEpoch = $storeResponse->getNewlyCreated()->getBlobObject()->getStorage()->getEndEpoch();
        $this->assertEquals(2, $endEpoch - $startEpoch, 'Expected epochs to be 2.');

        sleep(2);

        $retrievedContent = $client->getBlob($blobId);
        $this->assertStringContainsString($data, $retrievedContent, 'Retrieved content does not match stored data.');
    }

    public function testStoreBlobWithFileUpload(): void
    {
        $client = new WalrusClient($this->publisherUrl, $this->aggregatorUrl);
        $filePath = __DIR__ . '/walrus.jpg';
        if (!file_exists($filePath)) {
            $this->markTestSkipped("Test file not found: {$filePath}");
        }
        $options = new StoreBlobOptions(2, '', false);
        $storeResponse = $client->storeBlob($filePath, $options, true);

        $this->assertTrue($storeResponse->isAlreadyCertified(), 'Expected response to be alreadyCertified.');
        $blobId = $storeResponse->getAlreadyCertified()->getBlobId();
        $this->assertNotEmpty($blobId, 'Expected non-empty blobId.');

        sleep(2);
        $retrievedContent = $client->getBlob($blobId);
        $expectedContent = file_get_contents($filePath);
        $this->assertEquals($expectedContent, $retrievedContent, 'File content does not match retrieved blob.');
    }

    public function testStoreBlobWithMultipleEpochs(): void
    {
        $client = new WalrusClient($this->publisherUrl, $this->aggregatorUrl);
        $data = "Test data with multiple epochs: " . uniqid();
        $options = new StoreBlobOptions(5, '', false);
        $storeResponse = $client->storeBlob($data, $options, false);

        $this->assertTrue($storeResponse->isNewlyCreated(), 'Expected response to be newlyCreated.');
        $blobId = $storeResponse->getNewlyCreated()->getBlobObject()->getBlobId();
        $this->assertNotEmpty($blobId, 'Expected non-empty blobId.');
        $startEpoch = $storeResponse->getNewlyCreated()->getBlobObject()->getStorage()->getStartEpoch();
        $endEpoch = $storeResponse->getNewlyCreated()->getBlobObject()->getStorage()->getEndEpoch();
        $this->assertEquals(5, $endEpoch - $startEpoch, 'Expected epochs to be 5.');

        sleep(2);

        $retrievedContent = $client->getBlob($blobId);
        $this->assertStringContainsString(
            $data,
            $retrievedContent,
            'Retrieved content does not contain the original data.'
        );
    }

    public function testStoreBlobWithSendObjectTo(): void
    {
        $client = new WalrusClient($this->publisherUrl, $this->aggregatorUrl);
        $data = "Test data with sendObjectTo: " . uniqid();
        $sendObjectTo = "0xed20e3646700113065bb4a9c60565b671a3545800979cc9e82fcf3fabecb6e41";
        $options = new StoreBlobOptions(2, $sendObjectTo, false);
        $storeResponse = $client->storeBlob($data, $options, false);

        $this->assertTrue($storeResponse->isNewlyCreated(), 'Expected response to be newlyCreated.');
        $blobId = $storeResponse->getNewlyCreated()->getBlobObject()->getBlobId();
        $this->assertNotEmpty($blobId, 'Expected non-empty blobId.');

        sleep(2);

        $retrievedContent = $client->getBlob($blobId);
        $this->assertStringContainsString(
            $data,
            $retrievedContent,
            'Retrieved content does not contain the original data.'
        );
    }

    public function testStoreBlobWithFileMultipleEpochsAndSendObjectTo(): void
    {
        $client = new WalrusClient($this->publisherUrl, $this->aggregatorUrl);
        $filePath = __DIR__ . '/walrus.jpg';
        if (!file_exists($filePath)) {
            $this->markTestSkipped("Test file not found: {$filePath}");
        }
        $sendObjectTo = "0xed20e3646700113065bb4a9c60565b671a3545800979cc9e82fcf3fabecb6e41";
        $options = new StoreBlobOptions(10, $sendObjectTo, false);
        $storeResponse = $client->storeBlob($filePath, $options, true);

        $this->assertTrue($storeResponse->isAlreadyCertified(), 'Expected response to be alreadyCertified.');
        $blobId = $storeResponse->getAlreadyCertified()->getBlobId();
        $this->assertNotEmpty($blobId, 'Expected non-empty blobId.');

        sleep(2);

        $retrievedContent = $client->getBlob($blobId);
        $expectedContent = file_get_contents($filePath);
        $this->assertEquals($expectedContent, $retrievedContent, 'File content does not match retrieved blob.');
    }

    public function testStoreBlobWithTemporaryFile(): void
    {
        $client = new WalrusClient($this->publisherUrl, $this->aggregatorUrl);

        $filePath = __DIR__ . '/temp_test_file.txt';
        $fileContent = "Temporary file test content: " . uniqid();
        file_put_contents($filePath, $fileContent);

        try {
            $this->assertFileExists($filePath, "Temporary test file was not created.");

            $options = new StoreBlobOptions(2, '', false);
            $storeResponse = $client->storeBlob($filePath, $options, true);

            $this->assertTrue(
                $storeResponse->isNewlyCreated() || $storeResponse->isAlreadyCertified(),
                'Expected response to be newlyCreated or alreadyCertified.'
            );
            $blobId = $storeResponse->isNewlyCreated()
                ? $storeResponse->getNewlyCreated()->getBlobObject()->getBlobId()
                : $storeResponse->getAlreadyCertified()->getBlobId();
            $this->assertNotEmpty($blobId, 'Expected non-empty blobId.');

            sleep(2);

            $retrievedContent = $client->getBlob($blobId);
            $expectedContent = file_get_contents($filePath);
            $this->assertEquals($expectedContent, $retrievedContent, 'File content does not match retrieved blob.');
        } finally {
            // Ensure the file is deleted after test
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
