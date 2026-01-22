<?php
/**
 * Encryption Utilities
 *
 * AES-256-GCM encryption for sensitive member data.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Encryption
 */
class SocietyPress_Encryption {

    /**
     * Cipher algorithm.
     */
    private const CIPHER = 'aes-256-gcm';

    /**
     * Auth tag length.
     */
    private const TAG_LENGTH = 16;

    /**
     * Cached key.
     *
     * @var string|null
     */
    private static ?string $key = null;

    /**
     * Generate a new encryption key.
     *
     * @return string Base64-encoded key.
     */
    public static function generate_key(): string {
        return base64_encode( random_bytes( 32 ) );
    }

    /**
     * Get the encryption key.
     *
     * @return string Raw key.
     * @throws Exception If key not found.
     */
    private static function get_key(): string {
        if ( null === self::$key ) {
            $stored = get_option( SOCIETYPRESS_ENCRYPTION_KEY_OPTION );

            if ( empty( $stored ) ) {
                throw new Exception( __( 'Encryption key not found.', 'societypress' ) );
            }

            self::$key = base64_decode( $stored );
        }

        return self::$key;
    }

    /**
     * Encrypt a value.
     *
     * @param string $plaintext Value to encrypt.
     * @return string Encrypted value (base64).
     */
    public static function encrypt( string $plaintext ): string {
        if ( empty( $plaintext ) ) {
            return '';
        }

        try {
            $key       = self::get_key();
            $iv_length = openssl_cipher_iv_length( self::CIPHER );
            $iv        = random_bytes( $iv_length );
            $tag       = '';

            $ciphertext = openssl_encrypt(
                $plaintext,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                '',
                self::TAG_LENGTH
            );

            if ( false === $ciphertext ) {
                return '';
            }

            return base64_encode( $iv . $ciphertext . $tag );

        } catch ( Exception $e ) {
            error_log( 'SocietyPress encryption error: ' . $e->getMessage() );
            return '';
        }
    }

    /**
     * Decrypt a value.
     *
     * @param string $encrypted Encrypted value (base64).
     * @return string Decrypted value.
     */
    public static function decrypt( string $encrypted ): string {
        if ( empty( $encrypted ) ) {
            return '';
        }

        try {
            $key      = self::get_key();
            $combined = base64_decode( $encrypted, true );

            if ( false === $combined ) {
                return '';
            }

            $iv_length = openssl_cipher_iv_length( self::CIPHER );

            if ( strlen( $combined ) < $iv_length + 1 + self::TAG_LENGTH ) {
                return '';
            }

            $iv         = substr( $combined, 0, $iv_length );
            $tag        = substr( $combined, -self::TAG_LENGTH );
            $ciphertext = substr( $combined, $iv_length, -self::TAG_LENGTH );

            $plaintext = openssl_decrypt(
                $ciphertext,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            return false === $plaintext ? '' : $plaintext;

        } catch ( Exception $e ) {
            error_log( 'SocietyPress decryption error: ' . $e->getMessage() );
            return '';
        }
    }

    /**
     * Generate a random token.
     *
     * @param int $length Byte length.
     * @return string Hex token.
     */
    public static function generate_token( int $length = 32 ): string {
        return bin2hex( random_bytes( $length ) );
    }
}
