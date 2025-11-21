<?php

declare(strict_types=1);

/**
 * Chapter 34: Geometric Algorithms - Point and Vector Operations
 *
 * This example demonstrates fundamental geometric primitives including:
 * - Point operations (distance, addition, subtraction)
 * - Vector operations (dot product, cross product, magnitude)
 * - Orientation tests (clockwise, counter-clockwise, collinear)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

/**
 * Represents a 2D point in Cartesian coordinate system
 */
class Point
{
    /**
     * @param float $x X-coordinate
     * @param float $y Y-coordinate
     */
    public function __construct(
        public float $x,
        public float $y
    ) {}

    /**
     * Calculate Euclidean distance to another point
     *
     * @param Point $other The other point
     * @return float Distance between points
     */
    public function distanceTo(Point $other): float
    {
        $dx = $this->x - $other->x;
        $dy = $this->y - $other->y;
        return sqrt($dx * $dx + $dy * $dy);
    }

    /**
     * Subtract another point (vector subtraction)
     *
     * @param Point $other Point to subtract
     * @return Point Resulting point
     */
    public function subtract(Point $other): Point
    {
        return new Point($this->x - $other->x, $this->y - $other->y);
    }

    /**
     * Add another point (vector addition)
     *
     * @param Point $other Point to add
     * @return Point Resulting point
     */
    public function add(Point $other): Point
    {
        return new Point($this->x + $other->x, $this->y + $other->y);
    }

    /**
     * Scale the point by a factor
     *
     * @param float $factor Scaling factor
     * @return Point Scaled point
     */
    public function scale(float $factor): Point
    {
        return new Point($this->x * $factor, $this->y * $factor);
    }

    /**
     * Calculate Manhattan distance to another point
     *
     * @param Point $other The other point
     * @return float Manhattan distance
     */
    public function manhattanDistance(Point $other): float
    {
        return abs($this->x - $other->x) + abs($this->y - $other->y);
    }

    public function __toString(): string
    {
        return sprintf("(%.2f, %.2f)", $this->x, $this->y);
    }
}

/**
 * Represents a 2D vector with additional vector operations
 */
class Vector extends Point
{
    /**
     * Calculate dot product with another vector
     *
     * @param Vector $other The other vector
     * @return float Dot product result
     */
    public function dot(Vector $other): float
    {
        return $this->x * $other->x + $this->y * $other->y;
    }

    /**
     * Calculate cross product (z-component of 3D cross product)
     * Returns the magnitude of the perpendicular vector
     *
     * @param Vector $other The other vector
     * @return float Cross product result
     */
    public function cross(Vector $other): float
    {
        return $this->x * $other->y - $this->y * $other->x;
    }

    /**
     * Calculate the magnitude (length) of the vector
     *
     * @return float Vector magnitude
     */
    public function magnitude(): float
    {
        return sqrt($this->x * $this->x + $this->y * $this->y);
    }

    /**
     * Normalize the vector to unit length
     *
     * @return Vector Normalized vector
     */
    public function normalize(): Vector
    {
        $mag = $this->magnitude();
        if ($mag == 0) {
            return new Vector(0, 0);
        }
        return new Vector($this->x / $mag, $this->y / $mag);
    }

    /**
     * Calculate the angle of the vector in radians
     *
     * @return float Angle in radians
     */
    public function angle(): float
    {
        return atan2($this->y, $this->x);
    }

    /**
     * Calculate the angle between this vector and another
     *
     * @param Vector $other The other vector
     * @return float Angle in radians
     */
    public function angleBetween(Vector $other): float
    {
        $dot = $this->dot($other);
        $mag1 = $this->magnitude();
        $mag2 = $other->magnitude();

        if ($mag1 == 0 || $mag2 == 0) {
            return 0;
        }

        return acos($dot / ($mag1 * $mag2));
    }

    /**
     * Check if this vector is perpendicular to another
     *
     * @param Vector $other The other vector
     * @param float $epsilon Tolerance for floating point comparison
     * @return bool True if perpendicular
     */
    public function isPerpendicular(Vector $other, float $epsilon = 1e-10): bool
    {
        return abs($this->dot($other)) < $epsilon;
    }

    /**
     * Check if this vector is parallel to another
     *
     * @param Vector $other The other vector
     * @param float $epsilon Tolerance for floating point comparison
     * @return bool True if parallel
     */
    public function isParallel(Vector $other, float $epsilon = 1e-10): bool
    {
        return abs($this->cross($other)) < $epsilon;
    }
}

/**
 * Utility class for geometric calculations
 */
class GeometryUtils
{
    public const COLLINEAR = 0;
    public const CLOCKWISE = 1;
    public const COUNTER_CLOCKWISE = 2;

    /**
     * Determine the orientation of three points
     *
     * @param Point $p First point
     * @param Point $q Second point
     * @param Point $r Third point
     * @return int Orientation constant
     */
    public static function orientation(Point $p, Point $q, Point $r): int
    {
        // Calculate cross product of vectors (q-p) and (r-q)
        $val = ($q->y - $p->y) * ($r->x - $q->x) - ($q->x - $p->x) * ($r->y - $q->y);

        if (abs($val) < 1e-10) {
            return self::COLLINEAR;
        }

        return ($val > 0) ? self::CLOCKWISE : self::COUNTER_CLOCKWISE;
    }

