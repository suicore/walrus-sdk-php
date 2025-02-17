<?php

namespace Suicore\Walrus\Helpers;

class Aes256Encryptor
{
    /**
     * Encrypts the given plaintext using AES-256-CBC.
     *
     * @param string $plaintext The data to encrypt.
     * @param string $key       The encryption key (32 bytes for AES-256).
     *
     * @return string The base64-encoded ciphertext with the IV appended.
     *
     * @throws \Exception if encryption fails.
     */
    public static function encrypt(string $plaintext, string $key): string
    {
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);

        $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new \Exception('Encryption failed.');
        }

        return base64_encode($iv . $ciphertext);
    }

    /**
     * Decrypts the given ciphertext using AES-256-CBC.
     *
     * @param string $ciphertextWithIv The base64-encoded ciphertext with the IV appended.
     * @param string $key              The encryption key (32 bytes for AES-256).
     *
     * @return string The decrypted plaintext.
     *
     * @throws \Exception if decryption fails.
     */
    public static function decrypt(string $ciphertextWithIv, string $key): string
    {
        $ciphertextWithIv = base64_decode($ciphertextWithIv);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');

        $iv = substr($ciphertextWithIv, 0, $ivLength);
        $ciphertext = substr($ciphertextWithIv, $ivLength);

        $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            throw new \Exception('Decryption failed.');
        }

        return $plaintext;
    }
}
