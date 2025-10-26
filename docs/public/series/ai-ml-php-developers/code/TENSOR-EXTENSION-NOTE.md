# Tensor Extension Compatibility Note

## Status: Not Compatible with PHP 8.4

**Last Updated:** October 26, 2025

### Overview

The PECL Tensor extension (version 3.0.7) does **not currently support PHP 8.4**. The extension is limited to PHP versions **≤ 8.3.99**.

### Installation Attempt

```bash
pecl install tensor
```

**Error Message:**

```
pecl/tensor requires PHP (version >= 8.0.0, version <= 8.3.99), installed version is 8.4.14
No valid packages found
install failed
```

### Project Impact

Since this project requires **PHP 8.4** (as specified in `.cursor/rules/php-version.mdc`), we cannot use the Tensor extension directly.

## Recommended Alternatives

### 1. PHP-ML (Primary Recommendation)

**PHP-ML** provides comprehensive matrix and tensor-like operations compatible with PHP 8.4.

**Installation:**

```bash
composer require php-ai/php-ml
```

**Capabilities:**

- ✅ Matrix creation and manipulation
- ✅ Matrix arithmetic (addition, multiplication)
- ✅ Linear algebra operations (transpose, inverse, determinant)
- ✅ Statistical operations (mean, standard deviation, etc.)
- ✅ Feature scaling and normalization
- ✅ Distance calculations
- ✅ Support for machine learning workflows

**Example Code:**
See [`chapter-03/13-tensor-operations-phpml.php`](/series/ai-ml-php-developers/code/chapter-03/13-tensor-operations-phpml.php) for comprehensive examples.

### 2. RubixML

**RubixML** also includes tensor operations and is compatible with PHP 8.4.

**Installation:**

```bash
composer require rubix/ml
```

**Use Cases:**

- More advanced machine learning models
- Built-in transformers and preprocessors
- Neural network support

### 3. Native PHP Arrays

For simple matrix operations, native PHP arrays with custom functions can be sufficient:

```php
// Example: Matrix multiplication with arrays
function matrixMultiply(array $a, array $b): array
{
    $result = [];
    $rowsA = count($a);
    $colsA = count($a[0]);
    $colsB = count($b[0]);

    for ($i = 0; $i < $rowsA; $i++) {
        for ($j = 0; $j < $colsB; $j++) {
            $sum = 0;
            for ($k = 0; $k < $colsA; $k++) {
                $sum += $a[$i][$k] * $b[$k][$j];
            }
            $result[$i][$j] = $sum;
        }
    }

    return $result;
}
```

## Future Considerations

### Monitoring Tensor Extension Updates

- **GitHub Repository:** https://github.com/RubixML/Tensor
- **PECL Package:** https://pecl.php.net/package/Tensor
- **Check for PHP 8.4 support:** Periodically check for version updates

### If Tensor Becomes Compatible

Once Tensor supports PHP 8.4, installation would be:

```bash
# Future installation (when PHP 8.4 is supported)
pecl install tensor

# Add to php.ini
echo "extension=tensor.so" >> /opt/homebrew/etc/php/8.4/php.ini

# Verify installation
php -m | grep tensor
```

## Performance Considerations

### Tensor Extension (When Available)

- ✅ Highly optimized C extension
- ✅ Better performance for large-scale operations
- ❌ Not currently available for PHP 8.4

### PHP-ML

- ✅ Pure PHP implementation
- ✅ Easy to install and maintain
- ✅ Sufficient performance for most applications
- ⚠️ Slower than C extensions for very large matrices

### Recommendation

For this project targeting PHP 8.4:

1. **Use PHP-ML** for all tensor/matrix operations
2. **Monitor** Tensor extension updates for future PHP 8.4 support
3. **Benchmark** if performance becomes a concern
4. **Consider** Python integration (covered in Chapter 11) for heavy computational tasks

## Code Examples Location

All tensor operation examples using PHP-ML alternatives:

- [`chapter-03/13-tensor-operations-phpml.php`](/series/ai-ml-php-developers/code/chapter-03/13-tensor-operations-phpml.php) - Comprehensive matrix operations
- [`chapter-03/01-supervised-classification.php`](/series/ai-ml-php-developers/code/chapter-03/01-supervised-classification.php) - Using matrices in ML
- [`chapter-03/11-regression-example.php`](/series/ai-ml-php-developers/code/chapter-03/11-regression-example.php) - Linear algebra in regression

## Testing

All tensor operation examples have been tested with PHP 8.4.14:

```bash
cd /Users/dalehurley/Code/PHP-From-Scratch/testing/ai-ml-series/chapter-03
php 13-tensor-operations-phpml.php
```

## Documentation References

- **PHP-ML Documentation:** https://php-ml.readthedocs.io/
- **Matrix Class:** https://php-ml.readthedocs.io/en/latest/math/matrix/
- **RubixML Documentation:** https://docs.rubixml.com/

---

**Conclusion:** While the Tensor extension offers excellent performance, PHP-ML provides a robust and fully-functional alternative for PHP 8.4 projects, meeting all requirements for the AI/ML series tutorials.
