<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

class ExecTask extends Shell {

/**
 * run() executes a command as either root or vagrant user.
 *
 * @param string $command containing full path and connabd arguments, options
 * @param bool $su set to false to run as root, true to run as vagrant user
 * @return int $err containing exit code of executed command
 */
	public function run($command, $su = false) {
		if ($su == false) {
			$ret = $this->out(exec("$command", $out, $err));
		} else {
			$ret = exec("su vagrant -c \"$command\"", $out, $err);
		}

		if ($err) {
			$this->out("Error executing command '$command'");
			exit(1);
		}
	}

}
