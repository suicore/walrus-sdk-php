<?php

namespace Suicore\Walrus\Tests;

use PHPUnit\Framework\TestCase;
use Suicore\Walrus\Helpers\Aes256Encryptor;

final class Aes256EncryptorTest extends TestCase
{
    public function testEncryptionDecryption()
    {
        $plaintext = 'This is a secret message.';
        $key = '01234567890123456789012345678901'; // 32-byte key for AES-256

        // Encrypt the plaintext
        $encrypted = Aes256Encryptor::encrypt($plaintext, $key);
        $this->assertNotEmpty($encrypted, 'Encryption failed to produce output.');

        // Decrypt the ciphertext
        $decrypted = Aes256Encryptor::decrypt($encrypted, $key);
        $this->assertEquals($plaintext, $decrypted, 'Decrypted text does not match the original plaintext.');
    }

    public function testDecryptionWithWrongKey()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Decryption failed.');

        $plaintext = 'This is a secret message.';
        $key = '01234567890123456789012345678901'; // Original key
        $wrongKey = 'wrongkeywrongkeywrongkeywrongk'; // Incorrect key

        // Encrypt the plaintext
        $encrypted = Aes256Encryptor::encrypt($plaintext, $key);

        // Attempt decryption with the wrong key
        Aes256Encryptor::decrypt($encrypted, $wrongKey);
    }

    public function testEncryptionWithInvalidKeyLength()
    {
        $plaintext = 'This is a secret message.';
        $shortKey = 'shortkey'; // Invalid key length, should still work, but not advised

        // Attempt encryption with an invalid key length
        $encrypted = Aes256Encryptor::encrypt($plaintext, $shortKey);
        $decrypted = Aes256Encryptor::decrypt($encrypted, $shortKey);
        $this->assertEquals($plaintext, $decrypted, 'Decrypted text does not match the original plaintext.');
    }
}