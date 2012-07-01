<?php

class PHPCheck {
	private $claims = array();
	private $group = 'nogroup';
	private $reps = 100;

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
	 * @param string groupName If specified, will only run the tests in that one
	 *	group. Not that helpful, mostly used internally.
	 */
	public function check($groupName = '') {
		if (!$groupName) {
			foreach ($this->claims as $groupName => $claim) {
				$this->check($groupName);
			}
		} else {
			foreach ($this->claims[$groupName] as &$claim) {
				$pass = true;

				for ($i = 0; $i < $this->reps; $i++) {
					$arguments = $claim[2];
					foreach ($arguments as &$specifier) {
						$specifier = $this->evalSpecifier($specifier);
					}

					$result = call_user_func_array($claim[1], $arguments);

					if ($result !== true) {
						$pass = false;
					}
				}

				$claim[3] = $pass;
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
				$output[] = '<h2>' . $groupName . '</h2><br />' . $this->getHTML($groupName);
			}

			return implode('<br /><br />', $output);
		} else {
			foreach ($this->claims[$groupName] as $claim) {
				$output[] = '<b>' . $claim[0] . ':</b> ' . ($claim[3] ? 'pass' : 'fail');
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
			return rand(0, 1000) < ($bias * 1000); // 1000 so bias more accurate
		};
	}

	/**
	 * Returns a whole number between 0 and a specified integer, or a whole
	 * number between two specifier integers.
	 *
	 * @example
	 *	PHPCheck::Integer(5); // -> an integer between 0 and 5 inclusive
	 *	PHPCheck::Integer(3, 5); // -> an integer between 3 and 5 inclusive
	 * @param int $min Minimum number. If not specified, defaults to 0.
	 * @param in $max Maximum number.
	 */
	public static function Integer($min, $max = '') {
		if ($max === '') {
			$max = $min;
			$min = 0;
		}

		return function () use ($min, $max) {
			return rand($min, $max);
		};
	}

	/**
	 * Takes an array of specifiers and executes them.
	 *
	 * @example
	 *	PHPCheck::SpecArray(
	 *		PHPCheck::Integer(5),
	 *		PHPCheck::Integer(5, 10)
	 *	); // -> an array with two integers, eg [2, 6] or [5, 5]
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
}
