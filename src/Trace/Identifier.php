<?php declare(strict_types=1);

namespace Deleu\Sensor\Trace;

use Bref\Context\Context;

final class Identifier
{
    private static $context;

    public static function context(Context $context): void
    {
        // @TODO: Replace Context with an owned value object to decouple from Bref and Lambda.
        self::$context = $context;
    }

    public static function trace(): string
    {
        if (self::$context) {
            return self::getTraceFromLambdaContext();
        }

        return sprintf('1-%s-%s', dechex(time()), UniqueIdentifierGenerator::generateTrace());
    }

    private static function getTraceFromLambdaContext(): string
    {
        // @TODO: Study what can be done with the `Sampled` variable.
        $value = self::$context->getTraceId();

        return explode('=', explode(';', $value)[0])[1];
    }
}
