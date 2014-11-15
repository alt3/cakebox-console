<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

/**
 * Task class for managing symbolic links.
 */
class SymlinkTask extends Shell {

/**
 * Create a symbolic link unless it already exists.
 *
 * @param string $target Full path to the existing file
 * @param string $link Full path where symbolic link will be created
 * @return void
 */
	public function create($target, $link) {
		$this->out("Creating symbolic link $link");
		if ($this->exists($link)) {
			$this->out("* Skipping: symlink already exist");
			return;
		}
		symlink($target, $link);
	}

/**
 * Check if a symbolic link already exists.
 *
 * @param string $link Full path to the file/link to check
 * @return bool
 */
	public function exists($link) {
		if (is_link($link)) {
			return true;
		}
		return false;
	}

}
