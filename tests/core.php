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
}, array(function () {
	return 'a';
}));

$tests->claim('$tests->claim should work without specifiers', function () {
	return true;
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


$tests->group('Specifier tests');

$tests->claim('ArrayOf specifier', function ($a, $b) {
	if ($b !== array('a', 'a', 'a') || !count($a) === 3) {
		return false;
	}

	for ($i = 0; $i < 3; $i++) {
		if (!is_integer($a[$i]) || $a[$i] > 15 || $a[$i] < 3) {
			return false;
		}
	}

	return true;
}, array(
	PHPCheck::ArrayOf(3, PHPCheck::Integer(3, 15)),
	PHPCheck::ArrayOf(3, 'a')
));

$tests->claim('Boolean specifier', function ($a, $b) {
	return (is_bool($a) && is_bool($b) && $b === true);
}, array(
	PHPCheck::Boolean(),
	PHPCheck::Boolean(1)
));

$tests->claim('Character specifier', function ($a, $b) {
	if (!is_string($a) || !is_string($b) || strlen($a) !== 1 || strlen($b) !== 1) {
		return false;
	}

	return ($b < 'q' && $b > 'l');
}, array(
	PHPCheck::character(),
	PHPCheck::character('m', 'p')
));

$tests->claim('Integer specifier', function ($a, $b) {
	if (!is_int($a) || !is_int($b)) {
		return false;
	}

	return $a < $b;
}, array(
	PHPCheck::Integer(2),
	PHPCheck::Integer(3, 5)
));

$tests->claim('Literal specifier', function ($a, $b) {
	return (is_callable($a) && $b === true);
}, array(
	PHPCheck::Literal(function () {}),
	PHPCheck::Literal(true)
));

$tests->claim('Number specifier', function ($a, $b) {
	if (!is_float($a) || !is_float($b)) {
		return false;
	}

	return $a < $b; // both would have to equal 5, so won't happen
}, array(
	PHPCheck::Number(5),
	PHPCheck::Number(5, 10)
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

$tests->claim('SpecArray specifier 2 (with keys)', function ($ary) {
	if (!is_int($ary['foo']) || !is_int($ary['bar'])) {
		return false;
	}

	return $ary['foo'] < $ary['bar'];
}, array(PHPCheck::SpecArray(array(
	'foo'	=> PHPCheck::Integer(2),
	'bar'	=> PHPCheck::Integer(3, 5)
))));


echo $tests->check()->getHTML();
