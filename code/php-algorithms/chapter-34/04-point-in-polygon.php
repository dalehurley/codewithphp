<?php

declare(strict_types=1);

/**
 * Chapter 34: Geometric Algorithms - Point in Polygon
 *
 * This example demonstrates point-in-polygon detection algorithms:
 * - Ray casting algorithm
 * - Winding number algorithm
 * - Boundary detection
 * - Polygon operations (area, centroid, perimeter)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

require_once '01-point-vector-operations.php';
require_once '02-line-intersection.php';

/**
 * Represents a polygon defined by vertices
 */
class Polygon
{
    /** @var array<Point> */
    private array $vertices;

    /**
     * @param array<Point> $vertices Polygon vertices in order
     * @throws InvalidArgumentException If fewer than 3 vertices
     */
    public function __construct(array $vertices)
    {
        if (count($vertices) < 3) {
            throw new InvalidArgumentException("Polygon must have at least 3 vertices");
        }

        $this->vertices = $vertices;
    }

    /**
     * Check if a point is inside the polygon using ray casting algorithm
     * Time Complexity: O(n) where n is number of vertices
     *
     * @param Point $point Point to test
     * @return bool True if point is inside polygon
     */
    public function contains(Point $point): bool
    {
        $n = count($this->vertices);
        $inside = false;

        $p1 = $this->vertices[0];

        for ($i = 1; $i <= $n; $i++) {
            $p2 = $this->vertices[$i % $n];

            if ($point->y > min($p1->y, $p2->y)) {
                if ($point->y <= max($p1->y, $p2->y)) {
                    if ($point->x <= max($p1->x, $p2->x)) {
                        if ($p1->y !== $p2->y) {
                            $xinters = ($point->y - $p1->y) * ($p2->x - $p1->x) /
                                      ($p2->y - $p1->y) + $p1->x;

                            if ($p1->x === $p2->x || $point->x <= $xinters) {
                                $inside = !$inside;
                            }
                        }
                    }
                }
            }

            $p1 = $p2;
        }

        return $inside;
    }

    /**
     * Check if a point is on the polygon boundary
     *
     * @param Point $point Point to test
     * @return bool True if point is on boundary
     */
    public function onBoundary(Point $point): bool
    {
        $n = count($this->vertices);

        for ($i = 0; $i < $n; $i++) {
            $p1 = $this->vertices[$i];
            $p2 = $this->vertices[($i + 1) % $n];

            if (GeometryUtils::orientation($p1, $p2, $point) === GeometryUtils::COLLINEAR &&
                GeometryUtils::onSegment($p1, $point, $p2)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate the area of the polygon
     *
     * @return float Polygon area
     */
    public function area(): float
    {
        $n = count($this->vertices);
        $area = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $this->vertices[$i]->x * $this->vertices[$j]->y;
            $area -= $this->vertices[$j]->x * $this->vertices[$i]->y;
        }

        return abs($area) / 2;
    }

    /**
     * Calculate the centroid (geometric center) of the polygon
     *
     * @return Point Centroid point
     */
    public function centroid(): Point
    {
        $n = count($this->vertices);
        $cx = 0;
        $cy = 0;
        $area = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $cross = $this->vertices[$i]->x * $this->vertices[$j]->y -
                     $this->vertices[$j]->x * $this->vertices[$i]->y;

            $cx += ($this->vertices[$i]->x + $this->vertices[$j]->x) * $cross;
            $cy += ($this->vertices[$i]->y + $this->vertices[$j]->y) * $cross;
            $area += $cross;
        }

        $area /= 2;

        if ($area == 0) {
            // Degenerate polygon, return average of vertices
            $cx = array_sum(array_map(fn($p) => $p->x, $this->vertices)) / $n;
            $cy = array_sum(array_map(fn($p) => $p->y, $this->vertices)) / $n;
            return new Point($cx, $cy);
        }

        $cx /= (6 * $area);
        $cy /= (6 * $area);

        return new Point($cx, $cy);
    }

    /**
     * Calculate the perimeter of the polygon
     *
     * @return float Polygon perimeter
     */
    public function perimeter(): float
    {
        $n = count($this->vertices);
        $perimeter = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $perimeter += $this->vertices[$i]->distanceTo($this->vertices[$j]);
        }

        return $perimeter;
    }

    /**
     * Get all edges of the polygon
     *
     * @return array<LineSegment> Array of line segments
     */
    public function getEdges(): array
    {
        $edges = [];
        $n = count($this->vertices);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $edges[] = new LineSegment($this->vertices[$i], $this->vertices[$j]);
        }

