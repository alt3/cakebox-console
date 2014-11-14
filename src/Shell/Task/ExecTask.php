<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

/**
 * Task class for managing system command shelling.
 */
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
		$this->out("Executing command '$command'");
		$ret = exec($command, $out, $err);

		# Write stdout and stderr to log
		foreach ($out as $line) {
			if (!empty($line)) {
				$this->out("=> $line");
			}
		}

		# Log exit-code if errors occured
		if ($err) {
			$this->out("Error: Non-zero exit code ($err)");
			exit(1);
		}
	}

/**
 * dirAvailable() checks if a directory is either non-existent or empty and can
 * be used before e.g. git cloning.
 *
 * @param string $directory containing full path to the directory to check
 * @return bool true if the directory is non-existint or empty
 */
	public function dirAvailable($directory) {
		if (!file_exists($directory)) {
			return true;
		}
		if (($files = scandir($directory)) && count($files) <= 2) {
			return true;
		}
		return false;
	}
}
