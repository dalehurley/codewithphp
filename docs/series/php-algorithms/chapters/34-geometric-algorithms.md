---
title: "34: Geometric Algorithms"
description: "Computational geometry algorithms for graphics, GIS, and robotics including convex hull, collision detection, and spatial queries"
series: "php-algorithms"
chapter: 34
order: 34
difficulty: "Advanced"
prerequisites: []
---
![Geometric Algorithms](/images/php-algorithms/chapter-34-geometric-algorithms-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 34</span>
</div>

# 34: Geometric Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Enter the fascinating world of computational geometry where algorithms solve spatial problems for graphics, games, robotics, and GIS applications. These techniques power everything from collision detection in games to route planning in navigation apps. Computational geometry algorithms transform abstract geometric problems into efficient, practical solutions that run in real-time applications.

While basic geometric operations work well for simple tasks, real-world applications require sophisticated algorithms that can handle complex spatial relationships, process thousands of geometric objects efficiently, and solve problems like finding the smallest enclosing shape or detecting collisions between moving objects. You'll learn how game engines detect collisions between complex shapes, how mapping applications determine if a location is inside a geographic region, and how robotics systems plan paths avoiding obstacles.

By the end of this chapter, you'll have implemented fundamental geometric algorithms including convex hull construction, line segment intersection detection, point-in-polygon tests, and closest pair finding. These techniques form the foundation of modern graphics, mapping, and robotics systems.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Basic understanding of geometry: points, lines, angles, and coordinate systems
- Familiarity with vector mathematics: dot product, cross product, and vector operations
- Comfort with 2D coordinate geometry and Cartesian coordinates
- Understanding of data structures: arrays, sets, and spatial organization concepts

**Estimated Time**: ~50 minutes

**Verify your setup:**

```bash
php --version
```

## What You'll Build

By the end of this chapter, you will have created:

- Point and Vector classes with fundamental geometric operations
- GeometryUtils class with orientation tests and segment operations
- LineSegment class with intersection detection and computation
- ConvexHull implementation using both Graham Scan and Jarvis March algorithms
- Polygon class with point-in-polygon testing using ray casting
- ClosestPair algorithm using divide-and-conquer approach
- PolygonTriangulator using ear clipping algorithm
- MinimumEnclosingCircle using Welzl's algorithm
- Quadtree spatial index for efficient range queries
- VoronoiDiagram for spatial partitioning and nearest neighbor queries
- Geofence class for geographic boundary detection
- CollisionDetector for various shape collision tests
- Complete geometric algorithm library ready for real-world applications

## Objectives

- Master fundamental geometric primitives: points, vectors, lines, and polygons
- Implement collision detection algorithms for game development and physics engines
- Build convex hull algorithms for shape analysis and pattern recognition
- Apply spatial query techniques for efficient geometric operations
- Solve real-world problems in mapping, computer graphics, and robotics
- Understand time and space complexity of geometric algorithms
- Handle edge cases and numerical precision issues in geometric computations

## Quick Start

Here's a quick example to get you started with geometric algorithms. This demonstrates basic point operations and collision detection:

```php
# filename: quick-start.php
<?php

declare(strict_types=1);

class Point {
    public function __construct(
        public float $x,
        public float $y
    ) {}
    
    public function distanceTo(Point $other): float {
        $dx = $this->x - $other->x;
        $dy = $this->y - $other->y;
        return sqrt($dx * $dx + $dy * $dy);
    }
}

// Check if two circles collide
function circlesCollide(Point $c1, float $r1, Point $c2, float $r2): bool {
    return $c1->distanceTo($c2) <= $r1 + $r2;
}

$circle1 = new Point(0, 0);
$circle2 = new Point(3, 4);

echo circlesCollide($circle1, 2.5, $circle2, 2.5) ? "Collision!" : "No collision";
// Output: Collision! (distance is 5, sum of radii is 5)
```

This demonstrates the foundation: points represent positions in 2D space, and distance calculations enable collision detection. Now let's build a complete geometric algorithm library.

## Basic Geometric Primitives

All geometric algorithms build on fundamental primitives: points and vectors. Points represent positions in space, while vectors represent directions and magnitudes. These simple building blocks enable complex spatial operations.

### Point and Vector Operations

The `Point` class represents a 2D coordinate, while `Vector` extends `Point` to add vector-specific operations like dot product and cross product. These operations are essential for determining orientations, distances, and relationships between geometric objects.

```php
# filename: point-vector.php
<?php

declare(strict_types=1);

class Point {
    public function __construct(
        public float $x,
        public float $y
    ) {}

    public function distanceTo(Point $other): float {
        $dx = $this->x - $other->x;
        $dy = $this->y - $other->y;
        return sqrt($dx * $dx + $dy * $dy);
    }

    public function subtract(Point $other): Point {
        return new Point($this->x - $other->x, $this->y - $other->y);
    }

    public function add(Point $other): Point {
        return new Point($this->x + $other->x, $this->y + $other->y);
    }

    public function scale(float $factor): Point {
        return new Point($this->x * $factor, $this->y * $factor);
    }

    public function __toString(): string {
        return "({$this->x}, {$this->y})";
    }
}

class Vector extends Point {
    public function dot(Vector $other): float {
        return $this->x * $other->x + $this->y * $other->y;
    }

    public function cross(Vector $other): float {
        // Returns z-component of 3D cross product (x, y, 0) × (x', y', 0)
        return $this->x * $other->y - $this->y * $other->x;
    }

    public function magnitude(): float {
        return sqrt($this->x * $this->x + $this->y * $this->y);
    }

    public function normalize(): Vector {
        $mag = $this->magnitude();
        if ($mag == 0) {
            return new Vector(0, 0);
        }
        return new Vector($this->x / $mag, $this->y / $mag);
    }

    public function angle(): float {
        return atan2($this->y, $this->x);
    }
}

// Usage
$p1 = new Point(1, 2);
$p2 = new Point(4, 6);

echo "Distance: " . $p1->distanceTo($p2) . "\n";  // 5

$v1 = new Vector(3, 4);
$v2 = new Vector(1, 2);

echo "Dot product: " . $v1->dot($v2) . "\n";      // 11
echo "Cross product: " . $v1->cross($v2) . "\n";  // 2
echo "Magnitude: " . $v1->magnitude() . "\n";     // 5
```

**Why It Works**: The distance formula uses the Pythagorean theorem: distance = √(dx² + dy²). The dot product measures how aligned two vectors are (useful for projections), while the cross product in 2D gives the signed area of the parallelogram formed by two vectors, which indicates relative orientation. The magnitude is the vector's length, and normalization creates a unit vector pointing in the same direction.

### Orientation Test

The orientation test is fundamental to many geometric algorithms. It determines whether three points make a counter-clockwise turn, clockwise turn, or are collinear by computing the cross product of vectors formed by the points. This simple test enables line intersection detection, convex hull construction, and polygon operations.

```php
# filename: orientation-test.php
<?php

declare(strict_types=1);

class GeometryUtils {
    const COLLINEAR = 0;
    const CLOCKWISE = 1;
    const COUNTER_CLOCKWISE = 2;

    public static function orientation(Point $p, Point $q, Point $r): int {
        // Calculate cross product of vectors (q-p) and (r-q)
        $val = ($q->y - $p->y) * ($r->x - $q->x) - ($q->x - $p->x) * ($r->y - $q->y);

        if (abs($val) < 1e-10) {
            return self::COLLINEAR;
        }

        return ($val > 0) ? self::CLOCKWISE : self::COUNTER_CLOCKWISE;
    }

    public static function onSegment(Point $p, Point $q, Point $r): bool {
        // Check if point q lies on segment pr (given p, q, r are collinear)
        return $q->x <= max($p->x, $r->x) && $q->x >= min($p->x, $r->x) &&
               $q->y <= max($p->y, $r->y) && $q->y >= min($p->y, $r->y);
    }
}

// Usage
$p1 = new Point(0, 0);
$p2 = new Point(4, 4);
$p3 = new Point(1, 2);

$orient = GeometryUtils::orientation($p1, $p2, $p3);
echo $orient === GeometryUtils::COUNTER_CLOCKWISE ? "CCW" : "CW";
```

**Why It Works**: The orientation test calculates the cross product of vectors (q-p) and (r-q). If the result is positive, the points turn clockwise; if negative, counter-clockwise; if zero (within epsilon), they're collinear. The epsilon threshold (`1e-10`) handles floating-point precision issues. The `onSegment` function checks if a collinear point lies between two others by comparing coordinates.

## Line Segment Intersection

Line segment intersection is crucial for collision detection, path planning, and geometric queries. We'll implement two approaches: a simple O(1) test for two segments, and a sweep line algorithm for finding all intersections among multiple segments efficiently.

### Two-Segment Intersection

This algorithm uses orientation tests to determine if two line segments intersect. It handles all edge cases including parallel segments, collinear segments, and segments that share endpoints.

```php
# filename: line-segment-intersection.php
<?php

declare(strict_types=1);

class LineSegment {
    public function __construct(
        public Point $start,
        public Point $end
    ) {}

    public function intersects(LineSegment $other): bool {
        $o1 = GeometryUtils::orientation($this->start, $this->end, $other->start);
        $o2 = GeometryUtils::orientation($this->start, $this->end, $other->end);
        $o3 = GeometryUtils::orientation($other->start, $other->end, $this->start);
        $o4 = GeometryUtils::orientation($other->start, $other->end, $this->end);

        // General case
        if ($o1 !== $o2 && $o3 !== $o4) {
            return true;
        }

        // Special cases: collinear points
        if ($o1 === GeometryUtils::COLLINEAR && GeometryUtils::onSegment($this->start, $other->start, $this->end)) {
            return true;
        }

        if ($o2 === GeometryUtils::COLLINEAR && GeometryUtils::onSegment($this->start, $other->end, $this->end)) {
            return true;
        }

        if ($o3 === GeometryUtils::COLLINEAR && GeometryUtils::onSegment($other->start, $this->start, $other->end)) {
            return true;
        }

        if ($o4 === GeometryUtils::COLLINEAR && GeometryUtils::onSegment($other->start, $this->end, $other->end)) {
            return true;
        }

        return false;
    }

    public function intersectionPoint(LineSegment $other): ?Point {
        $x1 = $this->start->x;
        $y1 = $this->start->y;
        $x2 = $this->end->x;
        $y2 = $this->end->y;

        $x3 = $other->start->x;
        $y3 = $other->start->y;
        $x4 = $other->end->x;
        $y4 = $other->end->y;

        $denom = ($x1 - $x2) * ($y3 - $y4) - ($y1 - $y2) * ($x3 - $x4);

        if (abs($denom) < 1e-10) {
            return null;  // Parallel or coincident
        }

        $t = (($x1 - $x3) * ($y3 - $y4) - ($y1 - $y3) * ($x3 - $x4)) / $denom;
        $u = -(($x1 - $x2) * ($y1 - $y3) - ($y1 - $y2) * ($x1 - $x3)) / $denom;

        if ($t >= 0 && $t <= 1 && $u >= 0 && $u <= 1) {
            $x = $x1 + $t * ($x2 - $x1);
            $y = $y1 + $t * ($y2 - $y1);
            return new Point($x, $y);
        }

        return null;  // No intersection
    }

    public function length(): float {
        return $this->start->distanceTo($this->end);
    }
}

// Usage
$seg1 = new LineSegment(new Point(1, 1), new Point(10, 1));
$seg2 = new LineSegment(new Point(1, 2), new Point(10, 2));

if ($seg1->intersects($seg2)) {
    $intersection = $seg1->intersectionPoint($seg2);
    echo "Intersection at: " . $intersection . "\n";
} else {
    echo "No intersection\n";
}
```

**Why It Works**: Two segments intersect if their endpoints are on opposite sides of each other's lines. The algorithm checks four orientations: each endpoint relative to the other segment. If orientations differ, the segments cross. For collinear cases, it checks if endpoints lie on the segments. The intersection point calculation uses parametric line equations, solving for the parameter values where both segments meet.

**Time Complexity**: O(1) for two segments
**Space Complexity**: O(1)

### Sweep Line Algorithm for Multiple Segments

For finding all intersections among multiple segments, a sweep line algorithm processes segments as a vertical line sweeps from left to right. This reduces the problem from O(n²) pairwise checks to O(n log n + k) where k is the number of intersections.

```php
# filename: sweep-line-intersection.php
<?php

declare(strict_types=1);

class SegmentIntersectionDetector {
    private array $segments = [];
    private array $events = [];

    public function addSegment(LineSegment $segment, $id = null): void {
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

    public function findIntersections(): array {
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
                    if ($this->segments[$event['segment']]->intersects($this->segments[$activeId])) {
                        $point = $this->segments[$event['segment']]->intersectionPoint($this->segments[$activeId]);

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
}

// Usage
$detector = new SegmentIntersectionDetector();

$detector->addSegment(new LineSegment(new Point(0, 0), new Point(10, 10)), 'seg1');
$detector->addSegment(new LineSegment(new Point(0, 10), new Point(10, 0)), 'seg2');
$detector->addSegment(new LineSegment(new Point(5, 0), new Point(5, 10)), 'seg3');

$intersections = $detector->findIntersections();

foreach ($intersections as $intersection) {
    echo "Intersection at {$intersection['point']} between segments: ";
    echo implode(', ', $intersection['segments']) . "\n";
}
```

**Why It Works**: The sweep line algorithm sorts segment endpoints by x-coordinate, then processes them left-to-right. When a segment starts, it's added to the active set and checked against all other active segments. When it ends, it's removed. This ensures we only check segments that could possibly intersect at the current x-position, dramatically reducing comparisons.

**Time Complexity**: O(n log n + k) where k is intersections
**Space Complexity**: O(n)

## Convex Hull

A convex hull is the smallest convex polygon that contains all given points. It's essential for shape analysis, collision detection, and pattern recognition. We'll implement two algorithms: Graham Scan (O(n log n)) and Jarvis March (O(nh) where h is hull size).

### Graham Scan Algorithm

Graham Scan sorts points by polar angle from the bottommost point, then builds the hull by removing points that create clockwise turns. It's efficient and handles all edge cases including collinear points.

```php
# filename: convex-hull.php
<?php

declare(strict_types=1);

class ConvexHull {
    public static function grahamScan(array $points): array {
        if (count($points) < 3) {
            return $points;
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

                if (GeometryUtils::orientation($nextTop, $top, $points[$i]) !== GeometryUtils::CLOCKWISE) {
                    $hull[] = $top;
                    break;
                }
            }

            $hull[] = $points[$i];
        }

        return $hull;
    }

    public static function jarvisMarch(array $points): array {
        if (count($points) < 3) {
            return $points;
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
                    GeometryUtils::orientation($current, $next, $point) === GeometryUtils::COUNTER_CLOCKWISE) {
                    $next = $point;
                }
            }

            $current = $next;
        } while ($current !== $leftmost);

        return $hull;
    }

    public static function area(array $hull): float {
        $n = count($hull);
        $area = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $hull[$i]->x * $hull[$j]->y;
            $area -= $hull[$j]->x * $hull[$i]->y;
        }

        return abs($area) / 2;
    }

    public static function perimeter(array $hull): float {
        $n = count($hull);
        $perimeter = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $perimeter += $hull[$i]->distanceTo($hull[$j]);
        }

        return $perimeter;
    }
}

// Usage
$points = [
    new Point(0, 3),
    new Point(2, 2),
    new Point(1, 1),
    new Point(2, 1),
    new Point(3, 0),
    new Point(0, 0),
    new Point(3, 3),
];

$hull = ConvexHull::grahamScan($points);

echo "Convex Hull points:\n";
foreach ($hull as $point) {
    echo $point . "\n";
}

echo "Area: " . ConvexHull::area($hull) . "\n";
echo "Perimeter: " . ConvexHull::perimeter($hull) . "\n";
```

**Why It Works**: Graham Scan starts with the bottommost point (guaranteed to be on the hull), sorts all points by polar angle, then processes them in order. For each point, it removes previous hull points that would create a clockwise turn, ensuring the hull stays convex. The area calculation uses the shoelace formula, summing signed areas of triangles formed with the origin. Jarvis March (gift wrapping) finds the hull by repeatedly finding the most counter-clockwise point, wrapping around the set like a gift.

**Time Complexity**:
- Graham Scan: O(n log n) due to sorting
- Jarvis March: O(nh) where h is hull size (better for small hulls)
**Space Complexity**: O(n)

## Point in Polygon

Determining if a point lies inside a polygon is fundamental for geofencing, hit testing in graphics, and spatial queries. The ray casting algorithm shoots a ray from the point to infinity and counts intersections with polygon edges. Odd intersections mean inside, even means outside.

### Ray Casting Algorithm

The algorithm casts a horizontal ray from the point and counts edge crossings. It handles all edge cases including points on boundaries and complex polygons with holes.

```php
# filename: point-in-polygon.php
<?php

declare(strict_types=1);

class Polygon {
    public function __construct(
        public array $vertices
    ) {}

    public function contains(Point $point): bool {
        $n = count($this->vertices);
        $inside = false;

        $p1 = $this->vertices[0];

        for ($i = 1; $i <= $n; $i++) {
            $p2 = $this->vertices[$i % $n];

            if ($point->y > min($p1->y, $p2->y)) {
                if ($point->y <= max($p1->y, $p2->y)) {
                    if ($point->x <= max($p1->x, $p2->x)) {
                        if ($p1->y !== $p2->y) {
                            $xinters = ($point->y - $p1->y) * ($p2->x - $p1->x) / ($p2->y - $p1->y) + $p1->x;

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

    public function onBoundary(Point $point): bool {
        $n = count($this->vertices);

        for ($i = 0; $i < $n; $i++) {
            $p1 = $this->vertices[$i];
            $p2 = $this->vertices[($i + 1) % $n];

            $segment = new LineSegment($p1, $p2);

            if (GeometryUtils::orientation($p1, $p2, $point) === GeometryUtils::COLLINEAR &&
                GeometryUtils::onSegment($p1, $point, $p2)) {
                return true;
            }
        }

        return false;
    }

    public function area(): float {
        $n = count($this->vertices);
        $area = 0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $this->vertices[$i]->x * $this->vertices[$j]->y;
            $area -= $this->vertices[$j]->x * $this->vertices[$i]->y;
        }

        return abs($area) / 2;
    }

    public function centroid(): Point {
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
        $cx /= (6 * $area);
        $cy /= (6 * $area);

        return new Point($cx, $cy);
    }
}

// Usage
$polygon = new Polygon([
    new Point(0, 0),
    new Point(4, 0),
    new Point(4, 4),
    new Point(0, 4),
]);

$testPoint = new Point(2, 2);
echo $polygon->contains($testPoint) ? "Inside" : "Outside";  // Inside

echo "Area: " . $polygon->area() . "\n";  // 16
echo "Centroid: " . $polygon->centroid() . "\n";  // (2, 2)
```

**Why It Works**: The ray casting algorithm shoots a horizontal ray from the point and counts how many polygon edges it crosses. Each crossing flips the inside/outside state. The algorithm handles horizontal edges and boundary cases carefully. The area calculation uses the shoelace formula, and the centroid is the weighted average of triangle centroids, weighted by signed area.

**Time Complexity**: O(n) where n is number of vertices
**Space Complexity**: O(1)

## Closest Pair of Points

Finding the closest pair of points among n points is a classic divide-and-conquer problem. A naive O(n²) approach compares all pairs, but we can achieve O(n log² n) or even O(n log n) with careful implementation.

### Divide and Conquer Approach

The algorithm divides points by x-coordinate, recursively finds closest pairs in each half, then checks points near the dividing line. The key insight is that we only need to check points within the minimum distance found so far.

```php
# filename: closest-pair.php
<?php

declare(strict_types=1);

class ClosestPair {
    public static function find(array $points): array {
        // Sort by x-coordinate
        usort($points, fn($a, $b) => $a->x <=> $b->x);

        return self::findRecursive($points);
    }

    private static function findRecursive(array $points): array {
        $n = count($points);

        // Base case: brute force for small inputs
        if ($n <= 3) {
            return self::bruteForce($points);
        }

        // Divide
        $mid = (int)($n / 2);
        $midPoint = $points[$mid];

        $left = array_slice($points, 0, $mid);
        $right = array_slice($points, $mid);

        // Conquer
        $leftPair = self::findRecursive($left);
        $rightPair = self::findRecursive($right);

        // Find minimum distance
        $minDist = min($leftPair['distance'], $rightPair['distance']);
        $bestPair = $leftPair['distance'] < $rightPair['distance'] ? $leftPair : $rightPair;

        // Find points in strip
        $strip = [];
        foreach ($points as $point) {
            if (abs($point->x - $midPoint->x) < $minDist) {
                $strip[] = $point;
            }
        }

        // Sort strip by y-coordinate
        usort($strip, fn($a, $b) => $a->y <=> $b->y);

        // Find closest points in strip
        $stripPair = self::stripClosest($strip, $minDist);

        if ($stripPair !== null && $stripPair['distance'] < $bestPair['distance']) {
            return $stripPair;
        }

        return $bestPair;
    }

    private static function bruteForce(array $points): array {
        $n = count($points);
        $minDist = PHP_FLOAT_MAX;
        $bestPair = null;

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $dist = $points[$i]->distanceTo($points[$j]);

                if ($dist < $minDist) {
                    $minDist = $dist;
                    $bestPair = [
                        'point1' => $points[$i],
                        'point2' => $points[$j],
                        'distance' => $dist
                    ];
                }
            }
        }

        return $bestPair;
    }

    private static function stripClosest(array $strip, float $minDist): ?array {
        $n = count($strip);
        $best = null;

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n && ($strip[$j]->y - $strip[$i]->y) < $minDist; $j++) {
                $dist = $strip[$i]->distanceTo($strip[$j]);

                if ($dist < $minDist) {
                    $minDist = $dist;
                    $best = [
                        'point1' => $strip[$i],
                        'point2' => $strip[$j],
                        'distance' => $dist
                    ];
                }
            }
        }

        return $best;
    }
}

// Usage
$points = [
    new Point(2, 3),
    new Point(12, 30),
    new Point(40, 50),
    new Point(5, 1),
    new Point(12, 10),
    new Point(3, 4),
];

$result = ClosestPair::find($points);

echo "Closest pair:\n";
echo "Point 1: {$result['point1']}\n";
echo "Point 2: {$result['point2']}\n";
echo "Distance: {$result['distance']}\n";
```

**Why It Works**: The divide-and-conquer approach splits points by x-coordinate, recursively solves each half, then merges results. The critical optimization is the "strip" - only points within the minimum distance of the dividing line need checking. Within the strip, points are sorted by y-coordinate, and we only check points within the minimum distance vertically. This limits strip comparisons to a constant per point, achieving O(n log n) overall.

**Time Complexity**: O(n log² n) - can be optimized to O(n log n) with better strip handling
**Space Complexity**: O(n)

## Real-World Applications

Geometric algorithms power many real-world systems. Let's implement practical applications: geofencing for location-based services and collision detection for games and physics engines.

### 1. Geofencing

Geofencing determines if a location (like a GPS coordinate) is inside a geographic boundary. This is used in location-based apps, delivery services, and security systems.

```php
# filename: geofence.php
<?php

declare(strict_types=1);

class Geofence {
    private Polygon $boundary;
    private string $name;

    public function __construct(string $name, array $coordinates) {
        $this->name = $name;
        $points = array_map(fn($coord) => new Point($coord[0], $coord[1]), $coordinates);
        $this->boundary = new Polygon($points);
    }

    public function isInside(float $lat, float $lng): bool {
        return $this->boundary->contains(new Point($lat, $lng));
    }

    public function distanceToBoundary(float $lat, float $lng): float {
        $point = new Point($lat, $lng);
        $minDist = PHP_FLOAT_MAX;

        $vertices = $this->boundary->vertices;
        $n = count($vertices);

        for ($i = 0; $i < $n; $i++) {
            $segment = new LineSegment($vertices[$i], $vertices[($i + 1) % $n]);
            $dist = self::pointToSegmentDistance($point, $segment);
            $minDist = min($minDist, $dist);
        }

        return $minDist;
    }

    private static function pointToSegmentDistance(Point $p, LineSegment $seg): float {
        $dx = $seg->end->x - $seg->start->x;
        $dy = $seg->end->y - $seg->start->y;

        if ($dx === 0.0 && $dy === 0.0) {
            return $p->distanceTo($seg->start);
        }

        $t = (($p->x - $seg->start->x) * $dx + ($p->y - $seg->start->y) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0, min(1, $t));

        $nearest = new Point(
            $seg->start->x + $t * $dx,
            $seg->start->y + $t * $dy
        );

        return $p->distanceTo($nearest);
    }
}

// Usage
$fence = new Geofence('Downtown', [
    [37.7749, -122.4194],
    [37.7849, -122.4194],
    [37.7849, -122.4094],
    [37.7749, -122.4094],
]);

$userLat = 37.7799;
$userLng = -122.4144;

if ($fence->isInside($userLat, $userLng)) {
    echo "User is inside the geofence\n";
} else {
    $distance = $fence->distanceToBoundary($userLat, $userLng);
    echo "User is outside, distance to boundary: $distance\n";
}
```

**Why It Works**: Geofencing uses the point-in-polygon test to check if coordinates are inside the boundary. The distance calculation finds the minimum distance to any edge of the polygon by projecting the point onto each edge segment and measuring the distance. This enables applications to trigger actions when users enter or exit defined regions.

### 2. Collision Detection

Collision detection determines if geometric shapes overlap, essential for games, physics simulations, and robotics. Different shapes require different algorithms, from simple distance checks for circles to complex polygon intersection tests.

```php
# filename: collision-detection.php
<?php

declare(strict_types=1);

class CollisionDetector {
    public function circleCircle(Point $c1, float $r1, Point $c2, float $r2): bool {
        $distance = $c1->distanceTo($c2);
        return $distance <= $r1 + $r2;
    }

    public function circleRectangle(Point $center, float $radius, Point $rectMin, Point $rectMax): bool {
        // Find closest point on rectangle to circle center
        $closestX = max($rectMin->x, min($center->x, $rectMax->x));
        $closestY = max($rectMin->y, min($center->y, $rectMax->y));

        $closest = new Point($closestX, $closestY);

        return $center->distanceTo($closest) <= $radius;
    }

    public function rectangleRectangle(Point $r1Min, Point $r1Max, Point $r2Min, Point $r2Max): bool {
        return !($r1Max->x < $r2Min->x || $r2Max->x < $r1Min->x ||
                 $r1Max->y < $r2Min->y || $r2Max->y < $r1Min->y);
    }

    public function polygonPolygon(Polygon $poly1, Polygon $poly2): bool {
        // Simplified SAT (Separating Axis Theorem)
        // Check if any edge creates a separating axis

        $vertices1 = $poly1->vertices;
        $vertices2 = $poly2->vertices;

        // Check poly1 edges
        foreach ($this->getEdges($vertices1) as $edge) {
            $axis = $this->getPerpendicularAxis($edge);

            if ($this->isSeparatingAxis($axis, $vertices1, $vertices2)) {
                return false;  // Separating axis found, no collision
            }
        }

        // Check poly2 edges
        foreach ($this->getEdges($vertices2) as $edge) {
            $axis = $this->getPerpendicularAxis($edge);

            if ($this->isSeparatingAxis($axis, $vertices1, $vertices2)) {
                return false;
            }
        }

        return true;  // No separating axis found, collision detected
    }

    private function getEdges(array $vertices): array {
        $edges = [];
        $n = count($vertices);

        for ($i = 0; $i < $n; $i++) {
            $edges[] = new LineSegment($vertices[$i], $vertices[($i + 1) % $n]);
        }

        return $edges;
    }

    private function getPerpendicularAxis(LineSegment $edge): Vector {
        $dx = $edge->end->x - $edge->start->x;
        $dy = $edge->end->y - $edge->start->y;
        return new Vector(-$dy, $dx);
    }

    private function isSeparatingAxis(Vector $axis, array $vertices1, array $vertices2): bool {
        $proj1 = $this->projectPolygon($axis, $vertices1);
        $proj2 = $this->projectPolygon($axis, $vertices2);

        return $proj1['max'] < $proj2['min'] || $proj2['max'] < $proj1['min'];
    }

    private function projectPolygon(Vector $axis, array $vertices): array {
        $min = $max = $axis->dot(new Vector($vertices[0]->x, $vertices[0]->y));

        foreach ($vertices as $vertex) {
            $projection = $axis->dot(new Vector($vertex->x, $vertex->y));
            $min = min($min, $projection);
            $max = max($max, $projection);
        }

        return ['min' => $min, 'max' => $max];
    }
}

// Usage
$detector = new CollisionDetector();

// Circle-circle collision
$collision = $detector->circleCircle(
    new Point(0, 0), 5,
    new Point(8, 0), 5
);

echo $collision ? "Collision detected" : "No collision";
```

**Why It Works**: Circle collision uses simple distance comparison. Rectangle collision uses axis-aligned bounding box (AABB) tests - if rectangles don't overlap on any axis, they don't collide. Polygon collision uses the Separating Axis Theorem (SAT): if we can find any axis where the projections don't overlap, the polygons don't collide. We check perpendicular axes to each edge, projecting all vertices onto each axis and checking for gaps.

## Polygon Triangulation

Triangulating a polygon means dividing it into triangles. This is essential for graphics rendering, physics engines, and computational geometry. The ear clipping algorithm is a simple, intuitive method that works for any simple polygon.

### Ear Clipping Algorithm

The algorithm repeatedly finds and removes "ears" - triangles formed by three consecutive vertices where the triangle doesn't contain any other polygon vertices. Each ear removal reduces the polygon size until only a triangle remains.

```php
# filename: polygon-triangulation.php
<?php

declare(strict_types=1);

class PolygonTriangulator {
    public static function triangulate(Polygon $polygon): array {
        $vertices = $polygon->vertices;
        $n = count($vertices);
        
        if ($n < 3) {
            return [];
        }
        
        if ($n === 3) {
            return [[$vertices[0], $vertices[1], $vertices[2]]];
        }
        
        $triangles = [];
        $indices = range(0, $n - 1);
        
        // Ensure counter-clockwise orientation
        if (!self::isCounterClockwise($vertices)) {
            $indices = array_reverse($indices);
        }
        
        while (count($indices) > 3) {
            $earFound = false;
            
            for ($i = 0; $i < count($indices); $i++) {
                $prev = $indices[($i - 1 + count($indices)) % count($indices)];
                $curr = $indices[$i];
                $next = $indices[($i + 1) % count($indices)];
                
                if (self::isEar($vertices, $prev, $curr, $next, $indices)) {
                    // Add triangle
                    $triangles[] = [
                        $vertices[$prev],
                        $vertices[$curr],
                        $vertices[$next]
                    ];
                    
                    // Remove ear vertex
                    array_splice($indices, $i, 1);
                    $earFound = true;
                    break;
                }
            }
            
            if (!$earFound) {
                throw new \RuntimeException("Failed to triangulate polygon");
            }
        }
        
        // Add final triangle
        $triangles[] = [
            $vertices[$indices[0]],
            $vertices[$indices[1]],
            $vertices[$indices[2]]
        ];
        
        return $triangles;
    }
    
    private static function isCounterClockwise(array $vertices): bool {
        $area = 0;
        $n = count($vertices);
        
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += ($vertices[$j]->x - $vertices[$i]->x) * 
                     ($vertices[$j]->y + $vertices[$i]->y);
        }
        
        return $area < 0;
    }
    
    private static function isEar(array $vertices, int $prev, int $curr, int $next, array $indices): bool {
        $p1 = $vertices[$prev];
        $p2 = $vertices[$curr];
        $p3 = $vertices[$next];
        
        // Check if triangle is convex (ear)
        $orientation = GeometryUtils::orientation($p1, $p2, $p3);
        if ($orientation !== GeometryUtils::COUNTER_CLOCKWISE) {
            return false;
        }
        
        // Check if any other vertex is inside the triangle
        foreach ($indices as $idx) {
            if ($idx === $prev || $idx === $curr || $idx === $next) {
                continue;
            }
            
            $point = $vertices[$idx];
            if (self::pointInTriangle($point, $p1, $p2, $p3)) {
                return false;
            }
        }
        
        return true;
    }
    
    private static function pointInTriangle(Point $p, Point $a, Point $b, Point $c): bool {
        $d1 = GeometryUtils::orientation($p, $a, $b);
        $d2 = GeometryUtils::orientation($p, $b, $c);
        $d3 = GeometryUtils::orientation($p, $c, $a);
        
        $hasNeg = ($d1 < 0) || ($d2 < 0) || ($d3 < 0);
        $hasPos = ($d1 > 0) || ($d2 > 0) || ($d3 > 0);
        
        return !($hasNeg && $hasPos);
    }
}

// Usage
$polygon = new Polygon([
    new Point(0, 0),
    new Point(4, 0),
    new Point(4, 4),
    new Point(2, 6),
    new Point(0, 4),
]);

$triangles = PolygonTriangulator::triangulate($polygon);

echo "Triangulated into " . count($triangles) . " triangles\n";
foreach ($triangles as $i => $triangle) {
    echo "Triangle " . ($i + 1) . ": ";
    foreach ($triangle as $point) {
        echo $point . " ";
    }
    echo "\n";
}
```

**Why It Works**: Every simple polygon has at least two ears (triangles that can be clipped). The algorithm finds an ear, removes it, and repeats. An ear is identified by checking if three consecutive vertices form a convex triangle (counter-clockwise orientation) and no other vertices lie inside it. The algorithm terminates when only three vertices remain, forming the final triangle.

**Time Complexity**: O(n²) worst case, O(n) average case
**Space Complexity**: O(n)

## Minimum Enclosing Circle

Finding the smallest circle that contains all given points is useful for collision detection, bounding volumes, and spatial queries. We'll implement Welzl's algorithm, which finds the minimum enclosing circle in expected O(n) time.

### Welzl's Algorithm

The algorithm recursively finds the smallest circle by considering points one at a time. If a point is outside the current circle, it must be on the boundary of the new circle.

```php
# filename: minimum-enclosing-circle.php
<?php

declare(strict_types=1);

class MinimumEnclosingCircle {
    public static function find(array $points): array {
        if (empty($points)) {
            return ['center' => new Point(0, 0), 'radius' => 0];
        }
        
        if (count($points) === 1) {
            return ['center' => $points[0], 'radius' => 0];
        }
        
        // Shuffle for better average performance
        $shuffled = $points;
        shuffle($shuffled);
        
        return self::welzl($shuffled, []);
    }
    
    private static function welzl(array $points, array $boundary): array {
        if (empty($points) || count($boundary) === 3) {
            return self::circleFromBoundary($boundary);
        }
        
        $p = array_pop($points);
        $circle = self::welzl($points, $boundary);
        
        if (!self::pointInCircle($p, $circle['center'], $circle['radius'])) {
            $boundary[] = $p;
            $circle = self::welzl($points, $boundary);
        }
        
        return $circle;
    }
    
    private static function circleFromBoundary(array $boundary): array {
        if (empty($boundary)) {
            return ['center' => new Point(0, 0), 'radius' => 0];
        }
        
        if (count($boundary) === 1) {
            return ['center' => $boundary[0], 'radius' => 0];
        }
        
        if (count($boundary) === 2) {
            $center = new Point(
                ($boundary[0]->x + $boundary[1]->x) / 2,
                ($boundary[0]->y + $boundary[1]->y) / 2
            );
            $radius = $boundary[0]->distanceTo($boundary[1]) / 2;
            return ['center' => $center, 'radius' => $radius];
        }
        
        // Three points - circumcircle
        return self::circumcircle($boundary[0], $boundary[1], $boundary[2]);
    }
    
    private static function circumcircle(Point $a, Point $b, Point $c): array {
        $d = 2 * (($a->x - $c->x) * ($b->y - $c->y) - ($b->x - $c->x) * ($a->y - $c->y));
        
        if (abs($d) < 1e-10) {
            // Points are collinear, use bounding circle
            $minX = min($a->x, $b->x, $c->x);
            $maxX = max($a->x, $b->x, $c->x);
            $minY = min($a->y, $b->y, $c->y);
            $maxY = max($a->y, $b->y, $c->y);
            
            $center = new Point(($minX + $maxX) / 2, ($minY + $maxY) / 2);
            $radius = max(
                $center->distanceTo($a),
                $center->distanceTo($b),
                $center->distanceTo($c)
            );
            return ['center' => $center, 'radius' => $radius];
        }
        
        $ux = (($a->x - $c->x) * ($a->x + $c->x) + ($a->y - $c->y) * ($a->y + $c->y)) / 2;
        $uy = (($b->x - $c->x) * ($b->x + $c->x) + ($b->y - $c->y) * ($b->y + $c->y)) / 2;
        
        $center = new Point(
            (($a->y - $c->y) * $uy - ($b->y - $c->y) * $ux) / $d,
            (($b->x - $c->x) * $ux - ($a->x - $c->x) * $uy) / $d
        );
        
        $radius = $center->distanceTo($a);
        
        return ['center' => $center, 'radius' => $radius];
    }
    
    private static function pointInCircle(Point $p, Point $center, float $radius): bool {
        return $p->distanceTo($center) <= $radius + 1e-10;
    }
}

// Usage
$points = [
    new Point(0, 0),
    new Point(4, 0),
    new Point(4, 4),
    new Point(0, 4),
    new Point(2, 2),
];

$circle = MinimumEnclosingCircle::find($points);

echo "Minimum enclosing circle:\n";
echo "Center: {$circle['center']}\n";
echo "Radius: {$circle['radius']}\n";
```

**Why It Works**: Welzl's algorithm uses a recursive approach. If all points are inside the current circle, we're done. If a point is outside, it must be on the boundary of the new minimum circle. The algorithm builds circles from 1, 2, or 3 boundary points. The circumcircle calculation for three points uses the perpendicular bisectors of two sides, finding their intersection point.

**Time Complexity**: O(n) expected, O(n²) worst case
**Space Complexity**: O(n)

## Spatial Indexing with Quadtree

For efficient spatial queries on large point sets, we need spatial indexing. A quadtree recursively divides 2D space into four quadrants, enabling fast range queries and nearest neighbor searches.

### Quadtree Implementation

A quadtree partitions space into four equal-sized quadrants. Points are stored in leaf nodes, and nodes are subdivided when they exceed capacity.

```php
# filename: quadtree.php
<?php

declare(strict_types=1);

class Quadtree {
    private const MAX_CAPACITY = 4;
    private const MAX_DEPTH = 10;
    
    private ?BoundingBox $boundary = null;
    private array $points = [];
    private bool $divided = false;
    private int $depth;
    
    private ?Quadtree $northwest = null;
    private ?Quadtree $northeast = null;
    private ?Quadtree $southwest = null;
    private ?Quadtree $southeast = null;
    
    public function __construct(BoundingBox $boundary, int $depth = 0) {
        $this->boundary = $boundary;
        $this->depth = $depth;
    }
    
    public function insert(Point $point): bool {
        if (!$this->boundary->contains($point)) {
            return false;
        }
        
        if (count($this->points) < self::MAX_CAPACITY && !$this->divided) {
            $this->points[] = $point;
            return true;
        }
        
        if (!$this->divided) {
            $this->subdivide();
        }
        
        return $this->northwest->insert($point) ||
               $this->northeast->insert($point) ||
               $this->southwest->insert($point) ||
               $this->southeast->insert($point);
    }
    
    public function query(BoundingBox $range, array &$found = []): array {
        if (!$this->boundary->intersects($range)) {
            return $found;
        }
        
        foreach ($this->points as $point) {
            if ($range->contains($point)) {
                $found[] = $point;
            }
        }
        
        if ($this->divided) {
            $this->northwest->query($range, $found);
            $this->northeast->query($range, $found);
            $this->southwest->query($range, $found);
            $this->southeast->query($range, $found);
        }
        
        return $found;
    }
    
    private function subdivide(): void {
        if ($this->depth >= self::MAX_DEPTH) {
            return;
        }
        
        $x = $this->boundary->center->x;
        $y = $this->boundary->center->y;
        $w = $this->boundary->width / 2;
        $h = $this->boundary->height / 2;
        
        $nw = new BoundingBox(
            new Point($x - $w, $y - $h),
            $w,
            $h
        );
        $ne = new BoundingBox(
            new Point($x + $w, $y - $h),
            $w,
            $h
        );
        $sw = new BoundingBox(
            new Point($x - $w, $y + $h),
            $w,
            $h
        );
        $se = new BoundingBox(
            new Point($x + $w, $y + $h),
            $w,
            $h
        );
        
        $this->northwest = new Quadtree($nw, $this->depth + 1);
        $this->northeast = new Quadtree($ne, $this->depth + 1);
        $this->southwest = new Quadtree($sw, $this->depth + 1);
        $this->southeast = new Quadtree($se, $this->depth + 1);
        
        $this->divided = true;
        
        // Redistribute points
        $points = $this->points;
        $this->points = [];
        
        foreach ($points as $point) {
            $this->northwest->insert($point) ||
            $this->northeast->insert($point) ||
            $this->southwest->insert($point) ||
            $this->southeast->insert($point);
        }
    }
}

class BoundingBox {
    public function __construct(
        public Point $center,
        public float $width,
        public float $height
    ) {}
    
    public function contains(Point $point): bool {
        return $point->x >= $this->center->x - $this->width / 2 &&
               $point->x <= $this->center->x + $this->width / 2 &&
               $point->y >= $this->center->y - $this->height / 2 &&
               $point->y <= $this->center->y + $this->height / 2;
    }
    
    public function intersects(BoundingBox $other): bool {
        return !($this->center->x + $this->width / 2 < $other->center->x - $other->width / 2 ||
                 $other->center->x + $other->width / 2 < $this->center->x - $this->width / 2 ||
                 $this->center->y + $this->height / 2 < $other->center->y - $other->height / 2 ||
                 $other->center->y + $other->height / 2 < $this->center->y - $this->height / 2);
    }
}

// Usage
$boundary = new BoundingBox(new Point(0, 0), 100, 100);
$quadtree = new Quadtree($boundary);

// Insert points
$points = [
    new Point(10, 10),
    new Point(20, 20),
    new Point(30, 30),
    new Point(40, 40),
    new Point(50, 50),
];

foreach ($points as $point) {
    $quadtree->insert($point);
}

// Query points in range
$queryRange = new BoundingBox(new Point(25, 25), 20, 20);
$found = $quadtree->query($queryRange);

echo "Found " . count($found) . " points in range\n";
foreach ($found as $point) {
    echo $point . "\n";
}
```

**Why It Works**: A quadtree recursively divides space into four quadrants. Points are stored in leaf nodes until capacity is reached, then the node subdivides. Range queries traverse only nodes that intersect the query region, dramatically reducing comparisons. The tree adapts to point distribution, creating more subdivisions in dense areas.

**Time Complexity**: 
- Insert: O(log n) average, O(n) worst case
- Query: O(log n + k) where k is results found
**Space Complexity**: O(n)

## Voronoi Diagrams

A Voronoi diagram partitions space into regions based on distance to a set of seed points. Each region contains all points closer to its seed than to any other. This is fundamental for spatial analysis, nearest neighbor queries, and procedural generation.

### Fortune's Sweep Line Algorithm

We'll implement a simplified version that builds Voronoi diagrams using a sweep line approach. For production use, consider more robust implementations.

```php
# filename: voronoi-diagram.php
<?php

declare(strict_types=1);

class VoronoiDiagram {
    public static function build(array $sites): array {
        if (count($sites) < 2) {
            return [];
        }
        
        $diagram = [];
        
        // For each site, find its Voronoi cell
        foreach ($sites as $i => $site) {
            $cell = self::computeCell($site, $sites, $i);
            $diagram[] = [
                'site' => $site,
                'cell' => $cell
            ];
        }
        
        return $diagram;
    }
    
    private static function computeCell(Point $site, array $allSites, int $siteIndex): Polygon {
        // Find perpendicular bisectors with all other sites
        $halfPlanes = [];
        
        foreach ($allSites as $j => $other) {
            if ($siteIndex === $j) {
                continue;
            }
            
            // Midpoint
            $mid = new Point(
                ($site->x + $other->x) / 2,
                ($site->y + $other->y) / 2
            );
            
            // Direction vector (perpendicular to line between sites)
            $dx = $other->x - $site->x;
            $dy = $other->y - $site->y;
            
            // Perpendicular direction
            $perpX = -$dy;
            $perpY = $dx;
            
            // Normalize
            $len = sqrt($perpX * $perpX + $perpY * $perpY);
            if ($len > 0) {
                $perpX /= $len;
                $perpY /= $len;
            }
            
            // Create half-plane constraint
            $halfPlanes[] = [
                'point' => $mid,
                'normal' => new Vector($perpX, $perpY),
                'site' => $site
            ];
        }
        
        // Compute intersection of half-planes (simplified convex hull approach)
        return self::intersectHalfPlanes($halfPlanes, $site);
    }
    
    private static function intersectHalfPlanes(array $halfPlanes, Point $site): Polygon {
        // Simplified: use bounding box and clip with half-planes
        // For production, use proper half-plane intersection algorithm
        
        $minX = $site->x - 1000;
        $maxX = $site->x + 1000;
        $minY = $site->y - 1000;
        $maxY = $site->y + 1000;
        
        $vertices = [
            new Point($minX, $minY),
            new Point($maxX, $minY),
            new Point($maxX, $maxY),
            new Point($minX, $maxY),
        ];
        
        // Clip with each half-plane
        foreach ($halfPlanes as $halfPlane) {
            $vertices = self::clipPolygon($vertices, $halfPlane);
        }
        
        return new Polygon($vertices);
    }
    
    private static function clipPolygon(array $vertices, array $halfPlane): array {
        $clipped = [];
        $n = count($vertices);
        
        for ($i = 0; $i < $n; $i++) {
            $curr = $vertices[$i];
            $next = $vertices[($i + 1) % $n];
            
            $currInside = self::pointInHalfPlane($curr, $halfPlane);
            $nextInside = self::pointInHalfPlane($next, $halfPlane);
            
            if ($currInside) {
                $clipped[] = $curr;
            }
            
            if ($currInside !== $nextInside) {
                // Intersection point
                $intersection = self::lineHalfPlaneIntersection($curr, $next, $halfPlane);
                if ($intersection !== null) {
                    $clipped[] = $intersection;
                }
            }
        }
        
        return $clipped;
    }
    
    private static function pointInHalfPlane(Point $p, array $halfPlane): bool {
        $v = new Vector(
            $p->x - $halfPlane['point']->x,
            $p->y - $halfPlane['point']->y
        );
        return $v->dot($halfPlane['normal']) >= 0;
    }
    
    private static function lineHalfPlaneIntersection(Point $p1, Point $p2, array $halfPlane): ?Point {
        $d = new Vector($p2->x - $p1->x, $p2->y - $p1->y);
        $w = new Vector(
            $p1->x - $halfPlane['point']->x,
            $p1->y - $halfPlane['point']->y
        );
        
        $denom = $halfPlane['normal']->dot($d);
        if (abs($denom) < 1e-10) {
            return null;
        }
        
        $t = -$halfPlane['normal']->dot($w) / $denom;
        
        if ($t < 0 || $t > 1) {
            return null;
        }
        
        return new Point(
            $p1->x + $t * $d->x,
            $p1->y + $t * $d->y
        );
    }
}

// Usage
$sites = [
    new Point(10, 10),
    new Point(30, 10),
    new Point(20, 30),
    new Point(40, 40),
];

$diagram = VoronoiDiagram::build($sites);

echo "Voronoi diagram with " . count($diagram) . " cells\n";
foreach ($diagram as $i => $cell) {
    echo "Cell " . ($i + 1) . " at site {$cell['site']}\n";
    echo "  Vertices: " . count($cell['cell']->vertices) . "\n";
}
```

**Why It Works**: A Voronoi diagram divides space into regions where each region contains points closest to one seed site. The boundaries are formed by perpendicular bisectors between pairs of sites. Our implementation computes each cell by intersecting half-planes (all points closer to this site than to each other site). The half-plane intersection clips a large bounding box progressively with each constraint.

**Time Complexity**: O(n² log n) for this simplified version
**Space Complexity**: O(n²)

**Note**: For production use, Fortune's sweep line algorithm achieves O(n log n) time complexity, but requires more complex implementation with beach line and event queue data structures.

## Troubleshooting

Geometric algorithms involve floating-point arithmetic, which can introduce precision issues. Here are common problems and solutions:

### Error: "Incorrect intersection detection"

**Symptom**: Line segments that should intersect are reported as non-intersecting, or vice versa.

**Cause**: Floating-point precision errors in orientation tests or intersection calculations.

**Solution**: Use epsilon thresholds for comparisons:

```php
// Instead of exact equality
if ($val == 0) { ... }

// Use epsilon threshold
if (abs($val) < 1e-10) { ... }
```

### Error: "Convex hull missing points"

**Symptom**: Some points that should be on the hull are excluded.

**Cause**: Incorrect handling of collinear points or wrong orientation test.

**Solution**: Ensure collinear points are handled correctly in the sorting step:

```php
if ($orientation === GeometryUtils::COLLINEAR) {
    // Keep closer point first
    return $start->distanceTo($a) <=> $start->distanceTo($b);
}
```

### Error: "Point-in-polygon gives wrong results"

**Symptom**: Points clearly inside are reported as outside, or boundary points are inconsistent.

**Cause**: Ray casting algorithm doesn't handle edge cases (horizontal rays, boundary points).

**Solution**: Check boundary separately and handle horizontal edges:

```php
// First check if point is on boundary
if ($polygon->onBoundary($point)) {
    return true;  // or false, depending on your needs
}

// Then use ray casting for interior
return $polygon->contains($point);
```

### Error: "Collision detection too slow"

**Symptom**: Polygon collision detection takes too long for many objects.

**Cause**: Checking all edges of both polygons (O(nm) complexity).

**Solution**: Use spatial partitioning or bounding box pre-checks:

```php
// Quick rejection: check bounding boxes first
if (!boundingBoxesOverlap($poly1, $poly2)) {
    return false;  // Can't collide
}

// Then do expensive SAT test
return polygonPolygon($poly1, $poly2);
```

## Wrap-up

You've completed this chapter on geometric algorithms! Here's what you accomplished:

- ✓ Implemented fundamental geometric primitives (Point, Vector, LineSegment)
- ✓ Built orientation tests and line intersection algorithms
- ✓ Created convex hull algorithms (Graham Scan and Jarvis March)
- ✓ Implemented point-in-polygon testing using ray casting
- ✓ Solved the closest pair problem using divide-and-conquer
- ✓ Triangulated polygons using ear clipping algorithm
- ✓ Found minimum enclosing circles with Welzl's algorithm
- ✓ Built quadtree spatial index for efficient queries
- ✓ Constructed Voronoi diagrams for spatial partitioning
- ✓ Applied geometric algorithms to real-world problems (geofencing, collision detection)
- ✓ Understood time and space complexity of geometric operations

Geometric algorithms are essential for graphics, mapping, robotics, and game development. The techniques you've learned—orientation tests, convex hulls, and spatial queries—form the foundation for more advanced computational geometry problems.

These algorithms power collision detection in game engines, geographic information systems (GIS), computer-aided design (CAD) software, and robotics path planning. Understanding these fundamentals prepares you for tackling complex spatial problems in real-world applications.

## Further Reading

- [Computational Geometry Algorithms Library (CGAL)](https://www.cgal.org/) — Comprehensive C++ library for computational geometry
- [Introduction to Algorithms (CLRS)](https://mitpress.mit.edu/9780262046305/introduction-to-algorithms/) — Chapter 33 covers computational geometry
- [Geometric Algorithms](https://cp-algorithms.com/geometry/basic-geometry.html) — Competitive programming guide to geometric algorithms
- [Chapter 19: Graph Algorithms](/series/php-algorithms/chapters/19-graph-algorithms) — Spatial graphs and shortest paths
- [Chapter 31: Concurrent Algorithms](/series/php-algorithms/chapters/31-concurrent-algorithms) — Parallel geometric processing

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 34 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-34)**

Clone the repository to run examples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-34
php 01-*.php
```

## Practice Exercises

### Exercise 1: Line Simplification Algorithm

**Goal**: Implement the Ramer-Douglas-Peucker algorithm for simplifying polygonal lines

Create a class called `LineSimplifier` that:

- Takes an array of points and a tolerance threshold
- Recursively removes points that don't significantly affect the line shape
- Returns a simplified array of points

**Validation**: Test with a complex polyline and verify the simplified version maintains the general shape while reducing point count.

### Exercise 2: Spatial Index Implementation

**Goal**: Build a simple spatial index for efficient point queries

Create a class called `SpatialIndex` that:

- Divides space into a grid of cells
- Stores points in appropriate grid cells
- Provides fast queries for points within a bounding box
- Handles edge cases where points span multiple cells

**Validation**: Compare query performance against a naive O(n) search for large point sets.

### Exercise 3: Polygon Triangulation

**Goal**: Implement a simple polygon triangulation algorithm

Create a class called `PolygonTriangulator` that:

- Takes a simple polygon (no self-intersections)
- Triangulates it into triangles
- Returns an array of triangle vertices

**Validation**: Verify all triangles are valid and cover the entire polygon area.

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="34"
  label="Geometric Algorithms mastered!"
/>
