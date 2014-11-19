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
 * @var string Database connection.
 */
	public $conn;

/**
 * @var array Cakebox specific settings
 */
	public $settings = [
		"test_suffix" => '_test',
		"mysql" => [
			'system_databases' => [
				'mysql',
				'information_schema',
				'performance_schema',
				'test'
			]
		]
	];

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
		$stmt = $this->conn->execute("SHOW DATABASES LIKE '$database'");
		if ($stmt->count()) {
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
		foreach ($this->getDatabaseNames($database) as $database){
			$this->out("Creating database $database");
			if ($this->exists($database)){
				$this->out("  => Skipping: database $database already exists");
				return false;
			}
			$stmt = $this->conn->execute("CREATE DATABASE `$database`");
		};
	}

/**
 * Delete an existing database.
 *
 * @param string $database Database name
 * @return bool
 */
	public function drop($database) {
		# Prevent system database drop attempts
		if (in_array($database, $this->settings['mysql']['system_databases'])) {
			$this->out("Error: attempt to delete system database");
			return false;
		}

		# Delete database and related test-database
		foreach ($this->getDatabaseNames($database) as $database){
			$this->out("Deleting database " . $this->normalizeName($database));
			if ($this->exists($database)){
				$stmt = $this->conn->execute("DROP DATABASE `$database`");
			} else {
				$this->out("  => Skipping: database $database does not exist");
			}
		}
	}

/**
 * Grant localhost access to given database (and related _test database) to.
 *
 * @param string $database Database name
 * @param string $username Name of user to grant localhost access
 * @param string $password Password for given user
 * @return bool
 */
	public function setGrants($database, $username, $password) {
		foreach ($this->getDatabaseNames($database) as $database){
			$this->out("Granting user '$username' localhost access on database $database");
			$stmt = $this->conn->execute("GRANT ALL ON `$database`.* to  '$username'@'localhost' identified by '$password'");
		}
	}

/**
 * Return a list of all user created databases.
 *
 * @return void
 */
	public function getDatabaseList() {
		$stmt = $this->conn->execute('SHOW DATABASES');
		$rows = Hash::extract($stmt->fetchall(), '{n}.{n}');
		$stripped = array_diff($rows, $this->settings['mysql']['system_databases']);
		return $stripped;
	}

/**
 * Get an array containing the normalized database name and normalized name of
 * the related test database.
 */
	private function getDatabaseNames($database) {
		$database = $this->normalizeName($database);
		return [$database, $database . $this->settings['test_suffix']];
	}

}
