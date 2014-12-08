<?php
namespace App\View\Helper;

use Cake\View\Helper;

class CakeboxHelper extends Helper {

/**
 * Divide an array into n number of provided columns.
 *
 * @param array Full array
 * @param int Number of desired columns
 * @return array Array
 */
	public function columnize($data, $columns){
		$result = [];
		$i = 0;
		foreach ($data as $index => $item) {
			if ($i++ % $columns == 0) {
				$result[] = array();
				$current = & $result[count($result)-1];
			}
			$current[] = $item;
		}
		return $result;
	}

/**
 * Divide an array into fixed number of parts.
 *
 * @param array Array with data
 * @param int Number of parts to chop the data into
 * @return array Array
 */
	public function divideEvenly($data, $parts) {
		if (count($data) == $parts) {
			return [$data];
		}
		return array_chunk($data, ceil(count($data) / $parts));
	}

}
