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
			$command = "$command 2>&1";
		} else {
			$command = "su vagrant -c \"$command\" 2>&1";
		}

		# Execute the command
		$this->log("Executing command '$command'", 'info');
		$ret = exec($command, $out, $err);

		# Write stdout and stderr to log
		foreach ($out as $line) {
			if (!empty($line)) {
				$this->log("=> $line", 'info');
			}
		}

		# Log exit-code if errors occured
		if ($err) {
			$this->log("Non-zero exit code ($err)");
			exit(1);
		}
	}

}
