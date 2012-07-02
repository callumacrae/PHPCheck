---
layout: default
title: Usage
---

To use PHPCheck, include `PHPCheck.php` (or use Composer to autoload it) and create a new instance of the `PHPCheck` object. Then, use the `$phpcheck->claim` method to make "claims", and use the `$phpcheck->group` method to put the claims into groups.

The `$phpcheck->claim` method accepts the following arguments:

	$phpcheck->claim ( string $testName , function $predicate [ , array $specifiers ] )

`$testName` is the name of the test (obviously). `$predicate` is a function which returns true if the test passes. Anything else is a fail (including 1, etc). `$specifiers` is an optional array of specifiers to be passed to the predicate function. They can be like this:

	$specifiers = array(
		PHPCheck::Integer(5, 10),
		PHPCheck::String(PHPCheck::integer(2, 4), 'a'),
		PHPCheck::OneOf(array('cat', 'dog'))
	);

	$phpcheck->claim('Test name', function ($a, $b, $c) {
		var_dump($a); // 5 or 6 or 7 or 8 or 9 or 10
		var_dump($b); // "aa" or "aaa" or "aaaa"
		var_dump($c); // "cat" or "dog"
	}, $specifiers);

You can see a full list of specifiers [here](specifiers).

### Groups

Groups are pretty easy to use and understand. A group contains multiple tests, and then when the tests are output they are output by group. This can be useful to prevent a massive block of tests from being output, and for readability. You can also output the results or test by individual groups (see inline documentation, which is more detailed).

The default group is "nogroup". To change the group, use the `$phpcheck->group()` method:

	$phpcheck->group('Colour tests');

### Outputting tests

There are currently two functions to get test results, and more are planned in the future. Currently there is a `getRaw()` method, which returns an object detailing the test results, and a `getHTML()` method which returns nice HTML. `getHTML()` is more useful in real life, and `getRaw()` is mostly just used for testing the library.

Currently they only return whether the test passed, failed, or hasn't yet been tested. More detail, such as where it failed, is planned for the future.

### setup and teardown

There are two methods available for setting setup and teardown functions - functions that are called before and after every test. These can be useful for, say, resetting a counter that is used in every test, or destroying a global object which is created in every test.

The syntax for both methods are pretty much the same:

	$phpcheck->setup ( [ $groupName , ] $callback )

If a group name is specified, it will be ran for all tests in that group. If it is not, it will be ran for every single test (even ones in non-default groups which already have tests). You can only have one setup and teardown function per group.

### Demo

The following code makes a slightly pointless claim: that a number between 0 and 10 is less than a number between 10 and 20. In theory, this test will always pass: while it is possible for both number to equal 10, the chances of that happening are exactly one in 4611686014132420600 - not something we need to worry about.

	<?php

	require 'vendor/autoload.php';

	$phpcheck = new PHPCheck;

	$phpcheck->group('Numbers');

	$phpcheck->claim('Less than', function ($a, $b) {
		return ($a < $b);
	}, array(
		PHPCheck::Number(0, 10),
		PHPCheck::Number(10, 20)
	));

	$phpcheck->check();
	echo $phpcheck->getHTML();

That would output the following HTML:

	<h2>Numbers</h2><b style="color:green">Less than:</b> pass
