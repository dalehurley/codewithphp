<?php

declare(strict_types=1);

/**
 * Chapter 34: Geometric Algorithms - Collision Detection
 *
 * This example demonstrates various collision detection algorithms:
 * - Circle-circle collision
 * - Circle-rectangle collision
 * - Rectangle-rectangle collision (AABB)
 * - Polygon-polygon collision (SAT - Separating Axis Theorem)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

require_once '01-point-vector-operations.php';
require_once '04-point-in-polygon.php';

/**
 * Represents a circle for collision detection
 */
class Circle
{
    /**
     * @param Point $center Circle center
     * @param float $radius Circle radius
     */
    public function __construct(
        public Point $center,
        public float $radius
    ) {}

    /**
     * Check collision with another circle
     *
     * @param Circle $other The other circle
     * @return bool True if circles collide
     */
    public function collidesWith(Circle $other): bool
    {
        $distance = $this->center->distanceTo($other->center);
        return $distance <= ($this->radius + $other->radius);
    }

    /**
     * Get collision penetration depth with another circle
     *
     * @param Circle $other The other circle
     * @return float Penetration depth (0 if no collision)
     */
    public function penetrationDepth(Circle $other): float
    {
        $distance = $this->center->distanceTo($other->center);
        $overlap = ($this->radius + $other->radius) - $distance;
        return max(0, $overlap);
    }

    public function __toString(): string
    {
        return sprintf("Circle(center: %s, radius: %.2f)", $this->center, $this->radius);
    }
}

/**
 * Represents an axis-aligned bounding box (AABB) for collision detection
 */
class Rectangle
{
    /**
     * @param Point $min Minimum corner (bottom-left)
     * @param Point $max Maximum corner (top-right)
     */
    public function __construct(
        public Point $min,
        public Point $max
    ) {}

    /**
     * Create a rectangle from center and dimensions
     *
     * @param Point $center Center point
     * @param float $width Width
     * @param float $height Height
     * @return self
     */
    public static function fromCenter(Point $center, float $width, float $height): self
    {
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;

        return new self(
            new Point($center->x - $halfWidth, $center->y - $halfHeight),
            new Point($center->x + $halfWidth, $center->y + $halfHeight)
        );
    }

    /**
     * Check collision with another rectangle
     *
     * @param Rectangle $other The other rectangle
     * @return bool True if rectangles collide
     */
    public function collidesWith(Rectangle $other): bool
    {
        return !($this->max->x < $other->min->x ||
                 $other->max->x < $this->min->x ||
                 $this->max->y < $other->min->y ||
                 $other->max->y < $this->min->y);
    }

    /**
     * Check if a point is inside the rectangle
     *
     * @param Point $point Point to check
     * @return bool True if point is inside
     */
    public function contains(Point $point): bool
    {
        return $point->x >= $this->min->x &&
               $point->x <= $this->max->x &&
               $point->y >= $this->min->y &&
               $point->y <= $this->max->y;
    }

    /**
     * Get the center of the rectangle
     *
     * @return Point Center point
     */
    public function center(): Point
    {
        return new Point(
            ($this->min->x + $this->max->x) / 2,
            ($this->min->y + $this->max->y) / 2
        );
    }

    /**
     * Get the width of the rectangle
     *
     * @return float Width
     */
    public function width(): float
    {
        return $this->max->x - $this->min->x;
    }

    /**
     * Get the height of the rectangle
     *
     * @return float Height
     */
    public function height(): float
    {
        return $this->max->y - $this->min->y;
    }

    /**
     * Calculate the area of the rectangle
     *
     * @return float Area
     */
    public function area(): float
    {
        return $this->width() * $this->height();
    }

    /**
     * Get the intersection rectangle with another rectangle
     *
     * @param Rectangle $other The other rectangle
     * @return Rectangle|null Intersection rectangle or null if no collision
     */
    public function intersection(Rectangle $other): ?Rectangle
    {
        if (!$this->collidesWith($other)) {
            return null;
        }

        return new Rectangle(
            new Point(
                max($this->min->x, $other->min->x),
                max($this->min->y, $other->min->y)
            ),
            new Point(
                min($this->max->x, $other->max->x),
                min($this->max->y, $other->max->y)
            )
        );
    }

    public function __toString(): string
    {
        return sprintf("Rectangle(min: %s, max: %s)", $this->min, $this->max);
    }
}

/**
 * Comprehensive collision detection system
 */
class CollisionDetector
{
    /**
     * Check circle-circle collision
     *
     * @param Circle $c1 First circle
     * @param Circle $c2 Second circle
     * @return bool True if collision detected
     */
    public function circleCircle(Circle $c1, Circle $c2): bool
    {
        return $c1->collidesWith($c2);
    }

    /**
     * Check circle-rectangle collision
     *
     * @param Circle $circle Circle
     * @param Rectangle $rect Rectangle
     * @return bool True if collision detected
     */
    public function circleRectangle(Circle $circle, Rectangle $rect): bool
    {
        // Find closest point on rectangle to circle center
        $closestX = max($rect->min->x, min($circle->center->x, $rect->max->x));
        $closestY = max($rect->min->y, min($circle->center->y, $rect->max->y));

        $closest = new Point($closestX, $closestY);

        return $circle->center->distanceTo($closest) <= $circle->radius;
    }

