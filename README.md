# Walrus PHP SDK

The Walrus PHP SDK provides a convenient, strongly-typed interface for interacting with the Walrus HTTP APIs. It supports storing and retrieving blobs and quilts via the publisher and aggregator endpoints of the Walrus service, leveraging Guzzle HTTP for robust HTTP communication.

## Features

- **Store Blobs**: Upload data (or files) as blobs with configurable storage epochs, send-to address, and deletability options.
- **Retrieve Blobs**: Easily retrieve blob content using a unique blob ID.
- **Strongly Typed Responses**: Work with dedicated response classes such as `StoreBlobResponse`, `NewlyCreatedResponse`, and `AlreadyCertifiedResponse`.

## Installation

Install via Composer:

```bash
composer require suicore/walrus-sdk
```

## Usage

Here’s a basic example of how to use the SDK:

```php
<?php

require 'vendor/autoload.php';

use Suicore\Walrus\Types\StoreBlobOrQuiltOptions;
use Suicore\Walrus\WalrusClient;


// Set your API endpoints
$publisherUrl = 'https://publisher.walrus-testnet.walrus.space';
$aggregatorUrl = 'https://aggregator.walrus-testnet.walrus.space';

// Instantiate the client
$client = new WalrusClient($publisherUrl, $aggregatorUrl);

// Prepare options for storing a blob
$options = new StoreBlobOrQuiltOptions(epochs: 2);

// Store a text blob
$storeResponse = $client->storeBlob("Hello, Walrus!", $options);

// Check if the blob was newly created
if ($storeResponse->isNewlyCreated()) {
    $blobId = $storeResponse->getNewlyCreated()->getBlobObject()->getBlobId();
    echo "Blob stored successfully with ID: {$blobId}\n";
} else {
    // Handle the already certified case
    $blobId = $storeResponse->getAlreadyCertified()->getBlobId();
    echo "Blob was already certified with ID: {$blobId}\n";
}

// Retrieve the blob content
$content = $client->getBlob($blobId);
echo "Retrieved content: {$content}\n";
```

### Uploading Files
To upload a blob, simply pass the file path to the `storeBlob` method and set the $isFile parameter to `true`:

```php
// Save a file as a blob
$file = '/path/to/file.txt';
$options = new StoreBlobOrQuiltOptions(epochs: 2);
$storeResponse = $client->storeBlob(dataOrPath: $file, options: $options, isFile: true);
```

### Uploading a file in a quilt
The SDK supports uploading multiple files as a single quilt using the `storeQuilt` method. Each file can be associated with custom metadata.

```php
use Suicore\Walrus\WalrusClient;
use Suicore\Walrus\Types\StoreBlobOrQuiltOptions;
use Suicore\Walrus\Types\QuiltElementFile;
use Suicore\Walrus\Types\QuiltElementFileMetadata;

$client = new WalrusClient($publisherUrl, $aggregatorUrl);
$options = new StoreBlobOrQuiltOptions(epochs: 2);

$files = [
    new QuiltElementFile('wal1.jpg', __DIR__ . '/walrus.jpg'),
    new QuiltElementFile('wal2.jpg', fopen(__DIR__ . '/walrus.jpg', 'rb')), // Open resource
];

$metadata = [
    new QuiltElementFileMetadata('wal1.jpg', (object)['creator' => 'walrus', 'version' => '1.0']),
    new QuiltElementFileMetadata('wal2.jpg', (object)['type' => 'logo', 'format' => 'png']),
];

$storeResponse = $client->storeQuilt($files, $options, $metadata);

$files = $storeResponse->getStoredQuiltBlobs()->getQuiltFiles();
foreach ($files as $file) {
    echo "Stored patch ID: " . $file->getQuiltPatchId() . "\n";
}
```

### Retrieving a Quilt Patch or Quilt by name
```php
$patchId = 'quilt-file-patch-id';
$content = $client->getQuilt($patchId);

$blobId = 'quilt-blob-id';
$fileContent = $client->getQuilt($blobId, 'wal1.jpg');
```

### Encryption
The Walrus SDK provides a simple, but secure way to encrypt and decrypt data using the `Aes256Encryptor` class. Here’s an example:
```php
use Suicore\Walrus\Helpers\Aes256Encryptor;

// Encrypt data
$encryptor = new Aes256Encryptor('01234567890123456789012345678901'); // EXAMPLE, USE A DIFFERENT KEY!
$encryptedData = $encryptor->encrypt('Hello, Walrus!');
echo "Encrypted data: {$encryptedData}\n";

// Decrypt data
$decryptedData = $encryptor->decrypt($encryptedData);
echo "Decrypted data: {$decryptedData}\n";
```

A key length of 32 bytes is required for AES-256 encryption. Shorter or longer keys will work, but will be more unpredictable and less secure.


### API Methods

#### storeBlob
Stores a blob using the publisher API.

Parameters:
- `string dataOrPath`: The blob data or file path.
- `StoreBlobOrQuiltOptions $options`: Options such as epochs, sendObjectTo address, and deletability.
- `bool $isFile`: Set to true if $dataOrPath is a file path.
- Returns: A StoreBlobResponse object representing the response from the API.

#### getBlob
Retrieves a blob from the aggregator API.

Parameters:
- `string $blobId`: The unique blob identifier.
- Returns: The blob content as a string.

#### storeQuilt
Stores multiple files as a quilt with optional metadata.

Parameters:
- `QuiltFile[] $files`: An array of QuiltFile objects containing identifier and file source.
- `StoreBlobOrQuiltOptions $options`: Storage options.
- `QuiltMetadata[] $metadata`: (Optional) Metadata associated with each file.
- Returns: StoreQuiltResponse

#### getQuilt
Retrieves quilt content from the aggregator.

Parameters:
- `string $blobOrPatchId`: A blob ID (for full quilt) or a patch ID (for specific patch).
- `string|null $filename`: Optional filename to extract a specific file from the quilt.
- Returns: File contents as a string.

### Testing
To run the test suite, use the following commands:

#### Unit Tests
```bash
composer test
```

#### Include Integration Tests
```bash
export RUN_INTEGRATION_TESTS=1
vendor/bin/phpunit --filter WalrusClientE2ETest
```

## Contributing
Contributions are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License
This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
