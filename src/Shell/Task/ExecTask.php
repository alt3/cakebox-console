<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

/**
 * Task class for managing system command shelling.
 */
class ExecTask extends Shell {

/**
 * Execute a system command as root or using su with provided username.
 *
 * @param string $command Full path to the command with options and arguments
 * @param string $username Optional sudo user used to execute the command
 * @return int $err Exit code of executed command
 */
	public function runCommand($command, $username = "root") {
		$this->out("Executing system command as $username");
		if ($username == "root") {
			$command = "$command 2>&1";
		} else {
			$command = "su $username -c \"$command\" 2>&1";
		}
		$this->out("  => $command");

		# Execute the command, capture exit code, stdout and stderr
		$ret = exec($command, $out, $err);
		foreach ($out as $line) {
			if (!empty($line)) {
				$this->out("  => $line");
			}
		}

		# Log exit-code if errors occured
		if ($err) {
			$this->out("Error: Non-zero exit code ($err)");
			return false;
		}
	}

/**
 * Check if a directory is either non-existent or empty. Useful before running
 * commands which require empty directories (like git clone).
 *
 * @param string $directory Full path to directory to check
 * @return bool
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

/**
 * Exit PHP script with exit code 0 to inform bash about success.
 *
 * @return void
 */
	public function exitBashSuccess() {
		exit (0);
	}

/**
 * Exit PHP script with exit code 0 to inform bash about success.
 *
 * @return void
 */
	public function exitBashError() {
		exit (1);
	}

}
