<?php declare(strict_types=1);

namespace Deleu\Sensor;

use Aws\XRay\XRayClient;
use Bref\Context\Context;
use Psr\Log\LoggerInterface;

final class Sensor
{
    private static $context;

    private static $segments = [];

    private $logger;

    private $client;

    public function __construct(XRayClient $client, LoggerInterface $logger)
    {
        // @TODO: Replace XRayClient with an interface to allow custom profiling dashboards.
        $this->client = $client;

        // @TODO: Make Logger optional
        $this->logger = $logger;
    }

    public static function context(Context $context): void
    {
        // @TODO: Replace Context with an owned value object to decouple from Bref and Lambda.
        self::$context = $context;
    }

    private static function trace()
    {
        // @TODO: Study what can be done with the `Sampled` variable.
        $value = self::$context->getTraceId();

        return explode('=', explode(';', $value)[0])[1];
    }

    // @TODO: extract before and after to another class and let Sensor be the entrypoint for the library?

    public function before(string $method): callable
    {
        return function () use ($method) {
            if (! self::$context) {
                $this->logger->warning('Context not available');

                return;
            }

            if (empty(self::$segments)) {
                $prefix = '000000000a';

                $random = str_pad(random_int(1, 999) * 1000, 6, 0, STR_PAD_LEFT);

                $id = $prefix . $random;

                self::$segments[] = new Segment(
                    $id,
                    self::trace(),
                    $method,
                    microtime(true),
                );
            } else {
                $parent = self::$segments[count(self::$segments) - 1];

                $id = $parent->id();

                self::$segments[] = new Segment(
                    ++$id,
                    self::trace(),
                    $method,
                    microtime(true),
                    $parent
                );
            }
        };
    }

    public function after(string $method): callable
    {
        // @TODO: figure out a way to accumulate all of the Segment Documents and make a single call to send them all.
        return function () use ($method) {
            $segment = array_pop(self::$segments);

            $response = $this->client->putTraceSegments([
                'TraceSegmentDocuments' => [$segment->json(microtime(true))]
            ]);

            if(! empty($response['UnprocessedTraceSegments'])) {
                foreach ($response['UnprocessedTraceSegments'] as $error) {
                    $this->logger->warning($error['Message']);
                }
            }
        };
    }
}
