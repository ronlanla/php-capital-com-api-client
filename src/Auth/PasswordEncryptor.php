<?php

namespace CapitalCom\Auth;

use CapitalCom\Exception\CapitalComException;

class PasswordEncryptor
{
    /**
     * Encrypt password using RSA public key
     *
     * @param string $encryptionKey Base64 encoded public key
     * @param int $timestamp Timestamp from encryption key response
     * @param string $password Plain text password
     * @return string Base64 encoded encrypted password
     * @throws CapitalComException
     */
    public static function encrypt(string $encryptionKey, int $timestamp, string $password): string
    {
        try {
            // Prepare input: password|timestamp
            $input = $password . '|' . $timestamp;
            $input = base64_encode($input);
            
            // Format the public key
            $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n" 
                         . chunk_split($encryptionKey, 64, "\n")
                         . "-----END PUBLIC KEY-----";
            
            // Get public key resource
            $publicKey = openssl_pkey_get_public($publicKeyPem);
            if (!$publicKey) {
                throw new CapitalComException('Invalid encryption key');
            }
            
            // Encrypt with PKCS1 padding
            $encrypted = '';
            $success = openssl_public_encrypt($input, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
            
            if (!$success) {
                throw new CapitalComException('Failed to encrypt password: ' . openssl_error_string());
            }
            
            // Return base64 encoded result
            return base64_encode($encrypted);
            
        } catch (\Exception $e) {
            throw new CapitalComException('Password encryption failed: ' . $e->getMessage());
        }
    }
}