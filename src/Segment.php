<?php declare(strict_types=1);

namespace Deleu\Sensor;

/**
 * @internal
 */
final class Segment
{
    private $id;

    private $trace;

    private $name;

    private $start;

    private $parent;

    public function __construct($id, $trace, $name, $start, ?Segment $parent = null)
    {
        $this->id = $id;
        $this->trace = $trace;
        $this->name = $name;
        $this->start = $start;
        $this->parent = $parent;
    }

    public function id()
    {
        return $this->id;
    }

    public function json($end): string
    {
        $parent = [];

        if ($this->parent) {
            $parent = ['parent_id' => $this->parent->id];
        }

        $attributes = [
                'id' => $this->id,
                'name' => $this->name,
                'trace_id' => $this->trace,
                'start_time' => $this->start,
                'end_time' => $end,
            ] + $parent;

        return json_encode($attributes);
    }
}