# Namespaces Example

This directory demonstrates PHP namespaces with a practical example.

## Structure

```
namespaces/
├── App/
│   ├── Utils/
│   │   └── Logger.php
│   └── Database/
│       └── Logger.php
├── index.php
└── README.md
```

## Running

```bash
cd namespaces
php index.php
```

## Expected Output

```
[Utils Logger] This is a utility message.
[Database Logger] Database query executed.
```

## Key Concepts

- Two classes with the same name (`Logger`) can coexist in different namespaces
- Namespaces typically follow the directory structure (PSR-4 convention)
- The `use` keyword imports classes into the current file
- Aliases (`as`) allow you to use multiple classes with the same short name

## Related Examples

See also:

- `../namespaces-global.php` - Demonstrates how to use PHP built-in classes within namespaced code
- `../namespaces-global-error.php` - Shows the common mistake of forgetting to import built-in classes
