<?php declare(strict_types=1);

namespace Deleu\Sensor;

use Aws\XRay\XRayClient;
use Deleu\Sensor\Trace\Identifier;
use Psr\Log\LoggerInterface;

final class Sensor
{
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

    // @TODO: extract before and after to another class and let Sensor be the entrypoint for the library?

    public function before(string $method): callable
    {
        return function () use ($method) {
            if (empty(self::$segments)) {
                $this->makeSegment($method);
            } else {
                $this->makeChildSegment($method);
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

    private function makeSegment(string $method)
    {
        $prefix = '000000000a';

        $random = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);

        $id = $prefix . $random;

        self::$segments[] = new Segment(
            $id,
            Identifier::trace(),
            $method,
            microtime(true)
        );
    }

    private function makeChildSegment(string $method)
    {
        $parent = self::$segments[count(self::$segments) - 1];

        $id = $parent->id();

        self::$segments[] = new Segment(
            ++$id,
            Identifier::trace(),
            $method,
            microtime(true),
            $parent
        );
    }
}
