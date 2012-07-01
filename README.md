# PHPCheck

PHPCheck is a testing library for PHP loosely based on Haskell's QuickCheck.

## Usage

To start using PHPCheck, include `phpcheck.php` and create a new instance of the PHPCheck object. Then, use the `$phpcheck->claim` method to make "claims". It accepts the following arguments:

```php
$phpcheck->claim( string $testName , function $predicate [ , array $specifiers ] )
```

`$testName` is the name of the test (obviously).

`$predicate` is a function which returns true if the test passes. Anything else is a fail.

`$specifiers` is an optional array of specifiers to be passed to the predicate function. They are usually like this:

```php
$specifiers = array(
	PHPCheck::integer(5, 10), // An integer between 5 and 10
	PHPCheck::string(PHPCheck::integer(2, 4), 'a'), // 2 to 4 "a"s
	PHPCheck::oneOf(array('cat', 'dog')), // Either cat or dog
);
```

### Demo

```php
include('src/PHPCheck.php');

$tests = new PHPCheck;

$tests->group('Numbers');

$tests->claim('Less than', function (a, b) {
	return (a < b);
}, array(
	PHPCheck::number(0, 10),
	PHPCheck::number(10, 20);
));

$tests->check();
```

## License

![Creative Commons License](http://i.creativecommons.org/l/by-sa/3.0/88x31.png)

This work is licensed under a [Creative Commons Attribution-ShareAlike 3.0 Unported License](http://creativecommons.org/licenses/by-sa/3.0/).
