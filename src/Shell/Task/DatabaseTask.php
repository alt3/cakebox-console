<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

/**
 * Task class for managing Cakebox databases.
 */
class DatabaseTask extends Shell {

/**
 * @var array Shell Tasks used by this task.
 */
	public $tasks = [
		'Exec'
	];

/**
 * Replaces unsupported characters in passed database name with underscores.
 *
 * @param string $name Dirty database name
 * @return string $name Cleaned database name
 */
	public function normalizeName($name) {
		$name = str_replace('.', '_', $name);		# replace dots
		$name = (str_replace('\\', '_', $name));	# replace backslashes
		$name = (str_replace('\/', '_', $name));	# replace forward slashes
		return ($name);
	}

/**
 * Checks if a database already exists by looking for a directory named after
 * the normalized database name in /var/lib/mysql. Too be replaced with proper
 * detection method.
 *
 * @param string $name Database name
 * @return bool
 */
	public function exists($database) {
		$database = $this->normalizeName($database);
		$directory = "/var/lib/mysql/$database";
		if (file_exists($directory)) {
			return true;
		}
		return false;
	}

/**
 * Deletes an existing database.
 *
 * @param string $name Database name
 * @return bool
 */
	public function drop($database) {
		$database = $this->normalizeName($database);
		if ($this->Exec->runCommand("mysql -u root -e \"DROP DATABASE \`$database\`\"")) {
			return true;
		}
		return false;
	}

/**
 * Creates two new databases, one suffixed with '_test'.
 *
 * @param string $name Name used for the new databases
 * @return bool
 */
	public function create($database) {
		$database = $this->normalizeName($database);
		if ($this->Exec->runCommand("mysql -u root -e \"CREATE DATABASE \`$database\`\"")) {
			return true;
		}
		return false;
	}

/**
 * Grants localhost access to given database (and related _test database).
 *
 * @param string $name Database name
 * @param string $username Name of user to grant localhost access
 * @param string $password Password for given user
 * @return bool
 */
	public function grant($database, $username, $password) {
		$database = $this->normalizeName($database);
		if ($this->Exec->runCommand("mysql -uroot -e \"GRANT ALL ON \`$database\`.* to  '$username'@'localhost' identified by '$password'\"")) {
			return true;
		}
		return false;
	}

/**
 * Returns an array with all available databases, excluding protected ones.
 *
 * @return void
 */
	public function getList() {
		#$link = mysqli_connect('localhost', 'root');
		#$listdbtables = array_column(mysqli_fetch_all($link->query('SHOW DATABASES')),0);
		#$raw = $this->Exec->runCommand("mysql -uroot -e \"SHOW DATABASES\"");
		#var_dump($raw);
	}

}
