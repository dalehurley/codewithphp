<?php

declare(strict_types=1);

/**
 * Chapter 34: Geometric Algorithms - Convex Hull
 *
 * This example demonstrates convex hull algorithms:
 * - Graham Scan algorithm (O(n log n))
 * - Jarvis March (Gift Wrapping) algorithm (O(nh))
 * - Hull area and perimeter calculations
 *
 * A convex hull is the smallest convex polygon that contains all given points.
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

require_once '01-point-vector-operations.php';

/**
 * Convex Hull implementation with multiple algorithms
 */
class ConvexHull
{
    /**
     * Compute convex hull using Graham Scan algorithm
     * Time Complexity: O(n log n)
     * Space Complexity: O(n)
     *
     * @param array<Point> $points Input points
     * @return array<Point> Points forming the convex hull
     * @throws InvalidArgumentException If fewer than 3 points provided
     */
    public static function grahamScan(array $points): array
    {
        if (count($points) < 3) {
            throw new InvalidArgumentException("At least 3 points required for convex hull");
        }

        // Find bottom-most point (or leftmost if tie)
        $start = $points[0];
        foreach ($points as $point) {
            if ($point->y < $start->y || ($point->y === $start->y && $point->x < $start->x)) {
                $start = $point;
            }
        }

        // Sort points by polar angle with respect to start point
        usort($points, function ($a, $b) use ($start) {
            $orientation = GeometryUtils::orientation($start, $a, $b);

            if ($orientation === GeometryUtils::COLLINEAR) {
                // If collinear, closer point comes first
                return $start->distanceTo($a) <=> $start->distanceTo($b);
            }

            return $orientation === GeometryUtils::COUNTER_CLOCKWISE ? -1 : 1;
        });

        // Build convex hull
        $hull = [$points[0], $points[1]];

        for ($i = 2; $i < count($points); $i++) {
            // Remove points that make clockwise turn
            while (count($hull) > 1) {
                $top = array_pop($hull);
                $nextTop = end($hull);

                if (GeometryUtils::orientation($nextTop, $top, $points[$i]) !==
                    GeometryUtils::CLOCKWISE) {
                    $hull[] = $top;
                    break;
                }
            }

            $hull[] = $points[$i];
        }

        return $hull;
    }

    /**
     * Compute convex hull using Jarvis March (Gift Wrapping) algorithm
     * Time Complexity: O(nh) where h is number of hull vertices
     * Space Complexity: O(n)
     *
     * @param array<Point> $points Input points
     * @return array<Point> Points forming the convex hull
     * @throws InvalidArgumentException If fewer than 3 points provided
     */
    public static function jarvisMarch(array $points): array
    {
        if (count($points) < 3) {
            throw new InvalidArgumentException("At least 3 points required for convex hull");
        }

        $hull = [];

        // Find leftmost point
        $leftmost = $points[0];
        foreach ($points as $point) {
            if ($point->x < $leftmost->x) {
                $leftmost = $point;
            }
        }

        $current = $leftmost;

        do {
            $hull[] = $current;
            $next = $points[0];

            // Find most counter-clockwise point
            foreach ($points as $point) {
                if ($next === $current ||
                    GeometryUtils::orientation($current, $next, $point) ===
                    GeometryUtils::COUNTER_CLOCKWISE) {
                    $next = $point;
                }
            }

            $current = $next;
        } while ($current !== $leftmost);

        return $hull;
    }

    /**
     * Calculate the area of a convex hull
     *
     * @param array<Point> $hull Hull vertices
     * @return float Area of the hull
     */
    public static function area(array $hull): float
    {
        $n = count($hull);
        if ($n < 3) {
            return 0;
        }

        $area = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $hull[$i]->x * $hull[$j]->y;
            $area -= $hull[$j]->x * $hull[$i]->y;
        }

        return abs($area) / 2;
    }

    /**
     * Calculate the perimeter of a convex hull
     *
     * @param array<Point> $hull Hull vertices
     * @return float Perimeter of the hull
     */
    public static function perimeter(array $hull): float
    {
        $n = count($hull);
        if ($n < 2) {
            return 0;
        }

        $perimeter = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $perimeter += $hull[$i]->distanceTo($hull[$j]);
        }

        return $perimeter;
    }

    /**
     * Calculate the centroid (center of mass) of a convex hull
     *
     * @param array<Point> $hull Hull vertices
     * @return Point Centroid point
     */
    public static function centroid(array $hull): Point
    {
        $n = count($hull);
        if ($n === 0) {
            return new Point(0, 0);
        }

        $cx = 0;
        $cy = 0;
        $area = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $cross = $hull[$i]->x * $hull[$j]->y - $hull[$j]->x * $hull[$i]->y;

            $cx += ($hull[$i]->x + $hull[$j]->x) * $cross;
            $cy += ($hull[$i]->y + $hull[$j]->y) * $cross;
            $area += $cross;
        }

        $area /= 2;

        if ($area == 0) {
            return new Point(0, 0);
        }

        $cx /= (6 * $area);
        $cy /= (6 * $area);

        return new Point($cx, $cy);
    }

    /**
     * Check if a point is inside the convex hull
     *
     * @param array<Point> $hull Hull vertices
     * @param Point $point Point to check
     * @return bool True if point is inside hull
     */
    public static function containsPoint(array $hull, Point $point): bool
    {
        $n = count($hull);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;

            $orientation = GeometryUtils::orientation($hull[$i], $hull[$j], $point);

            // If point is on the right side of any edge, it's outside
            if ($orientation === GeometryUtils::CLOCKWISE) {
                return false;
            }
        }

        return true;
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Convex Hull Demo ===\n\n";

