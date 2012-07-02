<?php

class PHPCheckSpecifiers {
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
