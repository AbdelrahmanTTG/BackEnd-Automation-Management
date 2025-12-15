<?php

namespace App\Helpers;

use Exception;

class EncryptionHelper
{
    private static function getKey(): string
    {
        $key = "MY_SECRET_KEY";
        if (empty($key)) {
            throw new Exception('Encryption key not configured');
        }
        return hash('sha256', $key, true);
    }
    private static function getIv(): string
    {
        return substr(hash('sha256', "MY_SECRET_KEY"), 0, 16);
    }
    public static function encrypt(array $data): string
    {
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
            
            $compressed = gzcompress($json, 6);
            if ($compressed === false) {
                throw new Exception('Compression failed');
            }

            $encrypted = openssl_encrypt(
                $compressed,
                'AES-256-CBC',
                self::getKey(),
                OPENSSL_RAW_DATA,
                self::getIv()
            );

            if ($encrypted === false) {
                throw new Exception('Encryption failed');
            }

            return base64_encode($encrypted);
        } catch (Exception $e) {
            throw new Exception('Encryption process failed: ' . $e->getMessage());
        }
    }

    public static function decrypt(string $data): array
    {
        try {
            $decoded = base64_decode($data, true);
            if ($decoded === false) {
                throw new Exception('Base64 decode failed');
            }

            $decrypted = openssl_decrypt(
                $decoded,
                'AES-256-CBC',
                self::getKey(),
                OPENSSL_RAW_DATA,
                self::getIv()
            );

            if ($decrypted === false) {
                throw new Exception('Decryption failed');
            }

            $decompressed = gzuncompress($decrypted);
            if ($decompressed === false) {
                throw new Exception('Decompression failed');
            }

            return json_decode($decompressed, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new Exception('Decryption process failed: ' . $e->getMessage());
        }
    }
}