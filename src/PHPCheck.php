<?php

require_once('PHPCheckSpecifiers.php'); // Cannot rely on composer autoloading.

class PHPCheck extends PHPCheckSpecifiers {
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
}
