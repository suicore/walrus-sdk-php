<?php

namespace Suicore\Walrus;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Suicore\Walrus\Types\QuiltElementFile;
use Suicore\Walrus\Types\QuiltElementFileMetadata;
use Suicore\Walrus\Types\StoreBlobOrQuiltOptions;
use Suicore\Walrus\Types\StoreBlobResponse;
use Suicore\Walrus\Types\StoreQuiltResponse;

class WalrusClient
{
    private Client $publisherClient;
    private Client $aggregatorClient;

    public function __construct(
        ?string $publisherUrl = '',
        ?string $aggregatorUrl = '',
        ?array $options = [],
        ?float $timeout = 20.0,
        ?Client $publisherClient = null,
        ?Client $aggregatorClient = null
    ) {
        if ($publisherClient) {
            $this->publisherClient = $publisherClient;
        } elseif ($publisherUrl != '') {
            $publisherOptions = $options['publisher'] ?? [];
            $publisherOptions = array_merge(['base_uri' => $publisherUrl, 'timeout' => $timeout], $publisherOptions);
            $this->publisherClient = new Client($publisherOptions);
        } else {
            throw new \InvalidArgumentException('Either $publisherUrl or $publisherClient must be provided.');
        }

        if ($aggregatorClient) {
            $this->aggregatorClient = $aggregatorClient;
        } elseif ($aggregatorUrl != '') {
            $aggregatorOptions = $options['aggregator'] ?? [];
            $aggregatorOptions = array_merge(['base_uri' => $aggregatorUrl, 'timeout' => $timeout], $aggregatorOptions);
            $this->aggregatorClient = new Client($aggregatorOptions);
        } else {
            throw new \InvalidArgumentException('Either $aggregatorUrl or $aggregatorClient must be provided.');
        }
    }

    /**
     * Store a blob using the publisher API.
     *
     * @param string           $dataOrPath The data to store or a file path.
     * @param StoreBlobOrQuiltOptions $options    Options for the store request.
     * @param bool             $isFile     Whether $dataOrPath is a file path.
     *
     * @return StoreBlobResponse
     *
     * @throws \Exception if the request fails.
     */
    public function storeBlob(
        string $dataOrPath,
        StoreBlobOrQuiltOptions $options,
        bool $isFile = false,
    ): StoreBlobResponse {
        $query = [];
        $query['epochs'] = $options->getEpochs();
        if ($options->getSendObjectTo() !== '') {
            $query['send_object_to'] = $options->getSendObjectTo();
        }
        if ($options->isDeletable()) {
            $query['deletable'] = 'true';
        }

        $uri = '/v1/blobs';
        if (!empty($query)) {
            $uri .= '?' . http_build_query($query);
        }

        try {
            $body = $isFile ? fopen($dataOrPath, 'r') : $dataOrPath;
            $response = $this->publisherClient->request('PUT', $uri, [
                'body' => $body,
            ]);
            $content = $response->getBody()->getContents();
            return StoreBlobResponse::fromJson($content);
        } catch (GuzzleException $e) {
            throw new \Exception("Guzzle error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Store a Walrus quilt (multiple blobs in one request).
     *
     * @param array<QuiltElementFile> $files    key = identifier, value = path or stream
     * @param StoreBlobOrQuiltOptions       $options  epochs / send_object_to / deletable
     * @param array<QuiltElementFileMetadata>          $metadata optional Walrus-native metadata
     *                                                e.g. [
     *                                                  ['identifier'=>'logo.png','tags'=>['type'=>'logo']]
     *                                                ]
     *
     * @throws \Exception on HTTP/Guzzle errors
     */
    public function storeQuilt(
        array $files,
        StoreBlobOrQuiltOptions $options,
        array $metadata = []
    ): StoreQuiltResponse {
        $query = ['epochs' => $options->getEpochs()];
        if ($options->getSendObjectTo() !== '') {
            $query['send_object_to'] = $options->getSendObjectTo();
        }
        if ($options->isDeletable()) {
            $query['deletable'] = 'true';
        }

        $uri = '/v1/quilts' . (!empty($query) ? '?' . http_build_query($query) : '');

        $multipart = [];

        foreach ($files as $file) {
            $identifier = $file->getIdentifier();
            $source = $file->getSource();

            $multipart[] = [
                'name'     => $identifier,
                'contents' => is_resource($source) ? $source : fopen($source, 'rb'),
                'filename' => is_string($source) ? basename($source) : $identifier,
            ];
        }


        if ($metadata !== []) {
            $multipart[] = [
                'name'     => '_metadata',
                'contents' => json_encode(
                    array_map(
                        fn(QuiltElementFileMetadata $meta) => $meta->toArray(),
                        $metadata
                    ),
                    JSON_THROW_ON_ERROR
                ),
            ];
        }

        try {
            $response = $this->publisherClient->request('PUT', $uri, [
                'multipart' => $multipart,
            ]);

            return StoreQuiltResponse::fromJson($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            throw new \Exception("Guzzle error: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function getBlob(string $blobId): string
    {
        $uri = "/v1/blobs/{$blobId}";
        try {
            $response = $this->aggregatorClient->request('GET', $uri);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new \Exception("Guzzle error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getQuilt(string $quiltId, string $name = null): string
    {
        if (is_null($name)) {
            $uri = "/v1/blobs/by-quilt-patch-id/{$quiltId}";
        } else {
            $uri = "/v1/blobs/by-quilt-id/{$quiltId}/{$name}";
        }
        try {
            $response = $this->aggregatorClient->request('GET', $uri);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new \Exception("Guzzle error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
