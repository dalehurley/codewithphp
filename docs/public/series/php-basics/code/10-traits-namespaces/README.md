# Chapter 10: OOP - Traits and Namespaces - Code Examples

Master code reusability with traits and code organization with namespaces.

## Files

1. **`traits-basic.php`** - Trait fundamentals, multiple traits, conflicts
2. **`namespaces-basic.php`** - Namespace basics, use statements, aliasing

## Quick Start

```bash
php traits-basic.php
php namespaces-basic.php
```

## Key Concepts

### Traits

- **Purpose**: Horizontal code reuse without inheritance
- **Syntax**: `trait Name { }` and `use TraitName;`
- **Multiple**: `use Trait1, Trait2;`
- **Conflicts**: `use TraitA, TraitB { TraitA::method insteadof TraitB; }`

### Namespaces

- **Purpose**: Organize code, prevent naming conflicts
- **Syntax**: `namespace Vendor\Package;`
- **Import**: `use Full\Namespace\ClassName;`
- **Alias**: `use Long\Namespace\Class as ShortName;`
- **Group**: `use App\Models\{User, Post};`

## Common Patterns

**Timestampable Trait**:

```php
trait Timestampable {
    private ?string $createdAt = null;
    public function setCreatedAt(): void {
        $this->createdAt = date('Y-m-d H:i:s');
    }
}
```

**PSR-4 Structure**:

```
src/
  App/
    Models/User.php       → namespace App\Models;
    Controllers/User.php  → namespace App\Controllers;
```

## Related Chapter

[Chapter 10: OOP - Traits and Namespaces](../../chapters/10-oop-traits-and-namespaces.md)
