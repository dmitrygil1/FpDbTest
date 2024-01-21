# Readme.md

This repository contains the implementation of a PHP function for generating SQL queries (MySQL) from a template and parameter values.

## Task Description

The task is to write a function that constructs SQL queries based on a template and parameter values. The template includes placeholders marked with a question mark followed by a conversion specifier. The conversion specifiers are as follows:

- `?d` - converts to an integer
- `?f` - converts to a floating-point number
- `?a` - array of values
- `?#` - identifier or array of identifiers

If no conversion specifier is provided, the function uses the type of the passed value. However, only string, int, float, bool (converted to 0 or 1), and null types are allowed. Parameters `?`, `?d`, and `?f` can accept null values (in this case, the template is replaced with NULL). Strings and identifiers are automatically escaped.

Arrays (parameter `?a`) are converted either to a comma-separated list of values (list) or to pairs of identifier and value separated by commas (associative array). Each value in the array is formatted based on its type (similar to the universal parameter without a specifier).

The function should also handle conditional blocks marked with curly braces. If there is at least one parameter with a special value inside a conditional block, the block should not be included in the generated query. The special value is returned by the `skip` method. Conditional blocks cannot be nested.

The function should throw exceptions for errors in templates or values.

## Usage

To use the function, create an instance of the `Database` class and pass a `mysqli` object to the constructor. Then, call the `buildQuery` method with the SQL template and an optional array of parameter values. The method will return the constructed SQL query.

Example usage:

```php
$mysqli = new mysqli("localhost", "user", "password", "database");
$database = new FpDbTest\Database($mysqli);

$query = "SELECT * FROM users WHERE id = ?d AND status = ?";
$args = [123, "active"];

$sql = $database->buildQuery($query, $args);
echo $sql; // Output: SELECT * FROM users WHERE id = 123 AND status = 'active'
```

## Testing

The `DatabaseTest.php` file contains examples of using the function. You can run the tests to verify the correctness of the implementation.
The `text.php` file contains examples of tests. For run - use `php test.php` or just run it in your enviroment
