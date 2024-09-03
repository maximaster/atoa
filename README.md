# maximaster/atoa

Convert a value to another value using your own callables.

```bash
composer require maximaster/atoa
```

## Example

```php
namespace Maximaster\Atoa;

use Maximaster\Atoa\Contract\Atoa;
use Maximaster\Atoa\Converter;

class A { public function __construct(public string $value) {} }
class B { public function __construct(public string $value) {} }

// Implement converters for all needed cases.
// Make sure that callable has both input and return types described.
$converter = new Converter([
    static fn (A $a): B => new B($a->value),
    static fn (B $b): A => new A($b->value),
    static fn (string $c): int => intval($c),
]);

// Use Atoa interface instead of implementation in your services.
(static function (Atoa $atoa): void {
    // Ask converter to get desired type and pass any other type.
    // If the converter does know how to convert this object to desired type, it
    // would do it.
    $a = $converter->convertTo(A::class, new B('hello')); // A('hello')
    $b = $converter->convertTo(B::class, new A('hello')); // B('hello')
    $c = $converter->convertTo('int', '42'); // 42
})($converter);
```