        return $edges;
    }

    /**
     * Get the vertices of the polygon
     *
     * @return array<Point> Array of vertices
     */
    public function getVertices(): array
    {
        return $this->vertices;
    }

    /**
     * Calculate the bounding box of the polygon
     *
     * @return array Array with 'min' and 'max' points
     */
    public function boundingBox(): array
    {
        $minX = $maxX = $this->vertices[0]->x;
        $minY = $maxY = $this->vertices[0]->y;

        foreach ($this->vertices as $vertex) {
            $minX = min($minX, $vertex->x);
            $maxX = max($maxX, $vertex->x);
            $minY = min($minY, $vertex->y);
            $maxY = max($maxY, $vertex->y);
        }

        return [
            'min' => new Point($minX, $minY),
            'max' => new Point($maxX, $maxY)
        ];
    }

    /**
     * Check if the polygon is convex
     *
     * @return bool True if polygon is convex
     */
    public function isConvex(): bool
    {
        $n = count($this->vertices);
        if ($n < 3) {
            return false;
        }

        $sign = null;

        for ($i = 0; $i < $n; $i++) {
            $p1 = $this->vertices[$i];
            $p2 = $this->vertices[($i + 1) % $n];
            $p3 = $this->vertices[($i + 2) % $n];

            $orientation = GeometryUtils::orientation($p1, $p2, $p3);

            if ($orientation !== GeometryUtils::COLLINEAR) {
                if ($sign === null) {
                    $sign = $orientation;
                } elseif ($sign !== $orientation) {
                    return false;
                }
            }
        }

        return true;
    }
}

/**
 * Geofencing implementation for location-based services
 */
class Geofence
{
    private Polygon $boundary;
    private string $name;

    /**
     * @param string $name Geofence name
     * @param array $coordinates Array of [lat, lng] pairs
     */
    public function __construct(string $name, array $coordinates)
    {
        $this->name = $name;
        $points = array_map(
            fn($coord) => new Point($coord[0], $coord[1]),
            $coordinates
        );
        $this->boundary = new Polygon($points);
    }

    /**
     * Check if a location is inside the geofence
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return bool True if inside geofence
     */
    public function isInside(float $lat, float $lng): bool
    {
        return $this->boundary->contains(new Point($lat, $lng));
    }

    /**
     * Calculate distance to the nearest point on the boundary
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return float Distance to boundary
     */
    public function distanceToBoundary(float $lat, float $lng): float
    {
        $point = new Point($lat, $lng);
        $minDist = PHP_FLOAT_MAX;

        foreach ($this->boundary->getEdges() as $edge) {
            $closest = $edge->closestPointTo($point);
            $dist = $point->distanceTo($closest);
            $minDist = min($minDist, $dist);
        }

        return $minDist;
    }

