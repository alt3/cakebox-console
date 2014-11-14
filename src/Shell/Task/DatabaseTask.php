<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

/**
 * Task class for managing Cakebox databases
 */
class DatabaseTask extends Shell {

/**
 * @var array containing external tasks used by this task
 */
	public $tasks = [
		'Exec'
	];

/**
 * normalizeName() replaces non-allowed dots (.) in database name with underscores.
 *
 * @param string $name of the database
 * @return string $name converted to safe format
 */
	public function normalizeName($name) {
		return (str_replace('.', '_', $name));
	}

/**
 * exists() checks if a database already exists by looking for a directory named
 * after the database in /var/lib/mysql (to be replaced with proper detection method).
 *
 * @param string $name of the database to check
 * @return bool true when the database exists
 */
	public function exists($name) {
		$directory = "/var/lib/mysql/$name";
		if (file_exists($directory)) {
			return true;
		}
		return false;
	}

/**
 * drop() deletes an existing database.
 *
 * @param string $name of the database to drop
 * @return bool true when the drop succeeded
 */
	public function drop($name) {
		if ($this->Exec->run("mysql -u root -e \"DROP DATABASE \`$name\`\"")) {
			return true;
		}
		return false;
	}

/**
 * create() creates two new databases, one suffixed with '_test';
 *
 * @param string $name to use for the databases
 * @return bool true when created successfully
 */
	public function create($name) {
		if ($this->Exec->run("mysql -u root -e \"CREATE DATABASE \`$name\`\"")) {
			return true;
		}

		return false;
	}

/**
 * grant() gives localhost access to the given database (and _test database).
 *
 * @param string $database containing name of the database
 * @param string $username containing name of the user to grant localhost access
 * @param string $password for given $username
 * @return bool true when permissions are granted successfully
 */
	public function grant($database, $username, $password) {
		if ($this->Exec->run("mysql -uroot -e \"GRANT ALL ON \`$database\`.* to  '$username'@'localhost' identified by '$password'\"")) {
			return true;
		}
		return false;
	}

/**
 * getList() returns all available databases, excluding protected ones.
 *
 * @return void
 */
	public function getList() {
		#$link = mysqli_connect('localhost', 'root');
		#$listdbtables = array_column(mysqli_fetch_all($link->query('SHOW DATABASES')),0);
		#$raw = $this->Exec->run("mysql -uroot -e \"SHOW DATABASES\"");
		#var_dump($raw);
	}

}
