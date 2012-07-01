<?php

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

$OneOf_a = 0;
$OneOf_b = 0; // This test will fail, but not very often at all
$tests->claim('OneOf specifier (array)', function ($which) use ($OneOf_a, $OneOf_b) {
	if ($which === 'a' && $OneOf_a > 90 && $OneOf_b < 5) {
		return false;
	} else if ($OneOf_a < 5 && $OneOf_b > 90) {
		return false;
	}

	return true;
}, array(PHPCheck::OneOf(array(
	function () use ($OneOf_a) {
		$OneOf_a++;
		return 'a';
	}, function () use ($OneOf_b) {
		$OneOf_b++;
		return 'b';
	}
))));

$tests->claim('OneOf specifier (string)', function ($c) {
	return strlen($c) === 1 && $c > 'b' && $c < 'h';
}, array(
	PHPCheck::OneOf('cdefg')
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