    /**
     * Check if point q lies on segment pr (given they are collinear)
     *
     * @param Point $p First endpoint
     * @param Point $q Point to check
     * @param Point $r Second endpoint
     * @return bool True if q is on segment pr
     */
    public static function onSegment(Point $p, Point $q, Point $r): bool
    {
        return $q->x <= max($p->x, $r->x) && $q->x >= min($p->x, $r->x) &&
               $q->y <= max($p->y, $r->y) && $q->y >= min($p->y, $r->y);
    }

    /**
     * Calculate the signed area of a triangle
     *
     * @param Point $p1 First vertex
     * @param Point $p2 Second vertex
     * @param Point $p3 Third vertex
     * @return float Signed area (positive if counter-clockwise)
     */
    public static function triangleArea(Point $p1, Point $p2, Point $p3): float
    {
        return (($p2->x - $p1->x) * ($p3->y - $p1->y) -
                ($p3->x - $p1->x) * ($p2->y - $p1->y)) / 2;
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Point and Vector Operations Demo ===\n\n";

// Point operations
echo "1. Point Operations:\n";
$p1 = new Point(1, 2);
$p2 = new Point(4, 6);

echo "Point 1: $p1\n";
echo "Point 2: $p2\n";
echo "Euclidean Distance: " . number_format($p1->distanceTo($p2), 2) . "\n";
echo "Manhattan Distance: " . number_format($p1->manhattanDistance($p2), 2) . "\n";

$p3 = $p1->add($p2);
echo "Addition (p1 + p2): $p3\n";

$p4 = $p2->subtract($p1);
echo "Subtraction (p2 - p1): $p4\n";

$p5 = $p1->scale(2);
echo "Scaling (p1 * 2): $p5\n";

// Vector operations
echo "\n2. Vector Operations:\n";
$v1 = new Vector(3, 4);
$v2 = new Vector(1, 2);

echo "Vector 1: $v1\n";
echo "Vector 2: $v2\n";
echo "Magnitude of v1: " . number_format($v1->magnitude(), 2) . "\n";
echo "Dot product (v1 · v2): " . $v1->dot($v2) . "\n";
echo "Cross product (v1 × v2): " . $v1->cross($v2) . "\n";

$normalized = $v1->normalize();
echo "Normalized v1: $normalized (magnitude: " .
     number_format($normalized->magnitude(), 2) . ")\n";

echo "Angle of v1: " . number_format(rad2deg($v1->angle()), 2) . "°\n";
echo "Angle between v1 and v2: " .
     number_format(rad2deg($v1->angleBetween($v2)), 2) . "°\n";

// Perpendicular and parallel checks
$v3 = new Vector(4, -3); // Perpendicular to v1
$v4 = new Vector(6, 8);  // Parallel to v1

echo "\nv3 (4, -3) is perpendicular to v1: " .
     ($v1->isPerpendicular($v3) ? "Yes" : "No") . "\n";
echo "v4 (6, 8) is parallel to v1: " .
     ($v1->isParallel($v4) ? "Yes" : "No") . "\n";

// Orientation tests
echo "\n3. Orientation Tests:\n";
$a = new Point(0, 0);
$b = new Point(4, 4);
$c = new Point(1, 2);

$orientation = GeometryUtils::orientation($a, $b, $c);
$orientationName = match($orientation) {
    GeometryUtils::COLLINEAR => "Collinear",
    GeometryUtils::CLOCKWISE => "Clockwise",
    GeometryUtils::COUNTER_CLOCKWISE => "Counter-Clockwise"
};

echo "Points: $a, $b, $c\n";
echo "Orientation: $orientationName\n";

// Triangle area
$area = GeometryUtils::triangleArea($a, $b, $c);
echo "Triangle area: " . number_format(abs($area), 2) . "\n";

// Collinear points test
$d = new Point(8, 8); // On the line from a to b
$orientation2 = GeometryUtils::orientation($a, $b, $d);
$orientationName2 = match($orientation2) {
    GeometryUtils::COLLINEAR => "Collinear",
    GeometryUtils::CLOCKWISE => "Clockwise",
    GeometryUtils::COUNTER_CLOCKWISE => "Counter-Clockwise"
};

echo "\nPoints: $a, $b, $d\n";
echo "Orientation: $orientationName2\n";

if ($orientation2 === GeometryUtils::COLLINEAR) {
    echo "Point d is on segment ab: " .
         (GeometryUtils::onSegment($a, $d, $b) ? "Yes" : "No") . "\n";
}

// Real-world application example
echo "\n4. Real-World Example - Calculating Force Resultant:\n";
$force1 = new Vector(50, 30); // Force in Newtons
$force2 = new Vector(40, -20);

echo "Force 1: $force1 N\n";
echo "Force 2: $force2 N\n";

$resultant = new Vector(
    $force1->x + $force2->x,
    $force1->y + $force2->y
);

echo "Resultant force: $resultant N\n";
echo "Magnitude: " . number_format($resultant->magnitude(), 2) . " N\n";
echo "Direction: " . number_format(rad2deg($resultant->angle()), 2) . "°\n";

echo "\n=== Demo Complete ===\n";
