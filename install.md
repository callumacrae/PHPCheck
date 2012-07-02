---
layout: default
title: Installation
---

There are two ways of getting PHPCheck into your project. The first is to include it as you would any other PHP library:

	<?php

	require_once('PHPCheck/src/PHPCheck.php');

The second is to use [Composer](http://getcomposer.org/), a dependency management library for PHP (and a good one, at that). Either add `callumacrae/PHPCheck` as a depedency to your composer.json, or if you are not familiar with Composer, create a file called "composer.json" containing the following:

	{
		"require": {
			"callumacrae/PHPCheck": "*"
		}
	}

Then run the following two commands:

	curl -s http://getcomposer.org/installer | php
	php composer.phar install

The will download composer, and create a file called composer.phar. You can either add this file to your .gitignore or keep it, it doesn't really matter. They recommend committing it, as it makes sure that everyone is running the same version of the library. It will then download PHPCheck into `vendor/callumacrae/PHPCheck` (you should add the vendor directory to your .gitignore). You then either include the file as above using `require_once`, or use Composer's awesome autoloader:

	<?php

	require 'vendor/autoload.php';