// Generate sample points
$points = [
    new Point(0, 3),
    new Point(2, 2),
    new Point(1, 1),
    new Point(2, 1),
    new Point(3, 0),
    new Point(0, 0),
    new Point(3, 3),
    new Point(1.5, 1.5), // Interior point
];

echo "1. Input Points:\n";
foreach ($points as $i => $point) {
    echo "   Point " . ($i + 1) . ": $point\n";
}

// Graham Scan
echo "\n2. Convex Hull (Graham Scan Algorithm):\n";
try {
    $hull = ConvexHull::grahamScan($points);

    echo "Hull vertices (" . count($hull) . "):\n";
    foreach ($hull as $i => $point) {
        echo "   " . ($i + 1) . ". $point\n";
    }

    $area = ConvexHull::area($hull);
    $perimeter = ConvexHull::perimeter($hull);
    $centroid = ConvexHull::centroid($hull);

    echo "\nHull Properties:\n";
    echo "   Area: " . number_format($area, 2) . " square units\n";
    echo "   Perimeter: " . number_format($perimeter, 2) . " units\n";
    echo "   Centroid: $centroid\n";
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Jarvis March
echo "\n3. Convex Hull (Jarvis March Algorithm):\n";
try {
    $hull2 = ConvexHull::jarvisMarch($points);

    echo "Hull vertices (" . count($hull2) . "):\n";
    foreach ($hull2 as $i => $point) {
        echo "   " . ($i + 1) . ". $point\n";
    }
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Point containment tests
echo "\n4. Point Containment Tests:\n";
$testPoints = [
    new Point(1.5, 1.5),  // Inside
    new Point(5, 5),      // Outside
    new Point(0, 0),      // On hull
];

foreach ($testPoints as $testPoint) {
    $inside = ConvexHull::containsPoint($hull, $testPoint);
    echo "   Point $testPoint: " . ($inside ? "Inside" : "Outside") . " hull\n";
}

// Real-world example: Bounding area for GPS coordinates
echo "\n5. Real-World Example - GPS Coordinate Bounding:\n";

class Location
{
    public function __construct(
        public string $name,
        public Point $coordinates
    ) {}
}

$locations = [
    new Location('Store A', new Point(37.7749, -122.4194)),
    new Location('Store B', new Point(37.7849, -122.4094)),
    new Location('Store C', new Point(37.7649, -122.4294)),
    new Location('Store D', new Point(37.7849, -122.4294)),
    new Location('Store E', new Point(37.7749, -122.4144)),
];

echo "Store locations:\n";
foreach ($locations as $location) {
    echo "   {$location->name}: ({$location->coordinates->x}, {$location->coordinates->y})\n";
}

$locationPoints = array_map(fn($loc) => $loc->coordinates, $locations);
$serviceArea = ConvexHull::grahamScan($locationPoints);

echo "\nService area boundary:\n";
foreach ($serviceArea as $i => $point) {
    echo "   " . ($i + 1) . ". $point\n";
}

$serviceAreaSize = ConvexHull::area($serviceArea);
echo "\nTotal service area: " . number_format($serviceAreaSize, 4) . " square degrees\n";

// Check if a customer location is within service area
$customerLocation = new Point(37.7750, -122.4200);
$inServiceArea = ConvexHull::containsPoint($serviceArea, $customerLocation);

echo "Customer location $customerLocation: " .
     ($inServiceArea ? "Within" : "Outside") . " service area\n";

// Performance comparison
echo "\n6. Algorithm Performance Comparison:\n";

// Generate random points
$numPoints = 100;
$randomPoints = [];
for ($i = 0; $i < $numPoints; $i++) {
    $randomPoints[] = new Point(
        rand(0, 1000) / 10,
        rand(0, 1000) / 10
    );
}

echo "Testing with $numPoints random points...\n";

// Graham Scan timing
$start = microtime(true);
$hull1 = ConvexHull::grahamScan($randomPoints);
$grahamTime = microtime(true) - $start;

echo "   Graham Scan: " . number_format($grahamTime * 1000, 3) . " ms " .
     "(" . count($hull1) . " hull points)\n";

// Jarvis March timing
$start = microtime(true);
$hull2 = ConvexHull::jarvisMarch($randomPoints);
$jarvisTime = microtime(true) - $start;

echo "   Jarvis March: " . number_format($jarvisTime * 1000, 3) . " ms " .
     "(" . count($hull2) . " hull points)\n";

$faster = $grahamTime < $jarvisTime ? "Graham Scan" : "Jarvis March";
$ratio = max($grahamTime, $jarvisTime) / min($grahamTime, $jarvisTime);
echo "   $faster was " . number_format($ratio, 2) . "x faster\n";

echo "\n=== Demo Complete ===\n";
