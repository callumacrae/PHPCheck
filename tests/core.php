<?php

include('../src/PHPCheck.php');
$tests = new PHPCheck;

$tests->claim('less than', function ($a, $b) {
	return $a < $b;
}, array(
	PHPCheck::Integer(5),
	PHPCheck::Integer(6, 10)
));

$tests->claim('less than fail', function ($a, $b) {
	return $a < $b;
}, array(
	PHPCheck::Integer(6, 10),
	PHPCheck::Integer(5)
));

$tests->check();
echo $tests->getHTML();
