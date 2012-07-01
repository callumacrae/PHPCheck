<?php

$tests->group('Core tests');

$tests->claim('$tests->claim should work with true', function ($a) {
	return $a === $a;
}, array('a'));

$tests->claim('$tests->claim should work with specifiers', function ($a) {
	return $a === 'a';
}, array(function () {
	return 'a';
}));

$tests->claim('$tests->claim should work without specifiers', function () {
	return true;
});

$tests->claim('$tests->test should work', function () use ($testTests) {
	$testTests->clear();
	$i = 0;

	$result = $testTests->test('sth', function () use (&$i) {
		$i++;
		return true;
	});

	if ($i === 0 || !$result) {
		return false;
	}

	$result = $testTests->test('fail', function () {
		return false;
	});

	return !$result;
});

$tests->claim('$tests->clear should work', function () use ($testTests) {
	$testTests->claim('fail', function () {
		return false;
	});

	$testTests->clear()->check();
	return $testTests->getRaw('nogroup') === array();
});

$tests->claim('$tests->reps should work', function ($reps) use ($testTests) {
	$testTests->clear();
	$i = 0;

	$testTests->reps($reps);
	$testTests->test('reps', function () use (&$i) {
		$i++;
	});

	return $i === $reps;
}, array(
	PHPCheck::Integer(1, 10)
));

$tests->claim('$tests->claim should work with fail', function () use ($testTests) {
	$testTests->clear();
	$testTests->claim('fail', function () {
		return false;
	});
	$testTests->check();
	return $testTests->getRaw('nogroup') === array(
		array('fail', false)
	);
});

$tests->claim('$tests->claim should work when not tested', function () use ($testTests) {
	$testTests->clear();
	$testTests->claim('missing', function () {});
	return $testTests->getRaw('nogroup') === array(
		array('missing', null)
	);
});

$tests->claim('Groups should work', function () use ($testTests) {
	$testTests->clear();
	$testTests->group('Test group');
	$testTests->claim('missing', function () {});

	return $testTests->getRaw() === array(
		array('nogroup', array()),
		array('Test group', array(
			array('missing', null),
		)),
	);
});
