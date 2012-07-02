<?php

include('../src/PHPCheck.php');
$tests = new PHPCheck;
$testTests = new PHPCheck;

$tests->setup(function () use ($testTests) {
	$testTests->clear();
});

$handle = opendir('./');
while (($file = readdir($handle))) {
	if (preg_match('/^tests\_[a-z]+\.php$/', $file)) {
		include($file);
	}
}

echo $tests->check()->getHTML();
