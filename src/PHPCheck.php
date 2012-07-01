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
		}
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
						if (is_callable($specifier)) {
							$specifier = call_user_func($specifier);
						}
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

	public function group($groupName) {
		$this->group = $groupName;

		if (!$this->claims[$groupName]) {
			$this->claims[$groupName] = array();
		}

		return $this;
	}


	// SPECIFIERS ARE AWESOME

	public static function SpecArray($array) {
		return function () use ($array) {
			foreach ($array as &$specifier) {
				if (is_callable($specifier)) {
					$specifier = call_user_func($specifier);
				}
			}
		};
	}

	public static function Integer($min, $max = '') {
		if ($max === '') {
			$max = $min;
			$min = 0;
		}

		return function () use ($min, $max) {
			return rand($min, $max);
		};
	}
}
