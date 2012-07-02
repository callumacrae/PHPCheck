# PHPCheck

PHPCheck is a testing library for PHP loosely based on Haskell's QuickCheck.

## Installation

There are two ways of getting PHPCheck into your project. The first is to require it as you would any other PHP library:

```php
require_once('PHPCheck/src/PHPCheck.php');
```

The second is to use [Composer](http://getcomposer.org/). Either add `callumacrae/PHPCheck` to your composer.json, or create one containing the following:

```json
{
	"require": {
		"callumacrae/PHPCheck": "*"
	}
}
```

Then run the following two commands:

```bash
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

That will download PHPCheck into `vendor/callumacrae/PHPCheck`. You can then either require the file as above, or use Composers awesome autoloader:

```php
require 'vendor/autoload.php';
```

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
	PHPCheck::Integer(5, 10), // An integer between 5 and 10
	PHPCheck::String(PHPCheck::integer(2, 4), 'a'), // 2 to 4 "a"s
	PHPCheck::OneOf(array('cat', 'dog')), // Either cat or dog
);
```

### Demo

```php
require 'vendor/autoload.php';

$tests = new PHPCheck;

$tests->group('Numbers');

$tests->claim('Less than', function ($a, $b) {
	return ($a < $b);
}, array(
	PHPCheck::Number(0, 10),
	PHPCheck::Number(10, 20);
));

$tests->check();
```

## License

![Creative Commons License](http://i.creativecommons.org/l/by-sa/3.0/88x31.png)

This work is licensed under a [Creative Commons Attribution-ShareAlike 3.0 Unported License](http://creativecommons.org/licenses/by-sa/3.0/).
