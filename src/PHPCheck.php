<?php

class PHPCheck {
	private $claims = array();
	private $group = 'nogroup';
	private $reps = 100;

	private $setups = array();
	private $teardowns = array();

	/**
	 * Constructor just initiates the nogroup group.
	 */
	public function __constructor() {
		$this->claims['nogroup'] = array();
	}

	/**
	 * claim function is used to make claims, aka tests. It doesn't really
	 * contain any logic, and just adds the test to a private array to be
	 * handled by the check function.
	 *
	 * More docs and examples on this in the README.
	 *
	 * @param string $testName The name of the test.
	 * @param function $predicate The predicate function, which should return
	 *	true if the test was a success.
	 * @param array $specifiers An array of specifier functions, to be executed
	 *	and given to the predicate function as arguments. See README.
	 */
	public function claim($testName, $predicate, $specifiers = false) {
		$this->claims[$this->group][] = array(
			$testName,
			$predicate,
			$specifiers ?: array(),
			null // null as test hasn't been ran yet
		);

		return $this;
	}

	/**
	 * Clears all claims on a given group, or if no group is specified, all
	 * of them.
	 *
	 * @param string $groupName The name of the group to clear. If left blank,
	 *	will clear all of them.
	 */
	public function clear($groupName = '') {
		if (!$groupName) {
			$this->claims = array();
			$this->group = 'nogroup';
			$this->claims['nogroup'] = array();
		} else {
			$this->claims[$groupName] = array();
		}
		return $this;
	}

	/**
	 * Goes through the tests, executing them and storing whether they pass or
	 * fail for the get* functions to use.
	 *
	 * @param string group If specified, will only run the tests in that one
	 *	group. Not that helpful, mostly used internally.
	 */
	public function check($group = '') {
		if (!$group) {
			foreach ($this->claims as $group => $claim) {
				$this->check($group);
			}
		} else {
			foreach ($this->claims[$group] as &$claim) {
				$claim[3] = $this->test($claim[0], $claim[1], $claim[2], $group);
			}
		}
		return $this;
	}

	/**
	 * Returns nice HTML saying which tests failed and passed.
	 *
	 * @param string $groupName The name of the group to return. If not
	 *	specified, will return HTML for all the groups. Not hugely useful,
	 *	mostly just for internal use.
	 * @return string Returns HTML saying which tests failed and passed.
	 */
	public function getHTML($groupName = '') {
		$output = array();
		if (!$groupName) {
			foreach ($this->claims as $groupName => $claim) {
				$string = '<h2>' . $groupName . '</h2>';
				$string .= $this->getHTML($groupName);
				$output[] = $string;
			}

			return implode('<br /><br />', $output);
		} else {
			$statuses = array(
				true	=> 'pass',
				false	=> 'fail',
				null	=> 'untested'
			);
			$colors = array(
				true	=> 'green',
				false	=> 'red',
				null	=> 'yellow'
			);
			foreach ($this->claims[$groupName] as $claim) {
				$output[] = '<b style="color:' . $colors[$claim[3]] . '">'
					. $claim[0] . ':</b> ' . $statuses[$claim[3]];
			}

			return implode('<br />', $output);
		}

		return $output;
	}

	/**
	 * Returns an object saying which tests failed and passed.
	 *
	 * @param string $groupName The name of the group to return. If not
	 *	specified, will return an object for all the groups. Not hugely useful,
	 *	mostly just for internal use.
	 * @return object Returns an object saying which tests failed and passed.
	 */
	public function getRaw($groupName = '') {
		$output = array();
		if (!$groupName) {
			foreach ($this->claims as $groupName => $claim) {
				$output[] = array($groupName, $this->getRaw($groupName));
			}
		} else {
			foreach ($this->claims[$groupName] as $claim) {
				$output[] = array($claim[0], $claim[3]);
			}
		}

		return $output;
	}

	/**
	 * Changes the currently active group. Kind of similar to namespacing, I
	 * guess. The group names will be output by the get* functions.
	 *
	 * @param string $groupName The name of the group to switch to.
	 */
	public function group($groupName) {
		$this->group = $groupName;

		if (!$this->claims[$groupName]) {
			$this->claims[$groupName] = array();
		}

		return $this;
	}

	/**
	 * Changes the number of repeats for each test. Default is 100. More will
	 * have a greater chance of catching failed tests, but could also slow down
	 * the application.
	 *
	 * @param int $reps Number of repeats to change to.
	 */
	public function reps($reps) {
		$this->reps = $reps;
		return $this;
	}

	/**
	 * Defines a setup function to be ran before tests. If group name is
	 * specified, it'll be ran before all tests in that group. If it isn't
	 * specified, it will be ran for all tests.
	 *
	 * @param string $groupName Optional group name.
	 * @param function $callback Function to be ran before tests.
	 */
	public function setup($groupName, $callback = false) {
		if ($callback) {
			$this->setups[$groupName] = $callback;
		} else {
			$callback = $groupName;
			$this->setups['nogroup'] = $callback;
		}
	}

	/**
	 * Defines a teardown function to be ran after tests. If group name is
	 * specified, it'll be ran after all tests in that group. If it isn't
	 * specified, it will be ran for all tests.
	 *
	 * @param string $groupName Optional group name.
	 * @param function $callback Function to be ran after tests.
	 */
	public function teardown($groupName, $callback = false) {
		if ($callback) {
			$this->teardowns[$groupName] = $callback;
		} else {
			$callback = $groupName;
			$this->teardowns['nogroup'] = $callback;
		}
	}

