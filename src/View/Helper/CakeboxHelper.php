<?php
namespace App\View\Helper;

use Cake\View\Helper;

class CakeboxHelper extends Helper {

/**
 * Chop up an array into muliple, evenly divided, column arrays
 *
 * @param array Full array
 * @param int Number of columns to divide data into
 * @return array Array with extra root-id containing column elements
 */
	public function columnize($data, $columns){
		return array_chunk($data, ceil(count($data) / $columns));
	}

}
