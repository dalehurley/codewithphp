---
title: "Geometric Algorithms"
description: "Computational geometry algorithms for graphics, GIS, and robotics including convex hull, collision detection, and spatial queries"
series: "php-algorithms"
chapter: 34
order: 34
difficulty: "advanced"
prerequisites: ["Mathematics", "2D Geometry", "Data Structures"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 34</span>
</div>

# Geometric Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

## What You'll Learn

Enter the fascinating world of computational geometry where algorithms solve spatial problems for graphics, games, robotics, and GIS applications. These techniques power everything from collision detection in games to route planning in navigation apps.

- Master fundamental geometric primitives: points, vectors, lines, and polygons
- Implement collision detection algorithms for game development and physics engines
- Build convex hull algorithms for shape analysis and pattern recognition
- Apply spatial indexing techniques for efficient geometric queries
- Solve real-world problems in mapping, computer graphics, and robotics

**Estimated Time**: ~50 minutes

## Prerequisites

Geometric algorithms blend math and code. You'll need:

- [ ] **Basic geometry** - Understanding of points, lines, angles, and coordinate systems
- [ ] **Vector mathematics** - Dot product, cross product, and vector operations
- [ ] **2D coordinate geometry** - Comfort with Cartesian coordinates and transformations
- [ ] **Data structures** - Arrays, sets, and spatial organization concepts

Ready to solve spatial problems with elegant algorithms? Let's explore computational geometry!

## Introduction

Computational geometry algorithms solve problems involving geometric objects like points, lines, and polygons. These algorithms are essential for graphics, mapping, CAD, robotics, and game development. They transform abstract geometric problems into efficient, practical solutions that run in real-time applications.

## Basic Geometric Primitives

### Point and Vector Operations

```php
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

### Orientation Test

Determines if three points make a counter-clockwise turn, clockwise turn, or are collinear.

```php
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

## Line Segment Intersection

### Implementation

```php
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

**Time Complexity**: O(1)
**Space Complexity**: O(1)

### Sweep Line Algorithm for Multiple Segments

```php
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

## Convex Hull

### Graham Scan Algorithm

```php
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

**Time Complexity**:
- Graham Scan: O(n log n)
- Jarvis March: O(nh) where h is hull size
**Space Complexity**: O(n)

## Point in Polygon

### Ray Casting Algorithm

```php
class Polygon {
    private array $vertices;

    public function __construct(array $vertices) {
        $this->vertices = $vertices;
    }

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

**Time Complexity**: O(n) where n is number of vertices
**Space Complexity**: O(1)

## Closest Pair of Points

### Divide and Conquer Approach

```php
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

**Time Complexity**: O(n log n)
**Space Complexity**: O(n)

## Real-World Applications

### 1. Geofencing

```php
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

        $vertices = $this->boundary->vertices ?? [];
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

### 2. Collision Detection

```php
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

        $vertices1 = $poly1->vertices ?? [];
        $vertices2 = $poly2->vertices ?? [];

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

## Summary

Geometric algorithms solve spatial problems efficiently:

- **Orientation Test**: Foundation for many geometric algorithms
- **Line Intersection**: Detect and compute segment intersections
- **Convex Hull**: Find minimal enclosing polygon
- **Point in Polygon**: Determine containment using ray casting
- **Closest Pair**: Find nearest points in O(n log n) time

**Key Applications**:
- Mapping and GIS
- Computer graphics
- Collision detection
- Computational geometry
- Robotics path planning

## Next Steps

- **Chapter 19: Graph Algorithms** - Spatial graphs and shortest paths
- **Chapter 20: Advanced Graph Algorithms** - Network flow problems
- **Chapter 31: Concurrent Algorithms** - Parallel geometric processing

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 34 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-34)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-34
php 01-*.php
```

## Practice Exercises

1. Implement a line simplification algorithm (Ramer-Douglas-Peucker)
2. Build a spatial index using R-trees
3. Create a polygon triangulation algorithm
4. Implement Voronoi diagram construction
5. Build a 2D physics engine with collision detection