	/**
	 * Tests a given claim, returns whether it passed or not.
	 *
	 * @param string $test The name of the test. Unused, but may be used some
	 *	time in the future.
	 * @param function $predicate The predicate.
	 * @param array $specs The specifiers.
	 * @param string $groupName Internal parameter for setups and teardowns.
	 * @return boolean Returns whether the claim passed or failed.
	 */
	public function test($test, $predicate, $specs = false, $groupName = '') {
		$pass = true;

		for ($i = 0; $i < $this->reps; $i++) {
			$newSpecifiers = array();
			if (is_array($specs)) {
				foreach ($specs as $specifier) {
					$newSpecifiers[] = $this->evalSpecifier($specifier);
				}
			}

			if ($groupName && isset($this->setups[$groupName])) {
				call_user_func($this->setups[$groupName]);
			}
			if (isset($this->setups['nogroup'])) {
				call_user_func($this->setups['nogroup']);
			}

			$result = call_user_func_array($predicate, $newSpecifiers);

			if ($groupName && isset($this->teardowns[$groupName])) {
				call_user_func($this->teardowns[$groupName]);
			}
			if (isset($this->teardowns['nogroup'])) {
				call_user_func($this->teardowns['nogroup']);
			}

			if ($result !== true) {
				$pass = false;
			}
		}

		return $pass;
	}

	/**
	 * Internal method to deal with specifiers. It is public so that it can be
	 * called inside closures inside the specifiers, which use a different
	 * scope. PHP sucks.
	 *
	 * @param function $specifier The specifier to be handled.
	 * @return mixed Whatever the specifier returns.
	 */
	public function evalSpecifier($specifier) {
		if (is_callable($specifier)) {
			$specifier = call_user_func($specifier);
		}
		return $specifier;
	}


	// SPECIFIERS ARE AWESOME


	/**
	 * Returns a specified number of a specifier in an array.
	 *
	 * @example
	 * 	PHPCheck::ArrayOf(3, ArrayOf::Integer(3)); // -> [2, 3, 0]
	 * @param int $num Number of times to repeat specifier.
	 * @param function $specifier A specifier.
	 */
	public static function ArrayOf($num, $specifier) {
		return function () use ($num, $specifier) {
			$num = PHPCheck::evalSpecifier($num);
			$output = array();
			for ($i = 0; $i < $num; $i++) {
				$output[] = PHPCheck::evalSpecifier($specifier);
			}
			return $output;
		};
	}

	/**
	 * Returns true or false randomly. Can be biased - the higher the bias, the
	 * greater the chance of true being returned.
	 *
	 * @param int $bias Chance of getting true. Defaults to 0.5.
	 */
	public static function Boolean($bias = 0.5) {
		return function () use ($bias) {
			// We multiple $bias by 1000 so that it is more accurate - rand()
			// only does whole numbers, which is useless when the bias is < 1
			return rand(0, 1000) <= ($bias * 1000);
		};
	}

	/**
	 * Generates a character between within the given range.
	 *
	 * @param string $min Minimum character.
	 * @param string $max Maxmimum character.
	 */
	public static function Character($min = 'a', $max = 'z') {
		return function () use ($min, $max) {
			return chr(rand(ord($min), ord($max)));
		};
	}

	/**
	 * Returns a whole number between 0 and a specified integer, or a whole
	 * number between two specifier integers.
	 *
	 * @param int $max Maximum number.
	 */
	public static function Integer($min, $max = '') {
		if ($max === '') {
			$max = $min;
			$min = 0;
		}

		return function () use ($min, $max) {
			return mt_rand($min, $max);
		};
	}

	/**
	 * Returns the value given to it without doing anything with it. Useful
	 * if you want to pass a function to a predicate without it being called.
	 *
	 * @param mixed $item Item to be returned.
	 */
	public static function Literal($item) {
		return function () use ($item) {
			return $item;
		};
	}

	/**
	 * Returns a random number between 0 and a specified number, or a random
	 * number between two specified numbers.
	 *
	 * @param int $min Minimum number. If not specified, defaults to 0.
	 * @param int $max Maximum number.
	 */
	public static function Number($min, $max = '') {
		if ($max === '') {
			$max = $min;
			$min = 0;
		}

		return function () use ($min, $max) {
			return $min + ($max - $min) * (mt_rand() / mt_getrandmax());
		};
	}

	/**
	 * Returns a value from an array of specifiers.
	 *
	 * @param array $array The array of specifiers.
	 */
	public static function OneOf($input) {
		return function () use ($input) {
			$max = (is_array($input) ? count($input) : strlen($input)) - 1;
			return PHPCheck::evalSpecifier($input[rand(0, $max)]);
		};
	}

	/**
	 * Takes an array of specifiers and executes them.
	 *
	 * @param array $array Array of specifiers.
	 */
	public static function SpecArray($array) {
		return function () use ($array) {
			foreach ($array as &$specifier) {
				$specifier = PHPCheck::evalSpecifier($specifier);
			}

			return $array;
		};
	}

	/**
	 * Returns a stringified value, using json_encode.
	 *
	 * @param mixed $value Value to stringify.
	 */
	public static function String($num, $value = false) {
		if ($value) {
			$num = PHPCheck::evalSpecifier($num);

			return function () use ($num, $value) {
				$string = '';
				for ($i = 0; $i < $num; $i++) {
					$string .= PHPCheck::evalSpecifier($value);
				}
				return $string;
			};
		} else {
			$value = $num;
			return function () use ($value) {
				return json_encode(PHPCheck::evalSpecifier($value));
			};
		}
	}
}
