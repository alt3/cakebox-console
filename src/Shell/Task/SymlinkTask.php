<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

class SymlinkTask extends Shell {

/**
 * create() will create a symbolic link unless it already exists
 *
 * @param string $target containing full path to an existing file
 * @param string $link containing full path of symbolic link to be created
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
 * exists() checks if a symbolic link exists
 *
 * @param string $link containing full path to the symbolic link to check
 * @return bool true when the symbolic link exist
 */
	public function exists($link) {
		if (is_link($link)) {
			return true;
		}
		return false;
	}

}
