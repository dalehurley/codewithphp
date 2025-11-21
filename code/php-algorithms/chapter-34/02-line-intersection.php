<?php

declare(strict_types=1);

/**
 * Chapter 34: Geometric Algorithms - Line Segment Intersection
 *
 * This example demonstrates line segment intersection algorithms:
 * - Basic line segment intersection detection
 * - Finding exact intersection points
 * - Sweep line algorithm for multiple segments
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

require_once '01-point-vector-operations.php';

/**
 * Represents a line segment defined by two endpoints
 */
class LineSegment
{
    /**
     * @param Point $start Starting point
     * @param Point $end Ending point
     */
    public function __construct(
        public Point $start,
        public Point $end
    ) {}

    /**
     * Check if this segment intersects with another segment
     *
     * @param LineSegment $other The other segment
     * @return bool True if segments intersect
     */
    public function intersects(LineSegment $other): bool
    {
        $o1 = GeometryUtils::orientation($this->start, $this->end, $other->start);
        $o2 = GeometryUtils::orientation($this->start, $this->end, $other->end);
        $o3 = GeometryUtils::orientation($other->start, $other->end, $this->start);
        $o4 = GeometryUtils::orientation($other->start, $other->end, $this->end);

        // General case: segments intersect if orientations are different
        if ($o1 !== $o2 && $o3 !== $o4) {
            return true;
        }

        // Special cases: collinear points
        if ($o1 === GeometryUtils::COLLINEAR &&
            GeometryUtils::onSegment($this->start, $other->start, $this->end)) {
            return true;
        }

        if ($o2 === GeometryUtils::COLLINEAR &&
            GeometryUtils::onSegment($this->start, $other->end, $this->end)) {
            return true;
        }

        if ($o3 === GeometryUtils::COLLINEAR &&
            GeometryUtils::onSegment($other->start, $this->start, $other->end)) {
            return true;
        }

        if ($o4 === GeometryUtils::COLLINEAR &&
            GeometryUtils::onSegment($other->start, $this->end, $other->end)) {
            return true;
        }

        return false;
    }

    /**
     * Find the intersection point with another segment
     *
     * @param LineSegment $other The other segment
     * @return Point|null Intersection point or null if no intersection
     */
    public function intersectionPoint(LineSegment $other): ?Point
    {
        $x1 = $this->start->x;
        $y1 = $this->start->y;
        $x2 = $this->end->x;
        $y2 = $this->end->y;

        $x3 = $other->start->x;
        $y3 = $other->start->y;
        $x4 = $other->end->x;
        $y4 = $other->end->y;

        $denom = ($x1 - $x2) * ($y3 - $y4) - ($y1 - $y2) * ($x3 - $x4);

        // Parallel or coincident lines
        if (abs($denom) < 1e-10) {
            return null;
        }

        $t = (($x1 - $x3) * ($y3 - $y4) - ($y1 - $y3) * ($x3 - $x4)) / $denom;
        $u = -(($x1 - $x2) * ($y1 - $y3) - ($y1 - $y2) * ($x1 - $x3)) / $denom;

        // Check if intersection is within both segments
        if ($t >= 0 && $t <= 1 && $u >= 0 && $u <= 1) {
            $x = $x1 + $t * ($x2 - $x1);
            $y = $y1 + $t * ($y2 - $y1);
            return new Point($x, $y);
        }

        return null;
    }

    /**
     * Calculate the length of the segment
     *
     * @return float Segment length
     */
    public function length(): float
    {
        return $this->start->distanceTo($this->end);
    }

    /**
     * Get the midpoint of the segment
     *
     * @return Point Midpoint
     */
    public function midpoint(): Point
    {
        return new Point(
            ($this->start->x + $this->end->x) / 2,
            ($this->start->y + $this->end->y) / 2
        );
    }

    /**
     * Calculate the closest point on this segment to a given point
     *
     * @param Point $p The point
     * @return Point Closest point on segment
     */
    public function closestPointTo(Point $p): Point
    {
        $dx = $this->end->x - $this->start->x;
        $dy = $this->end->y - $this->start->y;

        if ($dx === 0.0 && $dy === 0.0) {
            return clone $this->start;
        }

        $t = (($p->x - $this->start->x) * $dx + ($p->y - $this->start->y) * $dy) /
             ($dx * $dx + $dy * $dy);
        $t = max(0, min(1, $t));

        return new Point(
            $this->start->x + $t * $dx,
            $this->start->y + $t * $dy
        );
    }

    public function __toString(): string
    {
        return sprintf("[%s -> %s]", $this->start, $this->end);
    }
}

/**
 * Detects intersections among multiple line segments using sweep line algorithm
 */
class SegmentIntersectionDetector
{
    private array $segments = [];
    private array $events = [];

    /**
     * Add a segment to the detector
     *
     * @param LineSegment $segment The segment to add
     * @param mixed $id Identifier for the segment
     */
    public function addSegment(LineSegment $segment, mixed $id = null): void
    {
        $id = $id ?? count($this->segments);
        $this->segments[$id] = $segment;

        // Add start and end events
        $this->events[] = [
            'x' => min($segment->start->x, $segment->end->x),
            'type' => 'start',
            'segment' => $id,
            'y' => min($segment->start->y, $segment->end->y)
        ];

        $this->events[] = [
            'x' => max($segment->start->x, $segment->end->x),
            'type' => 'end',
            'segment' => $id,
            'y' => max($segment->start->y, $segment->end->y)
        ];
    }

