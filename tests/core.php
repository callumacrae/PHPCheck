<?php

include('../src/PHPCheck.php');
$tests = new PHPCheck;

$testTests = new PHPCheck;

$tests->group('Core tests');

$tests->claim('$tests->claim should work with true', function ($a) {
	return $a === $a;
}, array('a'));

$tests->claim('$tests->claim should work with specifiers', function ($a) {
	return $a === 'a';
}, array(function() {
	return 'a';
}));

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

	$testTests->claim('reps', function ($i) {
		$i++;
	}, array(&$i));
	$testTests->reps($reps)->check();

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


$tests->group('Specifier tests');

$tests->claim('Integer specifier', function ($a, $b) {
	if (!is_int($a) || !is_int($b)) {
		return false;
	}

	return $a < $b;
}, array(
	PHPCheck::Integer(2),
	PHPCheck::Integer(3, 5)
));

$tests->claim('SpecArray specifier', function ($ary) {
	if (!is_int($ary[0]) || !is_int($ary[1])) {
		return false;
	}

	return $ary[0] < $ary[1];
}, array(PHPCheck::SpecArray(array(
	PHPCheck::Integer(2),
	PHPCheck::Integer(3, 5)
))));


echo $tests->check()->getHTML();
