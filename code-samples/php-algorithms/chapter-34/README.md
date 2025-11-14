# Chapter 34: Geometric Algorithms - Code Samples

This directory contains comprehensive, runnable PHP code examples for Chapter 34 of the PHP Algorithms series, focusing on computational geometry algorithms.

## Overview

Computational geometry algorithms solve problems involving geometric objects like points, lines, and polygons. These algorithms are essential for graphics, mapping, CAD, robotics, and game development.

## Code Samples

### 1. Point and Vector Operations (`01-point-vector-operations.php`)

**Purpose**: Demonstrates fundamental geometric primitives and vector mathematics.

**Key Concepts**:
- Point operations (distance, addition, subtraction, scaling)
- Vector operations (dot product, cross product, magnitude, normalization)
- Orientation tests (clockwise, counter-clockwise, collinear)
- Angle calculations and vector relationships

**Classes**:
- `Point`: 2D point with distance and arithmetic operations
- `Vector`: Extended point class with vector-specific operations
- `GeometryUtils`: Utility class for geometric calculations

**Use Cases**:
- Physics simulations (force calculations)
- Computer graphics (transformations)
- Robotics (motion planning)

**Run**:
```bash
php 01-point-vector-operations.php
```

---

### 2. Line Segment Intersection (`02-line-intersection.php`)

**Purpose**: Implements algorithms to detect and compute line segment intersections.

**Key Concepts**:
- Basic line segment intersection detection
- Finding exact intersection points
- Sweep line algorithm for multiple segments
- Closest point on segment calculations

**Classes**:
- `LineSegment`: Represents a line segment with intersection methods
- `SegmentIntersectionDetector`: Finds all intersections among multiple segments

**Use Cases**:
- Road network analysis
- Computer-aided design (CAD)
- Computational geometry problems
- Geographic information systems (GIS)

**Time Complexity**: O(1) for single pair, O(n²) for n segments (with sweep line optimization)

**Run**:
```bash
php 02-line-intersection.php
```

---

### 3. Convex Hull (`03-convex-hull.php`)

**Purpose**: Computes the convex hull (smallest convex polygon containing all points) using multiple algorithms.

**Key Concepts**:
- Graham Scan algorithm (O(n log n))
- Jarvis March / Gift Wrapping algorithm (O(nh))
- Hull area and perimeter calculations
- Point containment testing

**Classes**:
- `ConvexHull`: Static methods for computing convex hulls

**Use Cases**:
- Bounding region calculations
- Collision detection preprocessing
- Pattern recognition
- Geographic service area definition

**Algorithms**:
1. **Graham Scan**: Fast for large point sets, sorts by polar angle
2. **Jarvis March**: Better for small hulls, wraps around boundary

**Run**:
```bash
php 03-convex-hull.php
```

---

### 4. Point in Polygon (`04-point-in-polygon.php`)

**Purpose**: Determines if a point lies inside, outside, or on the boundary of a polygon.

**Key Concepts**:
- Ray casting algorithm for point containment
- Boundary detection
- Polygon properties (area, centroid, perimeter)
- Bounding box calculations
- Convexity testing

**Classes**:
- `Polygon`: Represents a polygon with various geometric operations
- `Geofence`: Real-world geofencing implementation

**Use Cases**:
- Location-based services (geofencing)
- Geographic boundary checking
- Map applications
- Delivery zone management

**Time Complexity**: O(n) where n is the number of polygon vertices

**Run**:
```bash
php 04-point-in-polygon.php
```

---

### 5. Collision Detection (`05-collision-detection.php`)

**Purpose**: Implements various collision detection algorithms for different geometric shapes.

**Key Concepts**:
- Circle-circle collision
- Circle-rectangle collision
- Rectangle-rectangle collision (AABB - Axis-Aligned Bounding Box)
- Polygon-polygon collision (SAT - Separating Axis Theorem)
- Penetration depth calculations

**Classes**:
- `Circle`: Circle shape with collision methods
- `Rectangle`: Axis-aligned rectangle with intersection methods
- `CollisionDetector`: Unified collision detection system
- `GameObject`: Example game entity with collision handling

**Use Cases**:
- 2D game development
- Physics simulations
- UI interaction detection
- Spatial query optimization

**Time Complexity**:
- Circle-circle: O(1)
- Circle-rectangle: O(1)
- Rectangle-rectangle: O(1)
- Polygon-polygon: O(n + m) where n, m are vertex counts

**Run**:
```bash
php 05-collision-detection.php
```

---

## Running All Examples

To run all examples in sequence:

```bash
for file in 0*.php; do
    echo "Running $file..."
    php "$file"
    echo "---"
done
```

## Requirements

- PHP 8.0 or higher
- No external dependencies required
- All code uses modern PHP syntax (constructor property promotion, match expressions, etc.)

## Key Algorithms Summary

| Algorithm | Time Complexity | Space Complexity | Use Case |
|-----------|----------------|------------------|----------|
| Point Distance | O(1) | O(1) | Distance calculations |
| Line Intersection | O(1) | O(1) | Single pair intersection |
| Graham Scan | O(n log n) | O(n) | Convex hull computation |
| Jarvis March | O(nh) | O(n) | Small convex hulls |
| Ray Casting | O(n) | O(1) | Point in polygon |
| Circle Collision | O(1) | O(1) | Fast collision detection |
| SAT | O(n+m) | O(n+m) | Polygon collision |

## Real-World Applications

1. **Geographic Information Systems (GIS)**
   - Geofencing and location-based services
   - Route planning and optimization
   - Spatial queries and boundary analysis

2. **Game Development**
   - Collision detection for game physics
   - Character movement and interaction
   - Hit detection and damage calculation

3. **Computer Graphics**
   - Rendering optimization (culling)
   - Shape transformations
   - Animation path planning

4. **Robotics**
   - Path planning and obstacle avoidance
   - Sensor data interpretation
   - Motion control

5. **CAD/CAM Systems**
   - Design validation
   - Manufacturing path planning
   - Interference detection

## Best Practices

1. **Floating Point Precision**: All algorithms use epsilon values (1e-10) for floating-point comparisons
2. **Error Handling**: Methods throw appropriate exceptions for invalid inputs
3. **Performance**: Algorithms are optimized for their typical use cases
4. **Documentation**: All classes and methods include PHPDoc comments
5. **Modern PHP**: Uses PHP 8.0+ features for cleaner, more efficient code

## Further Reading

- Computational Geometry: Algorithms and Applications by de Berg et al.
- Real-Time Collision Detection by Christer Ericson
- [Geometric Tools documentation](https://www.geometrictools.com/)

## License

MIT License - Free to use for learning and commercial purposes.

---

**Part of the PHP Algorithms Series**
Chapter 34: Geometric Algorithms
