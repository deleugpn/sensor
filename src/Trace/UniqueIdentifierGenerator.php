<?php declare(strict_types=1);

namespace Deleu\Sensor\Trace;

/**
 * @internal
 */
final class UniqueIdentifierGenerator
{
    public static function generateTrace(): string
    {
        if (extension_loaded('openssl')) {
            return self::usingOpenSSL();
        }

        return self::random();
    }

    private static function usingOpenSSL(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(12));
    }

    private static function random(): string
    {
        return bin2hex(random_bytes(12));
    }
}
