<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;

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
 * @var string Database connection
 */
	public $conn;

/**
 * Create a connection to the MySQL server (not database) during startup using
 * database settings in app.php.
 */
	public function initialize(){
		$this->conn = ConnectionManager::get('default');
	}

/**
 * Replaces unsupported characters in passed database name with underscores.
 *
 * @param string $name Dirty database name
 * @return string $name Cleaned database name
 */
	public function normalizeName($name) {
		$name = str_replace('.', '_', $name);		# replace dots
		$name = (str_replace('\\', '_', $name));	# replace backslashes
		$name = (str_replace('/', '_', $name));	# replace forward slashes
		return ($name);
	}

/**
 * Check if a database already exists by looking for a directory named after
 * the normalized database name in /var/lib/mysql. Too be replaced with proper
 * detection method.
 *
 * @param string $database Database name
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
 * Delete an existing database.
 *
 * @param string $database Database name
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
 * Create two new databases, one suffixed with '_test'.
 *
 * @param string $database Name used for the new databases
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
 * Grant localhost access to given database (and related _test database).
 *
 * @param string $database Database name
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
 * Return a list of all user created databases.
 *
 * @return void
 */
	public function getDatabaseList() {
		$stmt = $this->conn->query('SELECT dB FROM mysql.db');
		$rows = $stmt->fetchall();
		return Hash::flatten($rows);
	}

}
