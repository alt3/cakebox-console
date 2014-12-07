<?php
namespace App\View\Helper;

use Cake\View\Helper;

class CakeboxHelper extends Helper {

/**
 * Chop up an array into evenly divided column-arrays
 *
 * @param array Full array
 * @param int Number of columns to divide data into
 * @return array Array with extra root-id containing column elements
 */
	public function columnize($data, $columns){
		if (count($data) == $columns) {
			return [$data];
		}
		return array_chunk($data, ceil(count($data) / $columns));
	}

}
