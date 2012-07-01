<?php

class PHPCheck {
	private $claims = array();
	private $group = 'nogroup';
	private $reps = 100;

	public function __constructor() {
		$this->claims['nogroup'] = array();
	}

	public function claim($testName, $predicate, $specifiers = false) {
		$this->claims[$this->group][] = array(
			$testName,
			$predicate,
			$specifiers ?: array(),
			null // null as test hasn't been ran yet
		);

		return $this;
	}

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

	public function group($groupName) {
		$this->group = $groupName;

		if (!$this->claims[$groupName]) {
			$this->claims[$groupName] = array();
		}

		return $this;
	}

	public function reps($reps) {
		$this->reps = $reps;
		return $this;
	}

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