    /**
     * Find all intersection points among the segments
     *
     * @return array Array of intersection information
     */
    public function findIntersections(): array
    {
        $intersections = [];

        // Sort events by x-coordinate
        usort($this->events, function ($a, $b) {
            $cmp = $a['x'] <=> $b['x'];
            return $cmp !== 0 ? $cmp : $a['y'] <=> $b['y'];
        });

        $active = [];

        foreach ($this->events as $event) {
            if ($event['type'] === 'start') {
                // Check intersection with all active segments
                foreach ($active as $activeId) {
                    if ($this->segments[$event['segment']]->intersects(
                        $this->segments[$activeId]
                    )) {
                        $point = $this->segments[$event['segment']]->intersectionPoint(
                            $this->segments[$activeId]
                        );

                        if ($point !== null) {
                            $intersections[] = [
                                'point' => $point,
                                'segments' => [$event['segment'], $activeId]
                            ];
                        }
                    }
                }

                $active[] = $event['segment'];
            } else {
                // Remove from active set
                $active = array_filter($active, fn($id) => $id !== $event['segment']);
            }
        }

        return $intersections;
    }

    /**
     * Reset the detector
     */
    public function reset(): void
    {
        $this->segments = [];
        $this->events = [];
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Line Segment Intersection Demo ===\n\n";

// Example 1: Basic intersection
echo "1. Basic Line Segment Intersection:\n";
$seg1 = new LineSegment(new Point(0, 0), new Point(10, 10));
$seg2 = new LineSegment(new Point(0, 10), new Point(10, 0));

echo "Segment 1: $seg1\n";
echo "Segment 2: $seg2\n";

if ($seg1->intersects($seg2)) {
    $intersection = $seg1->intersectionPoint($seg2);
    echo "✓ Segments intersect at: $intersection\n";
} else {
    echo "✗ Segments do not intersect\n";
}

// Example 2: Parallel segments (no intersection)
echo "\n2. Parallel Segments:\n";
$seg3 = new LineSegment(new Point(0, 0), new Point(10, 0));
$seg4 = new LineSegment(new Point(0, 2), new Point(10, 2));

echo "Segment 3: $seg3\n";
echo "Segment 4: $seg4\n";

if ($seg3->intersects($seg4)) {
    echo "✓ Segments intersect\n";
} else {
    echo "✗ Segments do not intersect (parallel)\n";
}

// Example 3: Collinear segments
echo "\n3. Collinear Segments:\n";
$seg5 = new LineSegment(new Point(0, 0), new Point(5, 5));
$seg6 = new LineSegment(new Point(3, 3), new Point(8, 8));

echo "Segment 5: $seg5\n";
echo "Segment 6: $seg6\n";

if ($seg5->intersects($seg6)) {
    echo "✓ Segments intersect (collinear overlap)\n";
} else {
    echo "✗ Segments do not intersect\n";
}

// Example 4: Segment properties
echo "\n4. Segment Properties:\n";
$segment = new LineSegment(new Point(0, 0), new Point(6, 8));
echo "Segment: $segment\n";
echo "Length: " . number_format($segment->length(), 2) . "\n";
echo "Midpoint: " . $segment->midpoint() . "\n";

$testPoint = new Point(10, 10);
$closest = $segment->closestPointTo($testPoint);
echo "Closest point to $testPoint: $closest\n";
echo "Distance: " . number_format($testPoint->distanceTo($closest), 2) . "\n";

// Example 5: Multiple segment intersections
echo "\n5. Multiple Segment Intersections (Sweep Line Algorithm):\n";
$detector = new SegmentIntersectionDetector();

$detector->addSegment(
    new LineSegment(new Point(0, 0), new Point(10, 10)),
    'diagonal1'
);
$detector->addSegment(
    new LineSegment(new Point(0, 10), new Point(10, 0)),
    'diagonal2'
);
$detector->addSegment(
    new LineSegment(new Point(5, 0), new Point(5, 10)),
    'vertical'
);
$detector->addSegment(
    new LineSegment(new Point(0, 5), new Point(10, 5)),
    'horizontal'
);

$intersections = $detector->findIntersections();

echo "Found " . count($intersections) . " intersection(s):\n";
foreach ($intersections as $i => $intersection) {
    echo sprintf(
        "  %d. Point: %s between segments: %s\n",
        $i + 1,
        $intersection['point'],
        implode(' and ', $intersection['segments'])
    );
}

// Example 6: Real-world application - Road crossing detection
echo "\n6. Real-World Example - Road Crossing Detection:\n";

class Road
{
    public function __construct(
        public string $name,
        public LineSegment $segment
    ) {}
}

$roads = [
    new Road('Main Street', new LineSegment(
        new Point(0, 50),
        new Point(100, 50)
    )),
    new Road('First Avenue', new LineSegment(
        new Point(30, 0),
        new Point(30, 100)
    )),
    new Road('Second Avenue', new LineSegment(
        new Point(70, 0),
        new Point(70, 100)
    ))
];

echo "Analyzing road intersections...\n";
for ($i = 0; $i < count($roads); $i++) {
    for ($j = $i + 1; $j < count($roads); $j++) {
        if ($roads[$i]->segment->intersects($roads[$j]->segment)) {
            $crossing = $roads[$i]->segment->intersectionPoint($roads[$j]->segment);
            echo sprintf(
                "  ✓ Intersection: %s and %s at %s\n",
                $roads[$i]->name,
                $roads[$j]->name,
                $crossing
            );
        }
    }
}

echo "\n=== Demo Complete ===\n";