    /**
     * Check rectangle-rectangle collision
     *
     * @param Rectangle $r1 First rectangle
     * @param Rectangle $r2 Second rectangle
     * @return bool True if collision detected
     */
    public function rectangleRectangle(Rectangle $r1, Rectangle $r2): bool
    {
        return $r1->collidesWith($r2);
    }

    /**
     * Check polygon-polygon collision using Separating Axis Theorem (SAT)
     *
     * @param Polygon $poly1 First polygon
     * @param Polygon $poly2 Second polygon
     * @return bool True if collision detected
     */
    public function polygonPolygon(Polygon $poly1, Polygon $poly2): bool
    {
        $vertices1 = $poly1->getVertices();
        $vertices2 = $poly2->getVertices();

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

    /**
     * Get edges of a polygon
     *
     * @param array<Point> $vertices Polygon vertices
     * @return array<LineSegment> Array of edges
     */
    private function getEdges(array $vertices): array
    {
        $edges = [];
        $n = count($vertices);

        for ($i = 0; $i < $n; $i++) {
            $edges[] = new LineSegment($vertices[$i], $vertices[($i + 1) % $n]);
        }

        return $edges;
    }

    /**
     * Get perpendicular axis for an edge
     *
     * @param LineSegment $edge Edge
     * @return Vector Perpendicular axis
     */
    private function getPerpendicularAxis(LineSegment $edge): Vector
    {
        $dx = $edge->end->x - $edge->start->x;
        $dy = $edge->end->y - $edge->start->y;
        return new Vector(-$dy, $dx);
    }

    /**
     * Check if an axis is a separating axis
     *
     * @param Vector $axis Axis to check
     * @param array<Point> $vertices1 First polygon vertices
     * @param array<Point> $vertices2 Second polygon vertices
     * @return bool True if axis is separating
     */
    private function isSeparatingAxis(Vector $axis, array $vertices1, array $vertices2): bool
    {
        $proj1 = $this->projectPolygon($axis, $vertices1);
        $proj2 = $this->projectPolygon($axis, $vertices2);

        return $proj1['max'] < $proj2['min'] || $proj2['max'] < $proj1['min'];
    }

    /**
     * Project polygon onto axis
     *
     * @param Vector $axis Axis
     * @param array<Point> $vertices Polygon vertices
     * @return array Min and max projection values
     */
    private function projectPolygon(Vector $axis, array $vertices): array
    {
        $min = $max = $axis->dot(new Vector($vertices[0]->x, $vertices[0]->y));

        foreach ($vertices as $vertex) {
            $projection = $axis->dot(new Vector($vertex->x, $vertex->y));
            $min = min($min, $projection);
            $max = max($max, $projection);
        }

        return ['min' => $min, 'max' => $max];
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Collision Detection Demo ===\n\n";

$detector = new CollisionDetector();

// Example 1: Circle-Circle collision
echo "1. Circle-Circle Collision:\n";
$circle1 = new Circle(new Point(0, 0), 5);
$circle2 = new Circle(new Point(8, 0), 5);
$circle3 = new Circle(new Point(15, 0), 5);

echo "Circle 1: $circle1\n";
echo "Circle 2: $circle2\n";
echo "Circle 3: $circle3\n\n";

$collision1 = $detector->circleCircle($circle1, $circle2);
echo "Circle 1 and Circle 2: " . ($collision1 ? "✓ Collision" : "✗ No collision") . "\n";
if ($collision1) {
    echo "   Penetration depth: " .
         number_format($circle1->penetrationDepth($circle2), 2) . "\n";
}

$collision2 = $detector->circleCircle($circle1, $circle3);
echo "Circle 1 and Circle 3: " . ($collision2 ? "✓ Collision" : "✗ No collision") . "\n";

// Example 2: Circle-Rectangle collision
echo "\n2. Circle-Rectangle Collision:\n";
$circle = new Circle(new Point(5, 5), 3);
$rect1 = new Rectangle(new Point(0, 0), new Point(8, 8));
$rect2 = new Rectangle(new Point(10, 10), new Point(15, 15));

echo "Circle: $circle\n";
echo "Rectangle 1: $rect1\n";
echo "Rectangle 2: $rect2\n\n";

$collision3 = $detector->circleRectangle($circle, $rect1);
echo "Circle and Rectangle 1: " . ($collision3 ? "✓ Collision" : "✗ No collision") . "\n";

$collision4 = $detector->circleRectangle($circle, $rect2);
echo "Circle and Rectangle 2: " . ($collision4 ? "✓ Collision" : "✗ No collision") . "\n";

// Example 3: Rectangle-Rectangle collision (AABB)
echo "\n3. Rectangle-Rectangle Collision (AABB):\n";
$rectA = Rectangle::fromCenter(new Point(5, 5), 4, 4);
$rectB = Rectangle::fromCenter(new Point(7, 7), 4, 4);
$rectC = Rectangle::fromCenter(new Point(15, 15), 4, 4);

echo "Rectangle A: center={$rectA->center()}, size={$rectA->width()}x{$rectA->height()}\n";
echo "Rectangle B: center={$rectB->center()}, size={$rectB->width()}x{$rectB->height()}\n";
echo "Rectangle C: center={$rectC->center()}, size={$rectC->width()}x{$rectC->height()}\n\n";

$collision5 = $detector->rectangleRectangle($rectA, $rectB);
echo "Rectangle A and B: " . ($collision5 ? "✓ Collision" : "✗ No collision") . "\n";

if ($collision5) {
    $intersection = $rectA->intersection($rectB);
    echo "   Intersection area: " . number_format($intersection->area(), 2) . "\n";
}

$collision6 = $detector->rectangleRectangle($rectA, $rectC);
echo "Rectangle A and C: " . ($collision6 ? "✓ Collision" : "✗ No collision") . "\n";

// Example 4: Polygon-Polygon collision (SAT)
echo "\n4. Polygon-Polygon Collision (SAT):\n";
$triangle1 = new Polygon([
    new Point(0, 0),
    new Point(4, 0),
    new Point(2, 4)
]);

$triangle2 = new Polygon([
    new Point(3, 1),
    new Point(7, 1),
    new Point(5, 5)
]);

$triangle3 = new Polygon([
    new Point(10, 0),
    new Point(14, 0),
    new Point(12, 4)
]);

echo "Testing triangle-triangle collision...\n";
$collision7 = $detector->polygonPolygon($triangle1, $triangle2);
echo "Triangle 1 and Triangle 2: " . ($collision7 ? "✓ Collision" : "✗ No collision") . "\n";

$collision8 = $detector->polygonPolygon($triangle1, $triangle3);
echo "Triangle 1 and Triangle 3: " . ($collision8 ? "✓ Collision" : "✗ No collision") . "\n";

// Example 5: Real-world - Game collision system
echo "\n5. Real-World Example - 2D Game Collision System:\n";

class GameObject
{
    public function __construct(
        public string $name,
        public Point $position,
        public Circle|Rectangle $collider
    ) {}

    public function move(float $dx, float $dy): void
    {
        $this->position = new Point($this->position->x + $dx, $this->position->y + $dy);

        if ($this->collider instanceof Circle) {
            $this->collider = new Circle(
                $this->position,
                $this->collider->radius
            );
        } else {
            $width = $this->collider->width();
            $height = $this->collider->height();
            $this->collider = Rectangle::fromCenter($this->position, $width, $height);
        }
    }
}

// Create game objects
$player = new GameObject(
    'Player',
    new Point(5, 5),
    new Circle(new Point(5, 5), 1)
);

$enemy = new GameObject(
    'Enemy',
    new Point(10, 5),
    new Circle(new Point(10, 5), 1)
);

$wall = new GameObject(
    'Wall',
    new Point(8, 5),
    Rectangle::fromCenter(new Point(8, 5), 2, 10)
);

echo "Initial positions:\n";
echo "   {$player->name}: {$player->position}\n";
echo "   {$enemy->name}: {$enemy->position}\n";
echo "   {$wall->name}: {$wall->position}\n\n";

// Simulate player movement
echo "Player moves right...\n";
for ($i = 0; $i < 5; $i++) {
    $player->move(1, 0);

    // Check collision with wall
    $hitWall = $detector->circleRectangle($player->collider, $wall->collider);

    // Check collision with enemy
    $hitEnemy = $detector->circleCircle($player->collider, $enemy->collider);

    echo "   Step " . ($i + 1) . ": Position {$player->position}";

    if ($hitWall) {
        echo " - ✗ HIT WALL!";
        break;
    }

    if ($hitEnemy) {
        echo " - ✗ HIT ENEMY!";
        break;
    }

    echo " - ✓ Clear\n";
}

// Example 6: Performance test
echo "\n6. Performance Test:\n";

$numObjects = 100;
$objects = [];

// Create random circles
for ($i = 0; $i < $numObjects; $i++) {
    $objects[] = new Circle(
        new Point(rand(0, 100), rand(0, 100)),
        rand(1, 5)
    );
}

echo "Testing $numObjects circles for collisions...\n";

$start = microtime(true);
$collisionCount = 0;

for ($i = 0; $i < $numObjects; $i++) {
    for ($j = $i + 1; $j < $numObjects; $j++) {
        if ($detector->circleCircle($objects[$i], $objects[$j])) {
            $collisionCount++;
        }
    }
}

$elapsed = microtime(true) - $start;
$comparisons = ($numObjects * ($numObjects - 1)) / 2;

echo "Performed $comparisons collision checks in " .
     number_format($elapsed * 1000, 2) . " ms\n";
echo "Found $collisionCount collisions\n";
echo "Average time per check: " .
     number_format($elapsed * 1000000 / $comparisons, 2) . " μs\n";

echo "\n=== Demo Complete ===\n";