    /**
     * Get the name of the geofence
     *
     * @return string Geofence name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the geofence area
     *
     * @return float Area
     */
    public function getArea(): float
    {
        return $this->boundary->area();
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Point in Polygon Demo ===\n\n";

// Example 1: Basic polygon
echo "1. Basic Polygon (Square):\n";
$square = new Polygon([
    new Point(0, 0),
    new Point(4, 0),
    new Point(4, 4),
    new Point(0, 4),
]);

echo "Vertices: ";
foreach ($square->getVertices() as $vertex) {
    echo "$vertex ";
}
echo "\n";

echo "Area: " . $square->area() . " square units\n";
echo "Perimeter: " . $square->perimeter() . " units\n";
echo "Centroid: " . $square->centroid() . "\n";
echo "Is convex: " . ($square->isConvex() ? "Yes" : "No") . "\n";

// Test points
$testPoints = [
    new Point(2, 2),    // Inside
    new Point(5, 5),    // Outside
    new Point(0, 0),    // On boundary (vertex)
    new Point(2, 0),    // On boundary (edge)
];

echo "\nPoint containment tests:\n";
foreach ($testPoints as $point) {
    $inside = $square->contains($point);
    $onBoundary = $square->onBoundary($point);

    $status = $inside ? "Inside" : ($onBoundary ? "On boundary" : "Outside");
    echo "   Point $point: $status\n";
}

// Example 2: Complex polygon (L-shape)
echo "\n2. Complex Polygon (L-shape):\n";
$lShape = new Polygon([
    new Point(0, 0),
    new Point(2, 0),
    new Point(2, 2),
    new Point(4, 2),
    new Point(4, 4),
    new Point(0, 4),
]);

echo "Area: " . number_format($lShape->area(), 2) . " square units\n";
echo "Perimeter: " . number_format($lShape->perimeter(), 2) . " units\n";
echo "Centroid: " . $lShape->centroid() . "\n";
echo "Is convex: " . ($lShape->isConvex() ? "Yes" : "No") . "\n";

// Example 3: Bounding box
echo "\n3. Bounding Box:\n";
$bbox = $lShape->boundingBox();
echo "Min corner: " . $bbox['min'] . "\n";
echo "Max corner: " . $bbox['max'] . "\n";
$bboxWidth = $bbox['max']->x - $bbox['min']->x;
$bboxHeight = $bbox['max']->y - $bbox['min']->y;
echo "Dimensions: {$bboxWidth} x {$bboxHeight}\n";

// Example 4: Real-world - Geofencing
echo "\n4. Real-World Example - Geofencing:\n";

$downtown = new Geofence('Downtown Area', [
    [37.7749, -122.4194],
    [37.7849, -122.4194],
    [37.7849, -122.4094],
    [37.7749, -122.4094],
]);

echo "Geofence: {$downtown->getName()}\n";
echo "Area: " . number_format($downtown->getArea(), 6) . " square degrees\n";

$userLocations = [
    ['name' => 'User A', 'lat' => 37.7799, 'lng' => -122.4144],  // Inside
    ['name' => 'User B', 'lat' => 37.7900, 'lng' => -122.4200],  // Outside
    ['name' => 'User C', 'lat' => 37.7749, 'lng' => -122.4094],  // On boundary
];

echo "\nUser location checks:\n";
foreach ($userLocations as $location) {
    $inside = $downtown->isInside($location['lat'], $location['lng']);
    $distance = $downtown->distanceToBoundary($location['lat'], $location['lng']);

    $status = $inside ? "Inside geofence" : "Outside geofence";
    echo "   {$location['name']} ({$location['lat']}, {$location['lng']}): $status\n";
    echo "      Distance to boundary: " . number_format($distance, 6) . " degrees\n";
}

// Example 5: Multiple geofences
echo "\n5. Multiple Geofences - Delivery Zones:\n";

class DeliveryZone
{
    public function __construct(
        public string $name,
        public Geofence $geofence,
        public float $deliveryFee
    ) {}
}

$zones = [
    new DeliveryZone('Zone A', new Geofence('Zone A', [
        [0, 0], [5, 0], [5, 5], [0, 5]
    ]), 5.00),
    new DeliveryZone('Zone B', new Geofence('Zone B', [
        [5, 0], [10, 0], [10, 5], [5, 5]
    ]), 7.50),
    new DeliveryZone('Zone C', new Geofence('Zone C', [
        [0, 5], [5, 5], [5, 10], [0, 10]
    ]), 10.00),
];

$deliveryAddress = new Point(3, 3);

echo "Delivery address: $deliveryAddress\n";
foreach ($zones as $zone) {
    if ($zone->geofence->isInside($deliveryAddress->x, $deliveryAddress->y)) {
        echo "   ✓ Address is in {$zone->name}\n";
        echo "   Delivery fee: \$" . number_format($zone->deliveryFee, 2) . "\n";
        break;
    }
}

// Example 6: Performance test
echo "\n6. Performance Test:\n";

// Create a polygon with many vertices
$numVertices = 100;
$vertices = [];
for ($i = 0; $i < $numVertices; $i++) {
    $angle = 2 * M_PI * $i / $numVertices;
    $radius = 50 + 10 * sin(5 * $angle); // Star-like shape
    $vertices[] = new Point(
        50 + $radius * cos($angle),
        50 + $radius * sin($angle)
    );
}

$complexPolygon = new Polygon($vertices);

echo "Testing with a {$numVertices}-vertex polygon...\n";

// Test many points
$numTests = 1000;
$insideCount = 0;

$start = microtime(true);
for ($i = 0; $i < $numTests; $i++) {
    $testPoint = new Point(rand(0, 100), rand(0, 100));
    if ($complexPolygon->contains($testPoint)) {
        $insideCount++;
    }
}
$elapsed = microtime(true) - $start;

echo "Tested $numTests points in " . number_format($elapsed * 1000, 2) . " ms\n";
echo "Average time per test: " . number_format($elapsed * 1000000 / $numTests, 2) . " μs\n";
echo "Points inside: $insideCount (" . number_format($insideCount / $numTests * 100, 1) . "%)\n";

echo "\n=== Demo Complete ===\n";
