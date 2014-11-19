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
		try {
			if ($this->conn->execute("SHOW DATABASES LIKE '$database'")->count()) {
				$this->out("Database exists");
				return true;
			};
			return false;
		} catch (\Exception $e) {
			$this->out("Error: " . $e->getMessage());
			$this->Exec->exitBashError();
		}
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
				$this->out("* Skipping: database $database already exists");
				continue;
			}
			try {
				$this->conn->execute("CREATE DATABASE `$database`");
			} catch (\Exception $e) {
				$this->out("Error: " . $e->getMessage());
				$this->Exec->exitBashError();
			}
		}
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

		# Process both main and test database
		foreach ($this->getDatabaseNames($database) as $database){
			$this->out("Deleting database " . $this->normalizeName($database));
			if (!$this->exists($database)){
				$this->out("* Skipping: database $database does not exist");
				continue;
			}
			try {
				$this->conn->execute("DROP DATABASE `$database`");
			} catch (\Exception $e) {
				$this->out("MyError: " . $e->getMessage());
				$this->Exec->exitBashError();
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
			try {
				$this->conn->execute("GRANTS ALL ON `$database`.* to  '$username'@'localhost' identified by '$password'");
			} catch (\Exception $e) {
				$this->out("Error: " . $e->getMessage());
				$this->Exec->exitBashError();
			}
		}
	}

/**
 * Return a list of all user created databases.
 *
 * @return array $stripped Array containing user databases
 */
	public function getDatabaseList() {
		try {
			$stmt = $this->conn->execute('SHOW DATABASES');
		} catch (\Exception $e) {
			$this->out("Error: " . $e->getMessage());
			$this->Exec->exitBashError();
		}

		# Create flat array and remove system databases
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
